<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Konva from 'konva';

const width = window.innerWidth * 0.8;
const height = 600;

const stageConfig = ref({
    width: width,
    height: height,
});

// Referencias a las capas y transformador
const transformer = ref(null);
const selectedShapeName = ref('');

// Estado
const availableProducts = ref([]);
const canvasItems = ref([]);
const draggingItem = ref(null);

// Modal de Selección de Imagen
const showImageSelect = ref(false);
const selectedProduct = ref(null);
const productImages = ref({ qc: [], original: [], user_upload: [] });
const dropPosition = ref({ x: 0, y: 0 });

// AI State
const processingAi = ref(false);

// --- Carga Inicial ---
onMounted(async () => {
    fetchProducts();
    checkRemixMode();
});

const route = useRoute();
import { useRoute } from 'vue-router';

// Remix Mode
async function checkRemixMode() {
    const outfitId = route.query.outfit_id;
    if (!outfitId) return;

    try {
        const res = await axios.get(`/api/outfits/${outfitId}`);
        const outfit = res.data;
        
        // Populate Canvas
        // We need to recreate the items structure
        const newItems = [];
        
        for (const prod of outfit.products) {
            // Find the image URL. The pivot selected_image_id tells us which one
            // But we might need to find it in the prod.images array
            if (!prod.pivot) continue;

            const targetImage = prod.images.find(img => img.id == prod.pivot.selected_image_id) || prod.images[0];
            const imageUrl = targetImage ? targetImage.url : '';
            
            if (imageUrl) {
                 await new Promise((resolve) => {
                    const imageObj = new Image();
                    imageObj.src = imageUrl;
                    imageObj.crossOrigin = "Anonymous";
                    imageObj.onload = () => {
                        newItems.push({
                            product_id: prod.id,
                            selected_image_id: targetImage.id,
                            id: `item_${Date.now()}_${Math.random()}`,
                            x: parseFloat(prod.pivot.pos_x || 0),
                            y: parseFloat(prod.pivot.pos_y || 0),
                            scaleX: parseFloat(prod.pivot.scale_x || 1),
                            scaleY: parseFloat(prod.pivot.scale_y || 1),
                            rotation: parseFloat(prod.pivot.rotation || 0),
                            draggable: true,
                            image: imageObj,
                            imageUrl: imageUrl
                        });
                        resolve();
                    };
                    imageObj.onerror = resolve; // Continue even if one fails
                 });
            }
        }
        
        if (newItems.length > 0) {
            canvasItems.value = newItems;
        }

    } catch (e) {
        console.error("Error loading remix outfit", e);
    }
}

async function fetchProducts() {
    try {
        const res = await axios.get('/api/products/search?limit=20');
        availableProducts.value = res.data;
    } catch (e) {
        console.error("Error cargando productos", e);
    }
}

