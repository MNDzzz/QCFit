<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useCanvasStore } from '@/store/canvas';
import CanvasEditor from '@/components/canvas/CanvasEditor.vue';
import CanvasSidebar from '@/components/canvas/CanvasSidebar.vue';
import CanvasToolbar from '@/components/canvas/CanvasToolbar.vue';
import axios from 'axios';
import { useToast } from "primevue/usetoast";

const toast = useToast();
const router = useRouter();
const route = useRoute();
const canvasStore = useCanvasStore();

// Estado
const outfitTitle = ref('');
const saving = ref(false);
const showSaveModal = ref(false);
const loadingRemix = ref(false);
const remixSourceTitle = ref(''); // Título del outfit que estamos remixeando
const canvasEditorRef = ref(null); // Referencia al componente CanvasEditor

// Cargar outfit si viene con query param outfit_id (Modo Remix)
onMounted(async () => {
    const outfitId = route.query.outfit_id;
    
    if (outfitId) {
        await loadRemixOutfit(outfitId);
    }
});

/**
 * Cargar un outfit existente para remixear.
 * Obtiene los datos de la API y los carga en el canvasStore.
 */
async function loadRemixOutfit(outfitId) {
    loadingRemix.value = true;
    
    try {
        const response = await axios.get(`/api/outfits/${outfitId}`);
        const outfitData = response.data.data || response.data;
        
        // Guardar título para referencia
        remixSourceTitle.value = outfitData.title || 'Outfit sin título';
        
        // Cargar items en el canvas
        canvasStore.loadOutfit(outfitData);
        
        console.log(`Outfit "${remixSourceTitle.value}" cargado para Remix`);
        
    } catch (error) {
        console.error('Error cargando outfit para remix:', error);
        
        if (error.response?.status === 404) {
            toast.add({ severity: 'error', summary: 'Error', detail: 'El outfit que intentas remixear no existe.', life: 4000 });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Error al cargar el outfit. Intenta nuevamente.', life: 4000 });
        }
    } finally {
        loadingRemix.value = false;
    }
}

// Manejar guardar outfit
async function handleSave() {
    // Validar que haya items en el canvas
    if (canvasStore.canvasItems.length === 0) {
        toast.add({ severity: 'warn', summary: 'Canvas vacío', detail: 'Añade productos al canvas antes de guardar', life: 3000 });
        return;
    }

    // Mostrar modal para pedir el título
    showSaveModal.value = true;
}

// Confirmar guardado con título
async function confirmSave() {
    if (!outfitTitle.value.trim()) {
        toast.add({ severity: 'warn', summary: 'Atención', detail: 'Por favor, ingresa un título para tu outfit', life: 3000 });
        return;
    }

    saving.value = true;

    try {
        // Preparar datos del outfit para enviar al backend
        const outfitData = {
            title: outfitTitle.value,
            items: canvasStore.canvasItems.map(item => ({
                product_id: item.productId,
                x: item.x,
                y: item.y,
                rotation: item.rotation,
                scaleX: item.scaleX,
                scaleY: item.scaleY,
                zIndex: item.zIndex,
                isFlipped: item.isFlipped,
                imageId: item.imageId || null,
            })),
        };

        // Enviar al endpoint de la API
        const response = await axios.post('/api/outfits', outfitData);

        // Éxito: mostrar mensaje y cerrar modal
        toast.add({ severity: 'success', summary: '¡Éxito!', detail: `Outfit "${outfitTitle.value}" guardado correctamente`, life: 3000 });

        // Cerrar modal y limpiar
        showSaveModal.value = false;
        outfitTitle.value = '';

        // Opcional: limpiar el canvas después de guardar
        // canvasStore.clearCanvas();

        // Opcional: redirigir al detalle del outfit creado
        // if (response.data?.id) {
        //     router.push({ name: 'OutfitDetail', params: { id: response.data.id } });
        // }

    } catch (error) {
        console.error('Error guardando outfit:', error);
        
        // Manejar errores específicos
        if (error.response?.status === 401) {
            toast.add({ severity: 'info', summary: 'Requiere Login', detail: 'Debes iniciar sesión para guardar outfits.', life: 4000 });
            router.push({ name: 'auth.login' });
        } else if (error.response?.status === 422) {
            // Errores de validación
            const errors = error.response.data.errors;
            const firstError = Object.values(errors)[0][0];
            toast.add({ severity: 'error', summary: 'Error de Validación', detail: firstError, life: 5000 });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Error al guardar el outfit. Intenta nuevamente.', life: 4000 });
        }
    } finally {
        saving.value = false;
    }
}

