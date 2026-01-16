<script setup>
import { ref, onMounted } from 'vue';

const width = window.innerWidth * 0.8;
const height = 600;

const stageConfig = ref({
  width: width,
  height: height,
});

// Referencias a las capas y transformador
const transformer = ref(null);
const selectedShapeName = ref('');

// Lista de prendas disponibles (Mock para pruebas)
const availableProducts = ref([
    {
        id: 'prod1',
        name: 'Camiseta Nike',
        image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&q=80',
    },
    {
        id: 'prod2',
        name: 'Pantalones Cargo',
        image: 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&q=80',
    },
    {
        id: 'prod3',
        name: 'Sneakers Jordan',
        image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-1.2.1&auto=format&fit=crop&w=200&q=80',
    }
]);

// Elementos colocados en el canvas
const canvasItems = ref([]);

// --- Manejo del Transformer (Selección) ---
function updateTransformer() {
    // Aquí buscamos el nodo seleccionado en el stage
    const transformerNode = transformer.value.getNode();
    const stage = transformerNode.getStage();
    const { selectedShapeName: selectedName } = selectedShapeName.value;

    const selectedNode = stage.findOne('.' + selectedShapeName.value);

    // Si encontramos el nodo y no es el transformador mismo
    if (selectedNode === transformerNode) {
        return;
    }

    if (selectedNode) {
        // Conectamos el transformador al nodo
        transformerNode.nodes([selectedNode]);
    } else {
        // Desconectamos si no hay selección
        transformerNode.nodes([]);
    }
}

function handleStageMouseDown(e) {
    // Si hacemos click en una zona vacía, deseleccionamos
    if (e.target === e.target.getStage()) {
        selectedShapeName.value = '';
        updateTransformer();
        return;
    }

    // Si hacemos click en el transformador, no hacemos nada
    const clickedOnTransformer = e.target.getParent().className === 'Transformer';
    if (clickedOnTransformer) {
        return;
    }

    // Buscamos el nombre de la imagen seleccionada
    const name = e.target.name();
    const item = canvasItems.value.find((i) => i.id === name);
    
    if (item) {
        selectedShapeName.value = name;
    } else {
        selectedShapeName.value = '';
    }
    updateTransformer();
}

// --- Manejo de Drag & Drop (HTML5 -> Canvas) ---

// Variable para guardar qué estamos arrastrando desde el sidebar
const draggingItem = ref(null);

function onDragStartSidebar(item) {
    draggingItem.value = item;
}

function onDrop(e) {
    e.preventDefault();
    // Registramos la posición del stage para calcular coordenadas relativas
    const stage = transformer.value.getNode().getStage();
    stage.setPointersPositions(e);
    
    const pointerPosition = stage.getPointerPosition();

    if (draggingItem.value) {
        // Cargamos la imagen
        const imageObj = new Image();
        imageObj.src = draggingItem.value.image;
        
        imageObj.onload = () => {
            // Añadimos el item al array del canvas
            canvasItems.value.push({
                product_id: draggingItem.value.id,
                id: `item_${Date.now()}`, // ID único para Konva
                x: pointerPosition.x - 50, // Centrar aprox
                y: pointerPosition.y - 50,
                scaleX: 1,
                scaleY: 1,
                rotation: 0,
                image: imageObj,
                draggable: true,
            });
        };
    }
}

function onDragOver(e) {
    e.preventDefault(); // Necesario para permitir el drop
}

</script>

<template>
    <div class="flex h-screen bg-gray-100 p-4 gap-4">
        <!-- Sidebar de Productos -->
        <div class="w-1/4 bg-white rounded-xl shadow-lg p-4 overflow-y-auto">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Mi Armario</h2>
            <div class="grid grid-cols-2 gap-2">
                <div 
                    v-for="prod in availableProducts" 
                    :key="prod.id"
                    class="bg-gray-50 rounded p-2 cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow"
                    draggable="true"
                    @dragstart="onDragStartSidebar(prod)"
                >
                    <img :src="prod.image" class="w-full h-24 object-cover rounded mb-2 pointer-events-none">
                    <p class="text-xs font-semibold text-center truncate">{{ prod.name }}</p>
                </div>
            </div>
        </div>

        <!-- Área del Canvas -->
        <div 
            class="flex-1 bg-white rounded-xl shadow-lg flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300 relative"
            @drop="onDrop"
            @dragover="onDragOver"
        >
            <div class="absolute top-2 left-2 z-10 text-xs text-gray-400">
                Arrastra prendas aquí. Haz click para redimensionar (Konva).
            </div>

            <v-stage 
                :config="stageConfig" 
                @mousedown="handleStageMouseDown"
                @touchstart="handleStageMouseDown"
            >
                <v-layer>
                    <!-- Renderizamos las imágenes -->
                    <v-image 
                        v-for="item in canvasItems" 
                        :key="item.id"
                        :config="{
                            name: item.id,
                            image: item.image,
                            x: item.x,
                            y: item.y,
                            scaleX: item.scaleX,
                            scaleY: item.scaleY,
                            rotation: item.rotation,
                            draggable: true,
                        }"
                    />
                    
                    <!-- Transformador (Resize/Rotate) -->
                    <v-transformer ref="transformer" />
                </v-layer>
            </v-stage>
        </div>
    </div>
</template>
