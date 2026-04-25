<script setup>
import { computed } from 'vue';
import { useCanvasStore } from '@/store/canvas';

const canvasStore = useCanvasStore();

// Emits para comunicación con el padre (Vista Studio)
const emit = defineEmits(['save', 'export']);

// Computed: ¿Hay algún item seleccionado?
const hasSelection = computed(() => !!canvasStore.selectedId);

// Computed: ¿Hay items en el canvas?
const hasItems = computed(() => canvasStore.canvasItems.length > 0);

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
    
    if (confirm('Delete this item from canvas?')) {
        canvasStore.removeItem(canvasStore.selectedId);
    }
}

// Limpiar canvas
function clearCanvas() {
    if (!hasItems.value) return;
    
    if (confirm('Clear the entire canvas? This action cannot be undone.')) {
        canvasStore.clearCanvas();
    }
}

// Guardar outfit
function saveOutfit() {
    emit('save');
}

// Exportar como imagen
function exportImage() {
    emit('export');
}
</script>

<template>
    <div class="canvas-toolbar flex items-center justify-between px-6 py-3 bg-slate-900 border-b border-slate-800">
        <!-- Sección izquierda: Herramientas de transformación -->
        <div class="flex items-center gap-2">
            <!-- Layer Controls -->
            <div class="flex items-center gap-1 mr-4">
                <button
                    @click="bringToFront"
                    :disabled="!hasSelection"
                    :title="'Bring to front'"
                    class="px-3 py-2 text-sm bg-slate-800 hover:bg-slate-700 disabled:bg-slate-800/50 disabled:text-slate-600 disabled:cursor-not-allowed text-white rounded-lg transition-colors flex items-center gap-2"
                >
                    <i class="pi pi-arrow-up"></i>
                    <span class="hidden sm:inline">Front</span>
                </button>
                <button
                    @click="sendToBack"
                    :disabled="!hasSelection"
                    :title="'Send to back'"
                    class="px-3 py-2 text-sm bg-slate-800 hover:bg-slate-700 disabled:bg-slate-800/50 disabled:text-slate-600 disabled:cursor-not-allowed text-white rounded-lg transition-colors flex items-center gap-2"
                >
                    <i class="pi pi-arrow-down"></i>
                    <span class="hidden sm:inline">Back</span>
                </button>
            </div>

            <!-- Transform Controls -->
            <div class="flex items-center gap-1 mr-4">
                <button
                    @click="flipHorizontal"
                    :disabled="!hasSelection"
                    :title="'Flip horizontally'"
                    class="px-3 py-2 text-sm bg-slate-800 hover:bg-slate-700 disabled:bg-slate-800/50 disabled:text-slate-600 disabled:cursor-not-allowed text-white rounded-lg transition-colors flex items-center gap-2"
                >
                    <i class="pi pi-arrows-h"></i>
                    <span class="hidden sm:inline">Flip</span>
                </button>
            </div>

            <!-- Delete Controls -->
            <div class="flex items-center gap-1">
                <button
                    @click="removeSelected"
                    :disabled="!hasSelection"
                    :title="'Delete selected'"
                    class="px-3 py-2 text-sm bg-red-900/30 hover:bg-red-900/50 disabled:bg-slate-800/50 disabled:text-slate-600 disabled:cursor-not-allowed text-red-400 hover:text-red-300 disabled:text-slate-600 rounded-lg transition-colors flex items-center gap-2"
                >
                    <i class="pi pi-trash"></i>
                    <span class="hidden sm:inline">Delete</span>
                </button>
                <button
                    @click="clearCanvas"
                    :disabled="!hasItems"
                    :title="'Clear entire canvas'"
                    class="px-3 py-2 text-sm bg-slate-800 hover:bg-slate-700 disabled:bg-slate-800/50 disabled:text-slate-600 disabled:cursor-not-allowed text-slate-300 rounded-lg transition-colors flex items-center gap-2"
                >
                    <i class="pi pi-times-circle"></i>
                    <span class="hidden sm:inline">Clear all</span>
                </button>
            </div>
        </div>

        <!-- Sección derecha: Acciones principales -->
        <div class="flex items-center gap-2">
            <!-- Exportar como imagen -->
            <button
                @click="exportImage"
                :disabled="!hasItems"
                :title="'Export as image'"
                class="px-4 py-2 text-sm bg-slate-800 hover:bg-slate-700 disabled:bg-slate-800/50 disabled:text-slate-600 disabled:cursor-not-allowed text-white rounded-lg transition-colors flex items-center gap-2"
            >
                <i class="pi pi-download"></i>
                <span class="hidden sm:inline">Export</span>
            </button>

            <!-- Guardar Outfit -->
            <button
                @click="saveOutfit"
                :disabled="!hasItems"
                :title="'Save outfit'"
                class="px-6 py-2 text-sm bg-violet-600 hover:bg-violet-500 disabled:bg-slate-700 disabled:text-slate-500 disabled:cursor-not-allowed text-white font-bold rounded-lg transition-all transform hover:scale-105 shadow-lg shadow-violet-900/20 flex items-center gap-2"
            >
                <i class="pi pi-save"></i>
                Save Outfit
            </button>
        </div>
    </div>
</template>

<style scoped>
.canvas-toolbar {
    height: 60px;
    min-height: 60px;
}
</style>
