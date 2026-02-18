<script setup>
/**
 * Panel de Capas para el Canvas Studio.
 * Muestra la lista de items del canvas ordenados por z-index.
 * Permite reordenar capas, seleccionar items y mostrar/ocultar (futuro).
 */
import { computed, ref } from 'vue';
import { useCanvasStore } from '@/store/canvas';

const canvasStore = useCanvasStore();

// Items ordenados por z-index (de mayor a menor para mostrar arriba = al frente)
const sortedLayers = computed(() => {
    return [...canvasStore.canvasItems].sort((a, b) => b.zIndex - a.zIndex);
});

// Estado de drag
const draggedItemId = ref(null);
const dragOverItemId = ref(null);

/**
 * Seleccionar un item al hacer clic en la capa.
 */
function selectLayer(itemId) {
    canvasStore.selectItem(itemId);
}

/**
 * Mover capa hacia arriba (incrementar z-index)
 */
function moveLayerUp(itemId) {
    canvasStore.bringToFront(itemId);
}

/**
 * Mover capa hacia abajo (decrementar z-index)
 */
function moveLayerDown(itemId) {
    canvasStore.sendToBack(itemId);
}

/**
 * Eliminar item desde el panel de capas.
 */
function deleteLayer(itemId, productName) {
    if (confirm(`¿Eliminar "${productName}" del canvas?`)) {
        canvasStore.removeItem(itemId);
    }
}

// ---- Drag & Drop para reordenar ----
function onDragStart(event, itemId) {
    draggedItemId.value = itemId;
    event.dataTransfer.effectAllowed = 'move';
    // Añadir clase visual
    event.target.classList.add('dragging');
}

function onDragEnd(event) {
    draggedItemId.value = null;
    dragOverItemId.value = null;
    event.target.classList.remove('dragging');
}

function onDragOver(event, itemId) {
    event.preventDefault();
    if (itemId !== draggedItemId.value) {
        dragOverItemId.value = itemId;
    }
}

function onDragLeave(event) {
    dragOverItemId.value = null;
}

function onDrop(event, targetItemId) {
    event.preventDefault();
    
    if (!draggedItemId.value || draggedItemId.value === targetItemId) {
        draggedItemId.value = null;
        dragOverItemId.value = null;
        return;
    }

    // Intercambiar z-index entre los dos items
    const draggedItem = canvasStore.canvasItems.find(i => i.id === draggedItemId.value);
    const targetItem = canvasStore.canvasItems.find(i => i.id === targetItemId);

    if (draggedItem && targetItem) {
        const tempZIndex = draggedItem.zIndex;
        draggedItem.zIndex = targetItem.zIndex;
        targetItem.zIndex = tempZIndex;
    }

    draggedItemId.value = null;
    dragOverItemId.value = null;
}

/**
 * Truncar nombre de producto para la UI.
 */
function truncateName(name, maxLength = 18) {
    if (!name) return 'Item';
    return name.length > maxLength ? name.substring(0, maxLength) + '...' : name;
}
</script>

