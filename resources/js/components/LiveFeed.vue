<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const images = ref([]);
const loading = ref(true);
const scrollContainer = ref(null);
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

const scroll = (direction) => {
    if (!scrollContainer.value) return;
    const scrollAmount = 300;
    scrollContainer.value.scrollBy({
        left: direction === 'left' ? -scrollAmount : scrollAmount,
        behavior: 'smooth'
    });
};

// Optional: Auto-scroll
let autoScrollInterval = null;
const startAutoScroll = () => {
    autoScrollInterval = setInterval(() => {
        if (!scrollContainer.value) return;
        const container = scrollContainer.value;
        if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 10) {
            container.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            container.scrollBy({ left: 2, behavior: 'auto' });
        }
    }, 50);
};

const stopAutoScroll = () => {
    if (autoScrollInterval) clearInterval(autoScrollInterval);
};

onMounted(() => {
    fetchFeed();
});

onUnmounted(() => {
    stopAutoScroll();
});
</script>

<template>
    <div class="w-full bg-[#0B0F19] border-y border-white/5 py-12 relative overflow-hidden group/feed">
        <!-- Background Decoration -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-violet-600/10 blur-[120px] rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-6 mb-8 flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <h3 class="text-white font-display text-2xl font-bold flex items-center gap-3">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    Live QC Feed
                </h3>
                <p class="text-slate-500 text-sm font-sans">Real-time inspections from our global community</p>
            </div>

            <!-- Navigation Controls -->
            <div class="flex gap-2">
                <button 
                    @click="scroll('left')"
                    class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-white hover:bg-white/5 transition-all active:scale-95"
                >
                    <i class="pi pi-chevron-left text-xs"></i>
                </button>
                <button 
                    @click="scroll('right')"
                    class="w-10 h-10 rounded-full border border-white/10 flex items-center justify-center text-white hover:bg-white/5 transition-all active:scale-95"
                >
                    <i class="pi pi-chevron-right text-xs"></i>
                </button>
            </div>
        </div>
        
        <div v-if="loading" class="flex gap-6 overflow-x-hidden px-6">
            <div v-for="i in 6" :key="i" class="min-w-[280px] aspect-[4/5] bg-slate-900/50 rounded-2xl animate-pulse border border-white/5"></div>
        </div>

        <div v-else class="relative">
            <!-- Fade Masks -->
            <div class="absolute inset-y-0 left-0 w-32 bg-gradient-to-r from-[#0B0F19] to-transparent z-10 pointer-events-none"></div>
            <div class="absolute inset-y-0 right-0 w-32 bg-gradient-to-l from-[#0B0F19] to-transparent z-10 pointer-events-none"></div>

            <div 
                ref="scrollContainer"
                class="flex gap-6 overflow-x-auto pb-12 pt-4 px-32 snap-x snap-mandatory hide-scrollbar group"
            >
                <div 
                    v-for="img in images" 
                    :key="img.id"
                    @click="goToProduct(img.product_id)"
                    class="relative flex-none w-[280px] aspect-[4/5] rounded-2xl overflow-hidden border border-white/10 bg-slate-900 shadow-2xl transition-all duration-500 hover:scale-105 hover:border-violet-500/50 hover:shadow-violet-500/10 snap-start cursor-pointer group/card"
                >
                    <img 
                        :src="img.url" 
                        referrerpolicy="no-referrer"
                        loading="lazy" 
                        class="w-full h-full object-cover transition-all duration-700 group-hover/card:scale-110"
                    >
                    
                    <!-- Overlay Glassmorphism -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent opacity-60 group-hover/card:opacity-100 transition-opacity"></div>
                    
                    <!-- Badges -->
                    <div class="absolute top-4 left-4 flex gap-2">
                        <span class="px-2 py-1 bg-emerald-500 text-white text-[10px] font-black uppercase rounded shadow-lg backdrop-blur-md">
                            QC Verified
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="absolute bottom-0 left-0 right-0 p-5 transform translate-y-2 group-hover/card:translate-y-0 transition-transform">
                        <p class="text-white text-sm font-bold truncate mb-1">{{ img.title || 'Product' }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 text-[10px] flex items-center gap-1">
                                <i class="pi pi-clock text-[8px]"></i>
                                {{ img.date_human || 'Just now' }}
                            </span>
                            <span class="text-violet-400 text-[10px] font-bold uppercase tracking-wider">
                                View Product <i class="pi pi-arrow-right text-[8px] ml-1"></i>
                            </span>
                        </div>
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