/**
 * Manejar exportar como imagen.
 * Llama a la función downloadImage del CanvasEditor.
 */
function handleExport() {
    // Validar que haya items en el canvas
    if (canvasStore.canvasItems.length === 0) {
        toast.add({ severity: 'warn', summary: 'Canvas vacío', detail: 'Añade productos al canvas antes de exportar', life: 3000 });
        return;
    }

    // Verificar referencia al CanvasEditor
    if (!canvasEditorRef.value) {
        console.error('No hay referencia al CanvasEditor');
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error al exportar. Intenta recargar la página.', life: 3000 });
        return;
    }

    // Generar nombre de archivo
    const timestamp = new Date().toISOString().slice(0, 10);
    const filename = canvasStore.isRemixMode 
        ? `remix-${remixSourceTitle.value || 'outfit'}-${timestamp}`
        : `mi-outfit-${timestamp}`;

    // Llamar a la función de descarga
    canvasEditorRef.value.downloadImage(filename, 'png');
}

// Cancelar modal de guardado
function cancelSave() {
    showSaveModal.value = false;
    outfitTitle.value = '';
}
</script>

<template>
    <div class="studio-view flex flex-col h-screen bg-slate-950 text-white overflow-hidden">
        <!-- Loading Overlay para Remix -->
        <div v-if="loadingRemix" class="fixed inset-0 bg-slate-950/90 flex items-center justify-center z-50 backdrop-blur-sm">
            <div class="text-center">
                <i class="pi pi-spin pi-spinner text-5xl text-violet-600 mb-6"></i>
                <p class="text-slate-300 font-display text-lg tracking-wide">Cargando outfit para remix...</p>
            </div>
        </div>

        <!-- Header del Studio (Dark & Premium) -->
        <div class="studio-header flex items-center justify-between px-6 py-4 bg-slate-950 border-b border-slate-900/50">
            <!-- Logo y navegación -->
            <div class="flex items-center gap-6">
                <router-link to="/" class="flex items-center gap-2 hover:opacity-80 transition-opacity group">
                    <div class="w-8 h-8 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center group-hover:border-violet-600/50 transition-colors">
                        <i class="pi pi-arrow-left text-slate-400 group-hover:text-white text-xs"></i>
                    </div>
                </router-link>
                
                <div class="flex flex-col">
                    <h1 class="text-white font-display font-bold text-xl tracking-tight leading-none">
                        QCFit<span class="text-violet-500">.</span> Studio
                    </h1>
                     <!-- Badge de Remix Mode -->
                    <div v-if="canvasStore.isRemixMode" class="flex items-center gap-1 mt-1">
                        <span class="text-[10px] items-center gap-1 text-fuchsia-400 font-bold uppercase tracking-wider flex">
                             <i class="pi pi-refresh text-[10px]"></i> Remixing
                        </span>
                        <span v-if="remixSourceTitle" class="text-[10px] text-slate-500 truncate max-w-[150px]">
                            • {{ remixSourceTitle }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stats Centrales (Opcional) -->
            <div class="hidden md:flex items-center gap-6 text-sm">
                 <div class="flex items-center gap-2 text-slate-400 bg-slate-900/50 px-3 py-1.5 rounded-full border border-slate-800/50">
                    <i class="pi pi-layers text-xs"></i>
                    <span class="font-mono text-xs">{{ canvasStore.canvasItems.length }} layers</span>
                </div>
                 <div v-if="canvasStore.selectedItem" class="flex items-center gap-2 text-violet-400 bg-violet-900/10 px-3 py-1.5 rounded-full border border-violet-500/20 animate-fade-in">
                    <i class="pi pi-check-circle text-xs"></i>
                    <span class="font-bold text-xs">{{ canvasStore.selectedItem.productName }}</span>
                </div>
            </div>

            <!-- Acciones Header -->
            <div class="flex items-center gap-3">
                 <button 
                    class="w-10 h-10 rounded-full bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 flex items-center justify-center transition-all"
                    title="Undo (WIP)"
                >
                    <i class="pi pi-undo"></i>
                 </button>
                 <button 
                    class="w-10 h-10 rounded-full bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800 border border-slate-800 flex items-center justify-center transition-all"
                    title="Redo (WIP)"
                >
                    <i class="pi pi-refresh transform rotate-180"></i>
                 </button>
                 
                 <div class="h-6 w-px bg-slate-800 mx-1"></div>

                 <button 
                    @click="handleSave"
                    class="px-5 py-2 bg-white text-black font-bold text-sm rounded-full hover:bg-slate-200 transition-colors shadow-lg shadow-white/5"
                >
                    Publish
                 </button>
            </div>
        </div>


        <!-- Toolbar (Ahora integrada o separada, mantendremos la existente por ahora pero adaptada) -->
        <CanvasToolbar @save="handleSave" @export="handleExport" />

        <!-- Área principal: Sidebar + Canvas -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Sidebar -->
            <CanvasSidebar />

            <!-- Canvas Editor -->
            <div class="flex-1 overflow-hidden relative bg-[#0f1014]"> <!-- Background muy oscuro -->
                <CanvasEditor ref="canvasEditorRef" />
            </div>
        </div>

        <!-- Modal de Guardar Outfit (Dark Theme) -->
        <Dialog 
            v-model:visible="showSaveModal"
            modal
            header="Guardar Outfit"
            :style="{ width: '450px' }"
            :draggable="false"
            :pt="{
                root: { class: 'bg-slate-900 border border-slate-800 text-white' },
                header: { class: 'bg-slate-900 text-white border-b border-slate-800' },
                content: { class: 'bg-slate-900 text-white' },
                footer: { class: 'bg-slate-900 border-t border-slate-800' },
                closeButton: { class: 'text-slate-400 hover:text-white focus:ring-0' }
            }"
        >
            <div class="py-4">
                <label for="outfit-title" class="block text-sm font-medium text-slate-400 mb-2">
                    Título del Outfit
                </label>
                <input
                    id="outfit-title"
                    v-model="outfitTitle"
                    type="text"
                    placeholder="Ej: Summer Street Style 2024"
                    class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-violet-600 focus:ring-1 focus:ring-violet-600 placeholder-slate-600 transition-all"
                    @keyup.enter="confirmSave"
                    autofocus
                >
                <p class="mt-3 text-xs text-slate-500">
                    Este título será visible en tu perfil y en la galería pública.
                </p>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        @click="cancelSave"
                        class="px-4 py-2 text-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="confirmSave"
                        :disabled="saving || !outfitTitle.trim()"
                        class="px-6 py-2 text-sm bg-violet-600 hover:bg-violet-500 disabled:bg-slate-800 disabled:text-slate-500 disabled:cursor-not-allowed text-white font-bold rounded-lg transition-all flex items-center gap-2 shadow-lg shadow-violet-900/20"
                    >
                        <i v-if="saving" class="pi pi-spin pi-spinner"></i>
                        <span v-else>Guardar & Publicar</span>
                    </button>
                </div>
            </template>
        </Dialog>
    </div>
</template>

<style scoped>
/* No extra styles needed, using Tailwind */
</style>