<template>
    <div class="canvas-layers-panel w-60 bg-slate-950 border-l border-slate-900/50 flex flex-col h-full">
        <!-- Cabecera del Panel -->
        <div class="panel-header px-4 py-3 border-b border-slate-900/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-white tracking-wide flex items-center gap-2">
                <i class="pi pi-layers text-violet-500"></i>
                Layers
            </h3>
            <span class="text-xs text-slate-500 font-mono">{{ canvasStore.canvasItems.length }}</span>
        </div>

        <!-- Lista de Capas -->
        <div class="layers-list flex-1 overflow-y-auto py-2">
            <!-- Estado vacío -->
            <div v-if="canvasStore.canvasItems.length === 0" class="empty-state px-4 py-8 text-center">
                <i class="pi pi-inbox text-3xl text-slate-700 mb-3 block"></i>
                <p class="text-xs text-slate-600">No hay capas</p>
                <p class="text-[10px] text-slate-700 mt-1">Arrastra productos al canvas</p>
            </div>

            <!-- Items como capas -->
            <div 
                v-for="item in sortedLayers" 
                :key="item.id"
                :draggable="true"
                @dragstart="onDragStart($event, item.id)"
                @dragend="onDragEnd"
                @dragover="onDragOver($event, item.id)"
                @dragleave="onDragLeave"
                @drop="onDrop($event, item.id)"
                @click="selectLayer(item.id)"
                class="layer-item group flex items-center gap-3 px-4 py-2.5 cursor-pointer transition-all duration-150"
                :class="{
                    'bg-violet-600/20 border-l-2 border-violet-500': canvasStore.selectedId === item.id,
                    'hover:bg-slate-900/50 border-l-2 border-transparent': canvasStore.selectedId !== item.id,
                    'bg-slate-800/50 border-dashed border-violet-400': dragOverItemId === item.id && draggedItemId !== item.id,
                    'opacity-50': draggedItemId === item.id
                }"
            >
                <!-- Grip para arrastrar -->
                <div class="drag-handle cursor-grab active:cursor-grabbing text-slate-600 group-hover:text-slate-400 transition-colors">
                    <i class="pi pi-ellipsis-v text-xs"></i>
                </div>

                <!-- Thumbnail del item -->
                <div class="layer-thumbnail w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden flex-shrink-0">
                    <img 
                        v-if="item.imageUrl" 
                        :src="item.imageUrl" 
                        referrerpolicy="no-referrer"
                        :alt="item.productName"
                        class="w-full h-full object-cover"
                        :class="{ 'scale-x-[-1]': item.isFlipped }"
                    >
                    <div v-else class="w-full h-full flex items-center justify-center">
                        <i class="pi pi-image text-slate-600"></i>
                    </div>
                </div>

                <!-- Info del item -->
                <div class="layer-info flex-1 min-w-0">
                    <p 
                        class="text-xs font-medium truncate"
                        :class="canvasStore.selectedId === item.id ? 'text-white' : 'text-slate-300'"
                    >
                        {{ truncateName(item.productName) }}
                    </p>
                    <p class="text-[10px] text-slate-500 font-mono mt-0.5">
                        z: {{ item.zIndex }}
                    </p>
                </div>

                <!-- Acciones rápidas (visibles en hover) -->
                <div class="layer-actions flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button 
                        @click.stop="moveLayerUp(item.id)"
                        class="w-6 h-6 rounded flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-colors"
                        title="Subir capa"
                    >
                        <i class="pi pi-chevron-up text-[10px]"></i>
                    </button>
                    <button 
                        @click.stop="moveLayerDown(item.id)"
                        class="w-6 h-6 rounded flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-colors"
                        title="Bajar capa"
                    >
                        <i class="pi pi-chevron-down text-[10px]"></i>
                    </button>
                    <button 
                        @click.stop="deleteLayer(item.id, item.productName)"
                        class="w-6 h-6 rounded flex items-center justify-center text-slate-400 hover:text-red-400 hover:bg-red-900/30 transition-colors"
                        title="Eliminar"
                    >
                        <i class="pi pi-trash text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer con info adicional -->
        <div class="panel-footer px-4 py-3 border-t border-slate-900/50 text-[10px] text-slate-600">
            <p><i class="pi pi-info-circle mr-1"></i> Arrastra para reordenar</p>
        </div>
    </div>
</template>

<style scoped>
/* Animación de drag */
.layer-item.dragging {
    opacity: 0.4;
    background: rgba(124, 58, 237, 0.1);
}

/* Scrollbar personalizado */
.layers-list::-webkit-scrollbar {
    width: 4px;
}

.layers-list::-webkit-scrollbar-track {
    background: transparent;
}

.layers-list::-webkit-scrollbar-thumb {
    background: rgb(51, 65, 85);
    border-radius: 4px;
}

.layers-list::-webkit-scrollbar-thumb:hover {
    background: rgb(71, 85, 105);
}
</style>
