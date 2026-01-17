<script setup>
/**
 * Toolbar Flotante Contextual para el Canvas Studio.
 * Aparece flotando sobre el item seleccionado con acciones rápidas.
 * Siguiendo el diseño de referencia (Imagen 4).
 */
import { computed } from 'vue';
import { useCanvasStore } from '@/store/canvas';

const canvasStore = useCanvasStore();

// Emits para comunicar acciones al padre
const emit = defineEmits(['remove-bg']);

// ¿Hay algún item seleccionado?
const hasSelection = computed(() => !!canvasStore.selectedId);

// Obtener item seleccionado
const selectedItem = computed(() => canvasStore.selectedItem);

/**
 * Calcular posición del toolbar flotante.
 * Se posiciona encima del item seleccionado.
 */
const toolbarPosition = computed(() => {
    if (!selectedItem.value) {
        return { top: '50%', left: '50%' };
    }
    
    // Posición estimada (ajustar según viewport)
    // El toolbar aparece encima del item, centrado horizontalmente
    const itemX = selectedItem.value.x || 200;
    const itemY = selectedItem.value.y || 200;
    
    // Offset del sidebar izquierdo (~240px) y toolbar superior (~60px)
    // Ajustar para centrar sobre el item
    return {
        top: `${Math.max(60, itemY - 60)}px`,
        left: `${Math.max(100, itemX + 100)}px`,
    };
});

// Traer al frente
function bringToFront() {
    if (!canvasStore.selectedId) return;
    canvasStore.bringToFront(canvasStore.selectedId);
}

// Enviar atrás
function sendToBack() {
    if (!canvasStore.selectedId) return;
    canvasStore.sendToBack(canvasStore.selectedId);
}

// Voltear horizontalmente
function flipHorizontal() {
    if (!canvasStore.selectedId) return;
    canvasStore.flipItem(canvasStore.selectedId);
}

// Eliminar seleccionado
function removeSelected() {
    if (!canvasStore.selectedId) return;
    
    if (confirm('¿Eliminar este item del canvas?')) {
        canvasStore.removeItem(canvasStore.selectedId);
    }
}

// Quitar fondo (emitir al padre para la lógica de IA)
function removeBg() {
    if (!canvasStore.selectedId) return;
    emit('remove-bg', canvasStore.selectedId);
}
</script>

<template>
    <!-- Toolbar Flotante - Solo visible cuando hay selección -->
    <Transition
        enter-active-class="transition-all duration-200 ease-out"
        enter-from-class="opacity-0 transform scale-95 -translate-y-2"
        enter-to-class="opacity-100 transform scale-100 translate-y-0"
        leave-active-class="transition-all duration-150 ease-in"
        leave-from-class="opacity-100 transform scale-100"
        leave-to-class="opacity-0 transform scale-95"
    >
        <div 
            v-if="hasSelection"
            class="floating-toolbar absolute z-50 flex items-center gap-1 px-2 py-1.5 bg-slate-900/95 backdrop-blur-sm rounded-full shadow-2xl shadow-black/50 border border-slate-800/50"
            :style="toolbarPosition"
        >
            <!-- Remove BG - Botón especial con gradiente -->
            <button
                @click="removeBg"
                class="flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-violet-600 to-fuchsia-600 hover:from-violet-500 hover:to-fuchsia-500 text-white text-xs font-bold rounded-full transition-all transform hover:scale-105 shadow-lg shadow-violet-900/30"
                title="Quitar fondo (IA)"
            >
                <i class="pi pi-sparkles text-[10px]"></i>
                <span>Remove BG</span>
            </button>

            <!-- Separador -->
            <div class="w-px h-5 bg-slate-700 mx-1"></div>

            <!-- Bring Front -->
            <button
                @click="bringToFront"
                class="w-8 h-8 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                title="Traer al frente"
            >
                <i class="pi pi-arrow-up text-sm"></i>
            </button>

            <!-- Send Back -->
            <button
                @click="sendToBack"
                class="w-8 h-8 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                title="Enviar atrás"
            >
                <i class="pi pi-arrow-down text-sm"></i>
            </button>

            <!-- Separador -->
            <div class="w-px h-5 bg-slate-700 mx-0.5"></div>

            <!-- Flip -->
            <button
                @click="flipHorizontal"
                class="w-8 h-8 flex items-center justify-center text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                title="Voltear horizontalmente"
            >
                <i class="pi pi-arrows-h text-sm"></i>
            </button>

            <!-- Separador -->
            <div class="w-px h-5 bg-slate-700 mx-0.5"></div>

            <!-- Trash - Botón rojo -->
            <button
                @click="removeSelected"
                class="w-8 h-8 flex items-center justify-center text-red-400 hover:text-red-300 hover:bg-red-900/30 rounded-lg transition-colors"
                title="Eliminar"
            >
                <i class="pi pi-trash text-sm"></i>
            </button>
        </div>
    </Transition>
</template>

<style scoped>
/* Sombra personalizada para el tooltip flotante */
.floating-toolbar {
    box-shadow: 
        0 4px 6px -1px rgba(0, 0, 0, 0.3),
        0 10px 20px -5px rgba(0, 0, 0, 0.4),
        0 0 40px -10px rgba(139, 92, 246, 0.2);
}
</style>
