<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Stage, Layer, Image as KonvaImage, Transformer } from 'vue-konva';
import { useCanvasStore } from '@/store/canvas';

const canvasStore = useCanvasStore();

// Configuración del Stage (lienzo principal)
const stageConfig = ref({
    width: 1000,
    height: 700,
});

// Referencias a elementos de Konva
const transformerRef = ref(null);
const stageRef = ref(null);

// Imágenes cargadas (necesario para Konva)
const loadedImages = ref({});

// Computed: Items ordenados por z-index
const sortedItems = computed(() => canvasStore.sortedItems);

// Computed: Item seleccionado
const selectedItemId = computed(() => canvasStore.selectedId);

// Watch: Cuando cambian los items del store, cargar las imágenes
watch(() => canvasStore.canvasItems, (items) => {
    items.forEach(item => {
        if (!loadedImages.value[item.id]) {
            loadImage(item.id, item.imageUrl);
        }
    });
}, { deep: true });

// Cargar imagen para Konva
function loadImage(itemId, url) {
    const imageObj = new window.Image();
    imageObj.crossOrigin = 'Anonymous';
    
    imageObj.onload = () => {
        loadedImages.value[itemId] = imageObj;
    };
    
    imageObj.onerror = () => {
        console.error(`Error al cargar imagen para item ${itemId}:`, url);
    };
    
    imageObj.src = url;
}

// Obtener configuración de imagen para Konva
function getImageConfig(item) {
    return {
        image: loadedImages.value[item.id],
        x: item.x,
        y: item.y,
        scaleX: item.scaleX,
        scaleY: item.scaleY,
        rotation: item.rotation,
        draggable: true,
        name: item.id,
    };
}

// Manejar click en el stage (seleccionar/deseleccionar)
function handleStageClick(e) {
    // Click en área vacía -> deseleccionar
    if (e.target === e.target.getStage()) {
        canvasStore.selectItem(null);
        updateTransformer();
        return;
    }

    // Click en transformer -> ignorar
    const clickedOnTransformer = e.target.getParent().className === 'Transformer';
    if (clickedOnTransformer) {
        return;
    }

    // Click en imagen -> seleccionar
    const name = e.target.name();
    if (name) {
        canvasStore.selectItem(name);
        updateTransformer();
    }
}

// Manejar click en imagen específica
function handleImageClick(itemId) {
    canvasStore.selectItem(itemId);
    updateTransformer();
}

// Manejar fin de arrastre de imagen
function handleDragEnd(itemId, e) {
    const node = e.target;
    
    canvasStore.updateItem(itemId, {
        x: node.x(),
        y: node.y(),
    });
}

// Manejar transformación (escalar, rotar)
function handleTransformEnd(itemId, e) {
    const node = e.target;
    
    canvasStore.updateItem(itemId, {
        x: node.x(),
        y: node.y(),
        rotation: node.rotation(),
        scaleX: node.scaleX(),
        scaleY: node.scaleY(),
    });
}

// Actualizar transformer para el item seleccionado
function updateTransformer() {
    if (!transformerRef.value) return;

    const transformerNode = transformerRef.value.getNode();
    const stage = transformerNode.getStage();
    
    if (!selectedItemId.value) {
        transformerNode.nodes([]);
        return;
    }

    const selectedNode = stage.findOne(`.${selectedItemId.value}`);
    
    if (selectedNode) {
        transformerNode.nodes([selectedNode]);
    } else {
        transformerNode.nodes([]);
    }
}

// Watch: Actualizar transformer cuando cambia la selección
watch(selectedItemId, () => {
    updateTransformer();
});

// Cargar imágenes existentes al montar el componente
onMounted(() => {
    canvasStore.canvasItems.forEach(item => {
        loadImage(item.id, item.imageUrl);
    });
});
</script>

<template>
    <div class="canvas-editor-container relative w-full h-full bg-stone-50">
        <!-- Patrón de fondo tipo Photoshop -->
        <div class="absolute inset-0 opacity-10" 
             style="background-image: repeating-conic-gradient(#808080 0% 25%, transparent 0% 50%) 50% / 20px 20px;">
        </div>

        <!-- Canvas Stage -->
        <div class="relative flex items-center justify-center w-full h-full p-8">
            <Stage 
                ref="stageRef"
                :config="stageConfig"
                @mousedown="handleStageClick"
                @touchstart="handleStageClick"
                class="bg-white shadow-2xl rounded-lg"
            >
                <Layer>
                    <!-- Renderizar todas las imágenes ordenadas por z-index -->
                    <KonvaImage
                        v-for="item in sortedItems"
                        :key="item.id"
                        :config="getImageConfig(item)"
                        @click="handleImageClick(item.id)"
                        @tap="handleImageClick(item.id)"
                        @dragend="(e) => handleDragEnd(item.id, e)"
                        @transformend="(e) => handleTransformEnd(item.id, e)"
                    />

                    <!-- Transformer para selección y transformación -->
                    <Transformer 
                        ref="transformerRef"
                        :config="{
                            rotateEnabled: true,
                            borderStroke: '#8B5CF6',
                            borderStrokeWidth: 2,
                            anchorFill: '#8B5CF6',
                            anchorStroke: '#FFFFFF',
                            anchorSize: 12,
                            anchorCornerRadius: 6,
                        }"
                    />
                </Layer>
            </Stage>
        </div>

        <!-- Indicador de estado vacío -->
        <div 
            v-if="sortedItems.length === 0"
            class="absolute inset-0 flex items-center justify-center pointer-events-none"
        >
            <div class="text-center text-slate-400">
                <i class="pi pi-image text-6xl mb-4 opacity-20"></i>
                <p class="text-lg font-medium">Arrastra productos aquí para crear tu outfit</p>
                <p class="text-sm mt-2">Busca productos en el sidebar y arrástralos al canvas</p>
            </div>
        </div>

        <!-- Indicador de item seleccionado -->
        <div 
            v-if="selectedItemId && canvasStore.selectedItem"
            class="absolute top-4 left-4 bg-slate-900/90 backdrop-blur-sm text-white px-4 py-2 rounded-lg shadow-lg"
        >
            <p class="text-xs font-medium opacity-75">Seleccionado:</p>
            <p class="text-sm font-bold">{{ canvasStore.selectedItem.productName }}</p>
        </div>
    </div>
</template>

<style scoped>
.canvas-editor-container {
    /* Asegurar que el contenedor tenga altura definida */
    min-height: 700px;
}
</style>
