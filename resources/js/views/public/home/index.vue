<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Hero Section -->
        <div class="bg-indigo-700 text-white py-20 px-4 text-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            <h1 class="text-5xl font-extrabold mb-6 relative z-10 tracking-tight">
                FindQC <span class="text-indigo-300 font-light">+ ShopLook</span>
            </h1>
            <p class="text-xl mb-10 max-w-2xl mx-auto text-indigo-100 relative z-10">
                Busca productos con fotos reales (QC) de Taobao/Weidian y crea tus outfits en nuestro lienzo interactivo.
            </p>
            
            <!-- Smart Search Component -->
            <div class="relative z-10 mb-12">
                <SmartSearch />
            </div>
        </div>

        <!-- Live QC Feed Section -->
        <div class="container mx-auto px-4 py-12 flex-1">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 flex items-center">
                <span class="mr-3">🟢</span> Live QC Feed
                <span class="ml-4 text-sm font-normal text-gray-500 bg-gray-200 px-3 py-1 rounded-full animate-pulse">En tiempo real</span>
            </h2>

            <div v-if="loadingFeed" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <Skeleton v-for="i in 4" :key="i" height="300px" class="rounded-xl" />
            </div>
            
            <div v-else-if="feedImages.length > 0" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                <div 
                    v-for="img in feedImages" 
                    :key="img.id" 
                    class="group relative bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all hover:-translate-y-1 cursor-pointer border border-gray-100"
                    @click="goToProduct(img.product_id)"
                >
                    <div class="aspect-[3/4] bg-gray-100 overflow-hidden">
                        <img 
                            :src="img.url" 
                            :alt="img.product?.name || 'QC Photo'" 
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            loading="lazy"
                        >
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-4 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                        <p class="font-bold truncate text-sm">{{ img.product?.name }}</p>
                        <p class="text-xs opacity-80">{{ formatDate(img.created_at) }}</p>
                    </div>
                    <div class="absolute top-2 right-2 bg-green-500 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm">
                        QC
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-20 text-gray-500 bg-white rounded-xl shadow-inner">
                <p>No hay imágenes recientes en el feed.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import SmartSearch from '@/components/SmartSearch.vue';
import axios from 'axios';

const feedImages = ref([]);
const loadingFeed = ref(true);
const router = useRouter();

const fetchLiveFeed = async () => {
    loadingFeed.value = true;
    try {
        const response = await axios.get('/api/products/live-feed?limit=10');
        feedImages.value = response.data;
    } catch (error) {
        console.error('Error fetching live feed:', error);
    } finally {
        loadingFeed.value = false;
    }
};

const goToProduct = (id) => {
    if (id) {
        // En el futuro redirigirá a /product/:id
        console.log('Navegando a producto:', id);
        // router.push({ name: 'ProductDetail', params: { id } });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-ES', { 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

onMounted(() => {
    fetchLiveFeed();
});
</script>
