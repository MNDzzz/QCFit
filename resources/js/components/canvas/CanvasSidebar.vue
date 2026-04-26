<script setup>
import { ref, onMounted } from 'vue';
import { useCanvasStore } from '@/store/canvas';
import useProducts from '@/composables/products';

const canvasStore = useCanvasStore();

// Reutilizamos el composable de productos (mismo que usa el Panel Admin)
const { products: searchResults, isLoading: searchLoading, searchProducts: composableSearch, getProducts } = useProducts();

// Instancia separada para el wardrobe (evita colisiones de estado con la búsqueda)
const wardrobeProducts = useProducts();

// Tabs
const activeTab = ref('search');

// Búsqueda
const searchQuery = ref('');

// Wardrobe (productos favoritos / recientes)
const wardrobeItems = ref([]);
const wardrobeLoading = ref(false);

// Uploads
const uploadedImages = ref([]);
const isDraggingFile = ref(false);

// Estado de drag & drop
const draggedProduct = ref(null);

// Cargar productos del wardrobe al montar
onMounted(() => {
    loadWardrobe();
});

// Buscar productos (reutiliza el composable en vez de axios directo)
async function searchProducts() {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    await composableSearch(searchQuery.value, { limit: 12 });
}

// Cargar productos del wardrobe usando el composable
async function loadWardrobe() {
    wardrobeLoading.value = true;
    
    try {
        await wardrobeProducts.getProducts({ limit: 12 });
        wardrobeItems.value = wardrobeProducts.products.value || [];
    } catch (error) {
        console.error('Error cargando wardrobe:', error);
        wardrobeItems.value = [];
    } finally {
        wardrobeLoading.value = false;
    }
}

// Iniciar drag de producto
function handleDragStart(product, event) {
    draggedProduct.value = product;
    
    // Añadir datos de transferencia
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'copy';
        event.dataTransfer.setData('text/plain', JSON.stringify(product));
    }
}

// Añadir producto al canvas con selección de imagen
function addProductToCanvas(product) {
    // Si el producto tiene imágenes, usar la primera QC o la primera disponible
    const qcImage = product.images?.find(img => img.type === 'qc');
    const firstImage = qcImage || product.images?.[0];
    
    if (firstImage) {
        canvasStore.addItem(product, firstImage.url);
    } else {
        alert('Este producto no tiene imágenes disponibles');
    }
}

// --- Logic for Uploads ---

function onFileSelect(event) {
    const files = event.target.files;
    processFiles(files);
}

function onFileDrop(event) {
    isDraggingFile.value = false;
    const files = event.dataTransfer.files;
    processFiles(files);
}

