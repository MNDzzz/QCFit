<script setup>
import { ref, onMounted } from 'vue';

// Basic Canvas sizing
const width = window.innerWidth * 0.8;
const height = 600;

// Config for the stage
const stageConfig = ref({
  width: width,
  height: height,
});

// Layers
const backgroundLayer = ref(null);
const mainLayer = ref(null);

// Items on the canvas
const items = ref([
    {
        id: 'rect1',
        x: 100,
        y: 100,
        width: 100,
        height: 100,
        fill: 'red',
        draggable: true,
        type: 'rect'
    },
    {
        id: 'text1',
        x: 200,
        y: 200,
        text: 'Arrastra prendas aquí',
        fontSize: 24,
        draggable: true,
        type: 'text'
    }
]);

// Drag and drop handlers would go here
function handleDragStart(e) {
  e.target.setAttrs({
    scaleX: 1.1,
    scaleY: 1.1,
    shadowOffsetX: 5,
    shadowOffsetY: 5,
    shadowBlur: 10,
    shadowOpacity: 0.6,
  });
}

function handleDragEnd(e) {
  e.target.to({
    duration: 0.5,
    easing: 'ElasticEaseOut',
    scaleX: 1,
    scaleY: 1,
    shadowOffsetX: 0,
    shadowOffsetY: 0,
    shadowBlur: 0,
  });
}
</script>

<template>
    <div class="flex flex-col items-center p-8 bg-gray-100 min-h-screen">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Editor de Outfits (Canvas)</h1>
        
        <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200">
            <v-stage :config="stageConfig">
                <v-layer ref="mainLayer">
                    <template v-for="item in items" :key="item.id">
                        <v-rect v-if="item.type === 'rect'" :config="item" @dragstart="handleDragStart" @dragend="handleDragEnd" />
                        <v-text v-if="item.type === 'text'" :config="item" @dragstart="handleDragStart" @dragend="handleDragEnd" />
                        <!-- Images will go here later -->
                    </template>
                </v-layer>
            </v-stage>
        </div>

        <div class="mt-4 flex gap-4">
            <div class="p-4 bg-white rounded shadow">
                <p class="font-bold">Herramientas</p>
                <!-- Tool palette placeholder -->
                <button class="bg-blue-500 text-white px-3 py-1 rounded mt-2">Añadir Texto</button>
                <button class="bg-indigo-500 text-white px-3 py-1 rounded mt-2 ml-2">Subir Imagen</button>
            </div>
        </div>
    </div>
</template>
