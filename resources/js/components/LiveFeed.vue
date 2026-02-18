<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const images = ref([]);
const loading = ref(true);
const router = useRouter();

const fetchFeed = async () => {
    try {
        const response = await axios.get('/api/feed/live');
        images.value = response.data.data;
        loading.value = false;
    } catch (error) {
        console.error('Error fetching live feed:', error);
        loading.value = false;
    }
};

const goToProduct = (productId) => {
    router.push({ name: 'public.product.show', params: { id: productId } });
};

onMounted(() => {
    fetchFeed();
});
</script>

<template>
    <div class="w-full bg-slate-900 border-y border-slate-800 py-6 overflow-hidden">
        <div class="container mx-auto px-4 mb-4">
            <h3 class="text-white font-display text-lg flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                Live QC Feed
                <span class="text-slate-500 text-sm ml-2 font-sans font-normal">Real photos from massive database</span>
            </h3>
        </div>
        
        <div v-if="loading" class="flex gap-4 overflow-x-hidden px-4">
            <div v-for="i in 5" :key="i" class="min-w-[200px] h-[200px] bg-slate-800 animate-pulse rounded-lg"></div>
        </div>

        <div v-else class="relative w-full">
            <!-- Gradient Masks for smooth fade -->
            <div class="absolute inset-y-0 left-0 w-20 bg-gradient-to-r from-slate-950 to-transparent z-10 pointer-events-none"></div>
            <div class="absolute inset-y-0 right-0 w-20 bg-gradient-to-l from-slate-950 to-transparent z-10 pointer-events-none"></div>

            <div class="flex gap-4 overflow-x-auto pb-4 px-4 snap-x hide-scrollbar">
                <div 
                    v-for="img in images" 
                    :key="img.id"
                    @click="goToProduct(img.product_id)"
                    class="relative group cursor-pointer min-w-[200px] h-[200px] rounded-lg overflow-hidden border border-slate-700 bg-slate-800 transition-transform hover:scale-105 hover:z-10 hover:border-violet-500 snap-start"
                >
                    <img 
                        :src="img.url" 
                        referrerpolicy="no-referrer"
                        loading="lazy" 
                        class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity"
                    >
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent p-3 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                        <p class="text-white text-xs font-bold truncate">{{ img.title || 'Product' }}</p>
                        <p class="text-slate-400 text-[10px]">{{ img.date_human || 'Just now' }}</p>
                    </div>
                    <div class="absolute top-2 right-2 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded backdrop-blur-sm border border-white/10">
                        QC
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