function processFiles(files) {
    if (!files || files.length === 0) return;

    Array.from(files).forEach(file => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            // Creamos un objeto "mock" de producto para que sea compatible con el canvasStore
            const mockProduct = {
                id: `upload_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                name: file.name,
                brand: 'Personal Upload',
                is_upload: true,
                images: [{
                    url: e.target.result,
                    type: 'uploaded'
                }]
            };
            
            // Añadir al inicio
            uploadedImages.value.unshift(mockProduct);
        };
        reader.readAsDataURL(file);
    });
}
</script>

<template>
    <div class="canvas-sidebar h-full flex flex-col bg-[#0B0F19]/80 backdrop-blur-md text-white border-r border-white/5 relative z-10">
        <!-- Tabs -->
        <div class="flex border-b border-white/5">
            <button
                @click="activeTab = 'search'"
                :class="[
                    'flex-1 px-2 py-3 text-xs font-medium transition-colors border-b-2',
                    activeTab === 'search' 
                        ? 'bg-white/5 text-white border-violet-600' 
                        : 'text-slate-400 border-transparent hover:text-white hover:bg-white/5'
                ]"
            >
                <i class="pi pi-search mr-1"></i>
                Search
            </button>
            <button
                @click="activeTab = 'wardrobe'"
                :class="[
                    'flex-1 px-2 py-3 text-xs font-medium transition-colors border-b-2',
                    activeTab === 'wardrobe' 
                        ? 'bg-white/5 text-white border-violet-600' 
                        : 'text-slate-400 border-transparent hover:text-white hover:bg-white/5'
                ]"
            >
                <i class="pi pi-heart mr-1"></i>
                Wardrobe
            </button>
            <button
                @click="activeTab = 'uploads'"
                :class="[
                    'flex-1 px-2 py-3 text-xs font-medium transition-colors border-b-2',
                    activeTab === 'uploads' 
                        ? 'bg-white/5 text-white border-violet-600' 
                        : 'text-slate-400 border-transparent hover:text-white hover:bg-white/5'
                ]"
            >
                <i class="pi pi-upload mr-1"></i>
                Upload
            </button>
        </div>

        <!-- Tab Content: Search -->
        <div v-show="activeTab === 'search'" class="flex-1 flex flex-col overflow-hidden">
            <!-- Buscador -->
            <div class="p-4 border-b border-white/5">
                <div class="relative">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                    <input
                        v-model="searchQuery"
                        @keyup.enter="searchProducts"
                        type="text"
                        placeholder="Search products..."
                        class="w-full pl-10 pr-4 py-2 bg-black/40 border border-white/10 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:border-violet-600 focus:ring-1 focus:ring-violet-600"
                    >
                </div>
                <button
                    @click="searchProducts"
                    :disabled="searchLoading"
                    class="w-full mt-2 px-4 py-2 bg-violet-600 hover:bg-violet-500 disabled:bg-slate-700 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <i v-if="searchLoading" class="pi pi-spin pi-spinner mr-2"></i>
                    {{ searchLoading ? 'Searching...' : 'Search' }}
                </button>
            </div>

            <!-- Resultados de búsqueda -->
            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="searchResults.length === 0 && !searchLoading" class="text-center text-slate-500 py-8">
                    <i class="pi pi-inbox text-4xl mb-2 opacity-30"></i>
                    <p class="text-sm">Search products to add to your outfit</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div
                        v-for="product in searchResults"
                        :key="product.id"
                        draggable="true"
                        @dragstart="handleDragStart(product, $event)"
                        @click="addProductToCanvas(product)"
                        class="bg-white/5 border border-white/5 rounded-lg overflow-hidden cursor-grab active:cursor-grabbing hover:ring-2 hover:ring-violet-600 transition-all group"
                    >
                        <!-- Imagen del producto -->
                        <div class="aspect-square bg-black/40 overflow-hidden">
                            <img
                                v-if="(product.images && product.images.length) || product.thumbnail"
                                :src="product.thumbnail || product.images[0].url"
                                referrerpolicy="no-referrer"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                            >
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <i class="pi pi-image text-slate-700 text-3xl"></i>
                            </div>
                        </div>

                        <!-- Info del producto -->
                        <div class="p-2">
                            <p class="text-xs font-semibold truncate">{{ product.name }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ product.brand?.name || product.brand }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content: Wardrobe -->
        <div v-show="activeTab === 'wardrobe'" class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="wardrobeLoading" class="text-center text-slate-500 py-8">
                    <i class="pi pi-spin pi-spinner text-3xl mb-2"></i>
                    <p class="text-sm">Loading wardrobe...</p>
                </div>

                <div v-else-if="wardrobeItems.length === 0" class="text-center text-slate-500 py-8">
                    <i class="pi pi-heart text-4xl mb-2 opacity-30"></i>
                    <p class="text-sm">No saved products</p>
                    <p class="text-xs mt-2">Save products from search</p>
                </div>

                <div v-else class="grid grid-cols-2 gap-3">
                    <div
                        v-for="product in wardrobeItems"
                        :key="product.id"
                        draggable="true"
                        @dragstart="handleDragStart(product, $event)"
                        @click="addProductToCanvas(product)"
                        class="bg-white/5 border border-white/5 rounded-lg overflow-hidden cursor-grab active:cursor-grabbing hover:ring-2 hover:ring-violet-600 transition-all group"
                    >
                        <!-- Imagen del producto -->
                        <div class="aspect-square bg-black/40 overflow-hidden">
                            <img
                                v-if="(product.images && product.images.length) || product.thumbnail"
                                :src="product.thumbnail || product.images[0].url"
                                referrerpolicy="no-referrer"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                            >
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <i class="pi pi-image text-slate-700 text-3xl"></i>
                            </div>
                        </div>

                        <!-- Info del producto -->
                        <div class="p-2">
                            <p class="text-xs font-semibold truncate">{{ product.name }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ product.brand?.name || product.brand }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content: Uploads -->
        <div v-show="activeTab === 'uploads'" class="flex-1 flex flex-col overflow-hidden">
            <!-- Dropzone -->
            <div class="p-4 border-b border-white/5">
                <div 
                    class="border-2 border-dashed border-white/20 rounded-lg p-6 text-center transition-colors relative"
                    :class="{'border-violet-500 bg-violet-500/10': isDraggingFile, 'hover:border-white/40 hover:bg-white/5': !isDraggingFile}"
                    @dragover.prevent="isDraggingFile = true"
                    @dragleave.prevent="isDraggingFile = false"
                    @drop.prevent="onFileDrop"
                >
                    <input 
                        type="file" 
                        multiple 
                        accept="image/*" 
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        @change="onFileSelect"
                    >
                    <i class="pi pi-upload text-2xl text-slate-400 mb-2"></i>
                    <p class="text-xs text-slate-300 font-medium">Upload images</p>
                    <p class="text-[10px] text-slate-500 mt-1">Drag or click to select</p>
                </div>
            </div>

            <!-- Uploaded Items Grid -->
            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="uploadedImages.length === 0" class="text-center text-slate-500 py-8">
                    <i class="pi pi-images text-4xl mb-2 opacity-30"></i>
                    <p class="text-sm">Your uploads will appear here</p>
                </div>

                <div v-else class="grid grid-cols-2 gap-3">
                    <div
                        v-for="product in uploadedImages"
                        :key="product.id"
                        draggable="true"
                        @dragstart="handleDragStart(product, $event)"
                        @click="addProductToCanvas(product)"
                        class="bg-white/5 border border-white/5 rounded-lg overflow-hidden cursor-grab active:cursor-grabbing hover:ring-2 hover:ring-violet-600 transition-all group relative"
                    >
                        <!-- Imagen del producto -->
                        <div class="aspect-square bg-black/40 overflow-hidden">
                            <img
                                :src="product.images[0].url"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                            >
                        </div>

                        <!-- Info del producto -->
                        <div class="p-2">
                            <p class="text-xs font-semibold truncate">{{ product.name }}</p>
                            <p class="text-[10px] text-slate-400 truncate">Uploaded</p>
                        </div>
                        
                        <!-- Actions overlay (delete) could go here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Ayuda de drag & drop -->
        <div class="p-3 border-t border-white/5 bg-black/20">
            <p class="text-[10px] text-slate-500 text-center">
                <i class="pi pi-info-circle mr-1"></i>
                Drag items to the canvas or click to add
            </p>
        </div>
    </div>
</template>

<style scoped>
.canvas-sidebar {
    width: 320px;
    min-width: 320px;
}
</style>
