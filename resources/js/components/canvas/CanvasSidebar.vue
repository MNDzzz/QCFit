<script setup>
import { ref, onMounted } from 'vue';
import { useCanvasStore } from '@/store/canvas';
import axios from 'axios';

const canvasStore = useCanvasStore();

// Tabs
const activeTab = ref('search');

// Búsqueda
const searchQuery = ref('');
const searchResults = ref([]);
const searchLoading = ref(false);

// Wardrobe (productos favoritos)
const wardrobeItems = ref([]);
const wardrobeLoading = ref(false);

// Estado de drag & drop
const draggedProduct = ref(null);

// Cargar productos del wardrobe al montar
onMounted(() => {
    loadWardrobe();
});

// Buscar productos
async function searchProducts() {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    searchLoading.value = true;
    
    try {
        const response = await axios.get('/api/search', {
            params: { 
                q: searchQuery.value,
                limit: 12 
            }
        });

        searchResults.value = response.data.data || response.data || [];
    } catch (error) {
        console.error('Error buscando productos:', error);
        searchResults.value = [];
    } finally {
        searchLoading.value = false;
    }
}

// Cargar productos del wardrobe (favoritos)
async function loadWardrobe() {
    wardrobeLoading.value = true;
    
    try {
        // TODO: Implementar endpoint de favoritos del usuario
        // Por ahora, cargar productos recientes
        const response = await axios.get('/api/products', {
            params: { limit: 12 }
        });

        wardrobeItems.value = response.data.data || response.data || [];
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
</script>

<template>
    <div class="canvas-sidebar h-full flex flex-col bg-slate-950 text-white">
        <!-- Tabs -->
        <div class="flex border-b border-slate-800">
            <button
                @click="activeTab = 'search'"
                :class="[
                    'flex-1 px-4 py-3 text-sm font-medium transition-colors',
                    activeTab === 'search' 
                        ? 'bg-slate-900 text-white border-b-2 border-violet-600' 
                        : 'text-slate-400 hover:text-white hover:bg-slate-900/50'
                ]"
            >
                <i class="pi pi-search mr-2"></i>
                Buscar
            </button>
            <button
                @click="activeTab = 'wardrobe'"
                :class="[
                    'flex-1 px-4 py-3 text-sm font-medium transition-colors',
                    activeTab === 'wardrobe' 
                        ? 'bg-slate-900 text-white border-b-2 border-violet-600' 
                        : 'text-slate-400 hover:text-white hover:bg-slate-900/50'
                ]"
            >
                <i class="pi pi-heart mr-2"></i>
                Armario
            </button>
        </div>

        <!-- Tab Content: Search -->
        <div v-show="activeTab === 'search'" class="flex-1 flex flex-col overflow-hidden">
            <!-- Buscador -->
            <div class="p-4 border-b border-slate-800">
                <div class="relative">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                    <input
                        v-model="searchQuery"
                        @keyup.enter="searchProducts"
                        type="text"
                        placeholder="Busca productos..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-900 border border-slate-700 rounded-lg text-sm text-white placeholder-slate-500 focus:outline-none focus:border-violet-600 focus:ring-1 focus:ring-violet-600"
                    >
                </div>
                <button
                    @click="searchProducts"
                    :disabled="searchLoading"
                    class="w-full mt-2 px-4 py-2 bg-violet-600 hover:bg-violet-500 disabled:bg-slate-700 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors"
                >
                    <i v-if="searchLoading" class="pi pi-spin pi-spinner mr-2"></i>
                    {{ searchLoading ? 'Buscando...' : 'Buscar' }}
                </button>
            </div>

            <!-- Resultados de búsqueda -->
            <div class="flex-1 overflow-y-auto p-4">
                <div v-if="searchResults.length === 0 && !searchLoading" class="text-center text-slate-500 py-8">
                    <i class="pi pi-inbox text-4xl mb-2 opacity-30"></i>
                    <p class="text-sm">Busca productos para añadir a tu outfit</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div
                        v-for="product in searchResults"
                        :key="product.id"
                        draggable="true"
                        @dragstart="handleDragStart(product, $event)"
                        @click="addProductToCanvas(product)"
                        class="bg-slate-900 rounded-lg overflow-hidden cursor-grab active:cursor-grabbing hover:ring-2 hover:ring-violet-600 transition-all group"
                    >
                        <!-- Imagen del producto -->
                        <div class="aspect-square bg-slate-800 overflow-hidden">
                            <img
                                v-if="product.images && product.images.length"
                                :src="product.images[0].url"
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
                            <p class="text-[10px] text-slate-400 truncate">{{ product.brand }}</p>
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
                    <p class="text-sm">Cargando armario...</p>
                </div>

                <div v-else-if="wardrobeItems.length === 0" class="text-center text-slate-500 py-8">
                    <i class="pi pi-heart text-4xl mb-2 opacity-30"></i>
                    <p class="text-sm">No tienes productos guardados</p>
                    <p class="text-xs mt-2">Guarda productos desde la búsqueda</p>
                </div>

                <div v-else class="grid grid-cols-2 gap-3">
                    <div
                        v-for="product in wardrobeItems"
                        :key="product.id"
                        draggable="true"
                        @dragstart="handleDragStart(product, $event)"
                        @click="addProductToCanvas(product)"
                        class="bg-slate-900 rounded-lg overflow-hidden cursor-grab active:cursor-grabbing hover:ring-2 hover:ring-violet-600 transition-all group"
                    >
                        <!-- Imagen del producto -->
                        <div class="aspect-square bg-slate-800 overflow-hidden">
                            <img
                                v-if="product.images && product.images.length"
                                :src="product.images[0].url"
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
                            <p class="text-[10px] text-slate-400 truncate">{{ product.brand }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ayuda de drag & drop -->
        <div class="p-3 border-t border-slate-800 bg-slate-900/50">
            <p class="text-[10px] text-slate-500 text-center">
                <i class="pi pi-info-circle mr-1"></i>
                Arrastra productos al canvas o haz click para añadir
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