// --- Manejo del Transformer (Selección) ---
function updateTransformer() {
    const transformerNode = transformer.value.getNode();
    const stage = transformerNode.getStage();
    const selectedNode = stage.findOne('.' + selectedShapeName.value);

    if (selectedNode === transformerNode) {
        return;
    }

    if (selectedNode) {
        transformerNode.nodes([selectedNode]);
    } else {
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

    const clickedOnTransformer = e.target.getParent().className === 'Transformer';
    if (clickedOnTransformer) {
        return;
    }

    const name = e.target.name();
    const item = canvasItems.value.find((i) => i.id === name);
    
    if (item) {
        selectedShapeName.value = name;
    } else {
        selectedShapeName.value = '';
    }
    updateTransformer();
}

// --- Drag & Drop Flow ---

function onDragStartSidebar(item) {
    draggingItem.value = item;
}

function onDrop(e) {
    e.preventDefault();
    if (!draggingItem.value) return;

    // Registramos la posición del drop
    const stage = transformer.value.getNode().getStage();
    stage.setPointersPositions(e);
    const pos = stage.getPointerPosition();
    dropPosition.value = { x: pos.x - 50, y: pos.y - 50 };

    // Abrimos modal de selección
    openImageSelection(draggingItem.value);
}

function onDragOver(e) {
    e.preventDefault();
}

// --- Lógica del Modal ---

async function openImageSelection(product) {
    selectedProduct.value = product;
    showImageSelect.value = true;
    productImages.value = { qc: [], original: [], user_upload: [] };

    try {
        // Cargar detalle del producto con sus imágenes
        const res = await axios.get(`/api/products/${product.id}`);
        const images = res.data.images || [];
        
        // Clasificar imágenes
        images.forEach(img => {
            if (productImages.value[img.type]) {
                productImages.value[img.type].push(img);
            }
        });
        
    } catch (e) {
        console.error("Error cargando imagenes del producto", e);
    }
}

function selectImage(img) {
    addImageToCanvas(img.url, img.id);
    showImageSelect.value = false;
    selectedProduct.value = null;
    draggingItem.value = null;
}

function addImageToCanvas(url, imageId) {
    const imageObj = new Image();
    imageObj.src = url;
    imageObj.crossOrigin = "Anonymous"; // Importante para Canvas export

    imageObj.onload = () => {
        canvasItems.value.push({
            product_id: selectedProduct.value.id,
            selected_image_id: imageId,
            id: `item_${Date.now()}`,
            x: dropPosition.value.x,
            y: dropPosition.value.y,
            scaleX: 1,
            scaleY: 1,
            rotation: 0,
            image: imageObj,
            draggable: true,
            imageUrl: url // Guardamos la URL para operaciones IA
        });
    };
}

// --- AI Background Removal ---
async function removeBackground() {
    if (!selectedShapeName.value) return;

    const itemIndex = canvasItems.value.findIndex(i => i.id === selectedShapeName.value);
    if (itemIndex === -1) return;
    
    const item = canvasItems.value[itemIndex];
    if (!item.imageUrl) return;

    processingAi.value = true;
    try {
        const res = await axios.post('/api/ai/remove-bg', {
             image_url: item.imageUrl
        });

        // En un caso real, la URL cambiaría. 
        // Aquí simulamos que se "actualiza" la imagen.
        // Forzamos update de la imagen en Konva
        const newUrl = res.data.processed_url; 
        
        const imageObj = new Image();
        imageObj.src = newUrl;
        imageObj.crossOrigin = "Anonymous";
        
        imageObj.onload = () => {
            // Actualizamos la referencia de imagen en el item
            canvasItems.value[itemIndex].image = imageObj;
            // Hack para forzar reactividad si fuera necesario, 
            // pero Konva suele reaccionar al cambio de objeto Image.
            processingAi.value = false;
            alert(res.data.message);
        };
        
    } catch (e) {
        console.error(e);
        processingAi.value = false;
        alert("Error removing background");
    }
}

function deleteSelectedItem() {
     if (!selectedShapeName.value) return;
     canvasItems.value = canvasItems.value.filter(i => i.id !== selectedShapeName.value);
     selectedShapeName.value = '';
     updateTransformer();
}
</script>

<template>
    <div class="flex h-screen bg-gray-100 p-4 gap-4">
        <!-- Sidebar de Productos -->
        <div class="w-1/4 bg-white rounded-xl shadow-lg p-4 overflow-y-auto flex flex-col">
            <h2 class="text-xl font-bold mb-4 text-gray-800">My Wardrobe</h2>
            
            <div v-if="availableProducts.length === 0" class="text-gray-400 text-center py-4">
                No products available. Search on Home to add some.
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div 
                    v-for="prod in availableProducts" 
                    :key="prod.id"
                    class="bg-gray-50 rounded p-2 cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow border border-gray-100"
                    draggable="true"
                    @dragstart="onDragStartSidebar(prod)"
                >
                    <!-- Mostrar thumbnail (usar primera imagen si existe) -->
                    <div class="h-24 bg-gray-200 rounded mb-2 overflow-hidden">
                         <img 
                            v-if="prod.images && prod.images.length" 
                            :src="prod.images[0].url" 
                            referrerpolicy="no-referrer"
                            class="w-full h-full object-cover pointer-events-none"
                        >
                        <div v-else class="flex items-center justify-center h-full text-xs text-gray-400">No photo</div>
                    </div>
                    <p class="text-xs font-semibold text-center truncate">{{ prod.name }}</p>
                    <p class="text-[10px] text-center text-gray-500">{{ prod.brand }}</p>
                </div>
            </div>
        </div>

        <!-- Área del Canvas -->
        <div 
            class="flex-1 bg-white rounded-xl shadow-lg flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300 relative bg-[url('https://www.transparenttextures.com/patterns/graphy.png')]"
            @drop="onDrop"
            @dragover="onDragOver"
        >
            <div class="absolute top-2 left-2 z-10 text-xs text-gray-400 bg-white/80 p-1 rounded">
                Drag items here. Click to transform.
            </div>

            <!-- Toolbar Contextual -->
            <div v-if="selectedShapeName" class="absolute top-2 right-2 z-20 flex gap-2">
                 <button 
                    @click="removeBackground" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded shadow flex items-center gap-1 transition-colors"
                >
                    <i v-if="processingAi" class="pi pi-spin pi-spinner"></i>
                    <i v-else class="pi pi-sparkles"></i>
                    IA Remove BG
                </button>
                <button 
                    @click="deleteSelectedItem" 
                    class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded shadow flex items-center gap-1 transition-colors"
                >
                    <i class="pi pi-trash"></i>
                </button>
            </div>

            <v-stage 
                :config="stageConfig" 
                @mousedown="handleStageMouseDown"
                @touchstart="handleStageMouseDown"
            >
                <v-layer>
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
                    <v-transformer ref="transformer" />
                </v-layer>
            </v-stage>
        </div>

        <!-- Modal de Selección de Imagen -->
        <Dialog 
            v-model:visible="showImageSelect" 
            modal 
            header="Choose a variant" 
            :style="{ width: '50vw' }"
            :draggable="false"
        >
            <div v-if="selectedProduct">
                <p class="mb-4 text-gray-600">Select image for: <span class="font-bold">{{ selectedProduct.name }}</span></p>
                
                <div class="mb-6">
                    <h3 class="text-sm font-bold uppercase text-gray-400 mb-2">QCs (Quality Control)</h3>
                    <div class="flex gap-2 overflow-x-auto pb-2">
                        <div v-if="productImages.qc.length === 0" class="text-sm text-gray-400 italic">No QCs available</div>
                        <img 
                            v-for="img in productImages.qc" 
                            :key="img.id"
                            :src="img.url" 
                            referrerpolicy="no-referrer"
                            class="w-24 h-24 object-cover rounded cursor-pointer border-2 border-transparent hover:border-indigo-500"
                            @click="selectImage(img)"
                        >
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold uppercase text-gray-400 mb-2">Official / Marketing</h3>
                    <div class="flex gap-2 overflow-x-auto pb-2">
                        <div v-if="productImages.original.length === 0" class="text-sm text-gray-400 italic">No official photos</div>
                        <img 
                            v-for="img in productImages.original" 
                            :key="img.id"
                            :src="img.url" 
                            referrerpolicy="no-referrer"
                            class="w-24 h-24 object-cover rounded cursor-pointer border-2 border-transparent hover:border-indigo-500"
                            @click="selectImage(img)"
                        >
                    </div>
                </div>
            </div>
        </Dialog>
    </div>
</template>
