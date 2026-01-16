<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { usePreferenceStore } from '@/store/preference';

const route = useRoute();
const preferenceStore = usePreferenceStore();

const product = ref(null);
const loading = ref(true);
const activeImage = ref('');
const selectedTab = ref('qc'); // 'qc' or 'original'

onMounted(async () => {
    fetchProduct();
});

async function fetchProduct() {
    loading.value = true;
    try {
        const id = route.params.id;
        const res = await axios.get(`/api/products/${id}`);
        product.value = res.data;
        
        // Set initial image
        if (product.value.images && product.value.images.length > 0) {
            // Try to find a QC image first
            const qcImg = product.value.images.find(img => img.type === 'qc');
            activeImage.value = qcImg ? qcImg.url : product.value.images[0].url;
        }
    } catch (e) {
        console.error("Error loading product", e);
    } finally {
        loading.value = false;
    }
}

const filteredImages = computed(() => {
    if (!product.value || !product.value.images) return [];
    return product.value.images.filter(img => img.type === selectedTab.value);
});

const handleW2C = () => {
    if (!product.value || !product.value.original_link) return;
    
    // Hijack Logic
    const affiliateLink = preferenceStore.getAffiliateLink(product.value.original_link);
    console.log("Redirecting to Affiliate Link:", affiliateLink);
    
    window.open(affiliateLink, '_blank');
};

const agents = [
    { name: 'CNFans', value: 'cnfans' },
    { name: 'Mulebuy', value: 'mulebuy' },
    { name: 'Hoobuy', value: 'hoobuy' },
];

</script>

<template>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div v-if="loading" class="max-w-7xl mx-auto flex justify-center py-20">
            <i class="pi pi-spin pi-spinner text-4xl text-indigo-600"></i>
        </div>

        <div v-else-if="product" class="max-w-7xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">
            <!-- Left: Gallery -->
            <div class="md:w-1/2 p-8 bg-gray-50">
                <div class="aspect-square rounded-xl overflow-hidden shadow-lg border-2 border-white mb-6 bg-white relative group">
                    <img 
                        :src="activeImage" 
                        class="w-full h-full object-contain object-center transition-transform duration-500 group-hover:scale-110"
                    >
                    <div class="absolute top-4 left-4 bg-black/70 text-white px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                        {{ selectedTab === 'qc' ? 'QC Real' : 'Marketing' }}
                    </div>
                </div>

                <!-- Tabs & Thumbnails -->
                <div class="flex gap-4 mb-4 border-b border-gray-200">
                    <button 
                        @click="selectedTab = 'qc'"
                        class="pb-2 px-4 font-bold transition-colors border-b-2"
                        :class="selectedTab === 'qc' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400 hover:text-gray-600'"
                    >
                        QC Photos
                    </button>
                    <button 
                        @click="selectedTab = 'original'"
                        class="pb-2 px-4 font-bold transition-colors border-b-2"
                        :class="selectedTab === 'original' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-400 hover:text-gray-600'"
                    >
                        Official Photos
                    </button>
                </div>

                <div class="grid grid-cols-5 gap-2">
                    <div v-if="filteredImages.length === 0" class="col-span-5 text-gray-400 text-sm py-4 italic">
                        No has images for this category.
                    </div>
                    <div 
                        v-for="img in filteredImages"
                        :key="img.id"
                        class="aspect-square rounded-lg overflow-hidden cursor-pointer border-2 transition-all hover:opacity-100"
                        :class="activeImage === img.url ? 'border-indigo-600 ring-2 ring-indigo-200' : 'border-transparent opacity-70'"
                        @click="activeImage = img.url"
                    >
                        <img :src="img.url" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>

            <!-- Right: Info -->
            <div class="md:w-1/2 p-8 flex flex-col">
                <div class="mb-auto">
                    <div class="flex justify-between items-start mb-4">
                        <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                            {{ product.marketplace }}
                        </span>
                        <button class="text-gray-400 hover:text-red-500 transition-colors">
                            <i class="pi pi-heart text-2xl"></i>
                        </button>
                    </div>
                    
                    <h1 class="text-4xl font-extrabold text-gray-900 mb-2 leading-tight">
                        {{ product.name }}
                    </h1>
                    <p class="text-xl text-gray-500 font-medium mb-8">
                        {{ product.brand || 'Unbranded' }}
                    </p>

                    <!-- Agent Selector -->
                    <div class="bg-blue-50 rounded-xl p-6 mb-8 border border-blue-100">
                        <h3 class="text-sm font-bold text-blue-800 uppercase mb-3 flex items-center gap-2">
                            <i class="pi pi-shopping-bag"></i> Configuración de Compra
                        </h3>
                        <div class="flex gap-4 mb-4">
                            <div 
                                v-for="agent in agents" 
                                :key="agent.value"
                                @click="preferenceStore.setAgent(agent.value)"
                                class="flex-1 cursor-pointer rounded-lg border-2 p-3 text-center transition-all bg-white"
                                :class="preferenceStore.preferredAgent === agent.value 
                                    ? 'border-blue-500 text-blue-600 shadow-md ring-2 ring-blue-200' 
                                    : 'border-transparent text-gray-400 hover:border-blue-200'"
                            >
                                <span class="font-bold">{{ agent.name }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-blue-600/80">
                            * Enlaces optimizados para tu agente favorito.
                        </p>
                    </div>
                </div>

                <!-- Call To Action -->
                <div class="mt-8 space-y-4">
                    <button 
                        @click="handleW2C"
                        class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-black text-xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3"
                    >
                        <span>W2C - COMPRAR AHORA</span>
                        <i class="pi pi-external-link"></i>
                    </button>
                    
                    <button 
                        class="w-full py-3 bg-white border-2 border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-50 hover:border-gray-300 transition-all flex items-center justify-center gap-2"
                        @click="$router.push({name: 'app.canvas'})"
                    >
                        <i class="pi pi-palette"></i>
                        <span>Usar en Outfit</span>
                    </button>
                </div>
            </div>
        </div>
        
        <div v-else class="text-center py-20">
            <h2 class="text-2xl font-bold text-gray-400">Producto no encontrado</h2>
            <Button label="Volver al inicio" text class="mt-4" @click="$router.push('/')" />
        </div>
    </div>
</template>
