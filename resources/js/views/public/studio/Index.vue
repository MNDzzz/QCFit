<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useCanvasStore } from '@/store/canvas';
import CanvasEditor from '@/components/canvas/CanvasEditor.vue';
import CanvasSidebar from '@/components/canvas/CanvasSidebar.vue';
import CanvasToolbar from '@/components/canvas/CanvasToolbar.vue';
import axios from 'axios';

const router = useRouter();
const canvasStore = useCanvasStore();

// Estado
const outfitTitle = ref('');
const saving = ref(false);
const showSaveModal = ref(false);

// Manejar guardar outfit
async function handleSave() {
    // Validar que haya items en el canvas
    if (canvasStore.canvasItems.length === 0) {
        alert('Añade productos al canvas antes de guardar');
        return;
    }

    // Mostrar modal para pedir el título
    showSaveModal.value = true;
}

// Confirmar guardado con título
async function confirmSave() {
    if (!outfitTitle.value.trim()) {
        alert('Por favor, ingresa un título para tu outfit');
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
        alert(`¡Outfit "${outfitTitle.value}" guardado con éxito!`);

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
            alert('Debes iniciar sesión para guardar outfits.');
            router.push({ name: 'auth.login' });
        } else if (error.response?.status === 422) {
            // Errores de validación
            const errors = error.response.data.errors;
            const firstError = Object.values(errors)[0][0];
            alert(`Error de validación: ${firstError}`);
        } else {
            alert('Error al guardar el outfit. Intenta nuevamente.');
        }
    } finally {
        saving.value = false;
    }
}

// Manejar exportar como imagen
function handleExport() {
    // TODO: Implementar exportación del canvas como imagen
    // Usar stage.toDataURL() de Konva
    alert('Función de exportar imagen próximamente');
}

// Cancelar modal de guardado
function cancelSave() {
    showSaveModal.value = false;
    outfitTitle.value = '';
}
</script>

<template>
    <div class="studio-view flex flex-col h-screen bg-slate-950">
        <!-- Header del Studio -->
        <div class="studio-header flex items-center justify-between px-6 py-4 bg-slate-900 border-b border-slate-800">
            <!-- Logo y navegación -->
            <div class="flex items-center gap-6">
                <router-link to="/" class="flex items-center gap-2 hover:opacity-75 transition-opacity">
                    <i class="pi pi-arrow-left text-slate-400"></i>
                    <span class="text-white font-display font-bold text-xl">
                        QCFit<span class="text-violet-500">.</span>
                    </span>
                </router-link>
                <div class="h-6 w-px bg-slate-700"></div>
                <h1 class="text-white font-medium text-lg">The Studio</h1>
            </div>

            <!-- Stats del canvas -->
            <div class="flex items-center gap-4 text-sm text-slate-400">
                <div class="flex items-center gap-2">
                    <i class="pi pi-images"></i>
                    <span>{{ canvasStore.canvasItems.length }} items</span>
                </div>
                <div v-if="canvasStore.selectedItem" class="flex items-center gap-2 text-violet-400">
                    <i class="pi pi-check-circle"></i>
                    <span>{{ canvasStore.selectedItem.productName }}</span>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <CanvasToolbar @save="handleSave" @export="handleExport" />

        <!-- Área principal: Sidebar + Canvas -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Sidebar -->
            <CanvasSidebar />

            <!-- Canvas Editor -->
            <div class="flex-1 overflow-hidden">
                <CanvasEditor />
            </div>
        </div>

        <!-- Modal de Guardar Outfit -->
        <Dialog 
            v-model:visible="showSaveModal"
            modal
            header="Guardar Outfit"
            :style="{ width: '450px' }"
            :draggable="false"
        >
            <div class="py-4">
                <label for="outfit-title" class="block text-sm font-medium text-slate-700 mb-2">
                    Título del Outfit
                </label>
                <input
                    id="outfit-title"
                    v-model="outfitTitle"
                    type="text"
                    placeholder="Ej: Summer Street Style 2024"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-violet-600 focus:border-transparent"
                    @keyup.enter="confirmSave"
                    autofocus
                >
                <p class="mt-2 text-xs text-slate-500">
                    Este título será visible para otros usuarios cuando compartas tu outfit
                </p>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <button
                        @click="cancelSave"
                        class="px-4 py-2 text-sm text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="confirmSave"
                        :disabled="saving || !outfitTitle.trim()"
                        class="px-6 py-2 text-sm bg-violet-600 hover:bg-violet-500 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center gap-2"
                    >
                        <i v-if="saving" class="pi pi-spin pi-spinner"></i>
                        <i v-else class="pi pi-check"></i>
                        {{ saving ? 'Guardando...' : 'Guardar Outfit' }}
                    </button>
                </div>
            </template>
        </Dialog>
    </div>
</template>

<style scoped>
.studio-view {
    /* Asegurar que ocupe toda la pantalla */
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}
</style>
