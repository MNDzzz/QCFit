<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Stage, Layer, Image as KonvaImage, Transformer } from 'vue-konva';
import { useCanvasStore } from '@/store/canvas';
import CanvasFloatingToolbar from './CanvasFloatingToolbar.vue';

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

/**
 * Exportar el canvas como imagen.
 * Genera un Data URL de la imagen que puede descargarse o enviarse al servidor.
 * 
 * @param {Object} options - Opciones de exportación
 * @param {string} options.mimeType - Tipo de imagen ('image/png' o 'image/jpeg')
 * @param {number} options.quality - Calidad para JPEG (0-1)
 * @param {number} options.pixelRatio - Ratio de píxeles para mayor resolución
 * @returns {string|null} Data URL de la imagen o null si falla
 */
function exportToImage(options = {}) {
    const {
        mimeType = 'image/png',
        quality = 0.92,
        pixelRatio = 2,
    } = options;

    if (!stageRef.value) {
        console.error('No hay referencia al Stage de Konva');
        return null;
    }

    try {
        // Ocultar el transformer antes de exportar
        if (transformerRef.value) {
            const transformerNode = transformerRef.value.getNode();
            transformerNode.nodes([]);
        }

        // Obtener el stage de Konva
        const stage = stageRef.value.getNode();

        // Generar Data URL de la imagen
        const dataURL = stage.toDataURL({
            mimeType: mimeType,
            quality: quality,
            pixelRatio: pixelRatio,
        });

        // Restaurar la selección después de exportar
        updateTransformer();

        return dataURL;

    } catch (error) {
        console.error('Error al exportar imagen:', error);
        return null;
    }
}

/**
 * Descargar el canvas como archivo de imagen.
 * Genera la imagen y la descarga automáticamente.
 * 
 * @param {string} filename - Nombre del archivo (sin extensión)
 * @param {string} format - Formato de imagen ('png' o 'jpeg')
 */
function downloadImage(filename = 'outfit', format = 'png') {
    const mimeType = format === 'jpeg' ? 'image/jpeg' : 'image/png';
    const dataURL = exportToImage({ mimeType });

    if (!dataURL) {
        alert('Error al generar la imagen. Intenta nuevamente.');
        return;
    }

    // Crear enlace de descarga
    const link = document.createElement('a');
    link.download = `${filename}.${format}`;
    link.href = dataURL;
    
    // Simular click para descargar
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Manejar la acción "Remove BG" del Floating Toolbar.
 * Por ahora es un placeholder para futura integración con IA.
 * @param {string} itemId - ID del item al que quitar el fondo
 */
function handleRemoveBg(itemId) {
    console.log('Remove BG solicitado para item:', itemId);
    // TODO: Integrar con endpoint /api/ai/remove-bg
    alert('La función "Quitar Fondo" con IA estará disponible pronto.');
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

// Exponer funciones al componente padre
defineExpose({
    exportToImage,
    downloadImage,
});
</script>

<template>
    <div class="canvas-editor-container relative w-full h-full bg-transparent">
        <!-- Patrón de fondo tipo Checkerboard (Dark Mode Subtler) -->
         <div class="absolute inset-0 opacity-[0.03]" 
             style="background-image: linear-gradient(45deg, #808080 25%, transparent 25%), linear-gradient(-45deg, #808080 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #808080 75%), linear-gradient(-45deg, transparent 75%, #808080 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;">
        </div>

        <!-- Canvas Stage -->
        <div class="relative flex items-center justify-center w-full h-full overflow-hidden">
            <!-- Stage sin sombra ni fondo blanco, flotando en el espacio -->
            <Stage 
                ref="stageRef"
                :config="stageConfig"
                @mousedown="handleStageClick"
                @touchstart="handleStageClick"
                class="bg-transparent"
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
                            anchorSize: 10,
                            anchorCornerRadius: 5,
                            keepRatio: true
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
            <div class="text-center text-slate-700">
                <i class="pi pi-plus-circle text-6xl mb-4 opacity-20"></i>
                <p class="text-lg font-bold uppercase tracking-widest opacity-40">Start Creating</p>
                <p class="text-xs mt-2 opacity-30">Drag items from wardrobe</p>
            </div>
        </div>

        <!-- Floating Toolbar Contextual (aparece sobre el item seleccionado) -->
        <CanvasFloatingToolbar @remove-bg="handleRemoveBg" />
    </div>
</template>

<style scoped>
.canvas-editor-container {
    /* Asegurar que el contenedor tenga altura definida */
    min-height: 700px;
}
</style>
