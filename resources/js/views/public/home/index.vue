<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import SmartSearch from '@/components/SmartSearch.vue';
import LiveFeed from '@/components/LiveFeed.vue';
import { authStore } from '@/store/auth';

const useAuth = authStore();
const activeTab = ref('trending');
const outfits = ref([]);
const loading = ref(false);

onMounted(() => {
    loadOutfits();
});

watch(activeTab, () => {
    loadOutfits();
});

async function loadOutfits() {
    loading.value = true;
    outfits.value = [];
    try {
        const url = activeTab.value === 'following' 
            ? '/api/feed/following'
            : '/api/outfits';
            
        const response = await axios.get(url);
        // Manejar estructura de respuesta de Resource (obj.data)
        outfits.value = response.data.data;
    } catch (e) {
        console.error('Error loading outfits', e);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-stone-50 flex flex-col font-sans">
        <!-- Hero Section -->
        <div class="bg-slate-950 pt-32 pb-20 px-4 text-center relative overflow-hidden ring-1 ring-white/10 border-b border-white/5">
            <!-- Background Gradients -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-violet-600/20 blur-[120px] rounded-full pointer-events-none"></div>
            
            <div class="relative z-10 max-w-4xl mx-auto">
                <div class="flex justify-center mb-12 gap-8">
                     <!-- Floating Cards Animation (Mock) -->
                     <div class="hidden md:block w-48 h-64 bg-slate-900/80 backdrop-blur border border-slate-700 rounded-xl transform -rotate-6 shadow-2xl p-3">
                         <div class="bg-emerald-500/20 text-emerald-400 text-xs font-mono py-1 px-2 rounded w-fit mb-2">QC VERIFIED</div>
                         <div class="h-32 bg-slate-800 rounded-lg mb-3 flex items-center justify-center text-slate-600"><i class="pi pi-image text-2xl"></i></div>
                         <div class="h-2 w-20 bg-slate-800 rounded mb-2"></div>
                         <div class="h-2 w-12 bg-slate-800 rounded"></div>
                     </div>
                     <div class="hidden md:block w-48 h-64 bg-slate-900/80 backdrop-blur border border-slate-700 rounded-xl transform rotate-6 shadow-2xl p-3 translate-y-8">
                         <div class="bg-emerald-500/20 text-emerald-400 text-xs font-mono py-1 px-2 rounded w-fit mb-2">QC VERIFIED</div>
                         <div class="h-32 bg-slate-800 rounded-lg mb-3 flex items-center justify-center text-slate-600"><i class="pi pi-image text-2xl"></i></div>
                         <div class="h-2 w-20 bg-slate-800 rounded mb-2"></div>
                         <div class="h-2 w-12 bg-slate-800 rounded"></div>
                     </div>
                </div>

                <h1 class="text-5xl md:text-7xl font-display font-bold text-white mb-6 tracking-tight leading-tight">
                    The Ultimate Weidian <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-indigo-400">& Taobao QC Finder</span>
                </h1>
                <p class="text-lg md:text-xl text-slate-400 mb-12 max-w-2xl mx-auto font-light">
                    Search millions of real photos and build outfits. Stop buying blind.
                </p>
                
                <SmartSearch />
            </div>
        </div>

        <!-- Live Feed Ticker -->
        <LiveFeed />

        <!-- Social Feed Section -->
        <div class="max-w-7xl mx-auto px-4 py-16">
            <!-- Tabs -->
            <div class="flex items-center gap-8 mb-8 border-b border-slate-200 pb-4">
                <button 
                    @click="activeTab = 'trending'"
                    class="text-2xl font-bold transition-colors pb-2 relative"
                    :class="activeTab === 'trending' ? 'text-slate-900' : 'text-slate-400 hover:text-slate-600'"
                >
                    Trending
                    <span v-if="activeTab === 'trending'" class="absolute bottom-0 left-0 w-full h-1 bg-violet-600 rounded-t-full -mb-4"></span>
                </button>
                <button 
                    v-if="useAuth.authenticated"
                    @click="activeTab = 'following'"
                    class="text-2xl font-bold transition-colors pb-2 relative"
                    :class="activeTab === 'following' ? 'text-slate-900' : 'text-slate-400 hover:text-slate-600'"
                >
                    Following
                    <span v-if="activeTab === 'following'" class="absolute bottom-0 left-0 w-full h-1 bg-violet-600 rounded-t-full -mb-4"></span>
                </button>
            </div>

            <!-- Content -->
            <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                 <div v-for="i in 4" :key="i" class="aspect-[4/5] bg-slate-100 rounded-2xl animate-pulse"></div>
            </div>
            
            <div v-else-if="outfits.length > 0" class="grid grid-cols-2 lg:grid-cols-4 gap-6 animate-fade-in-up">
                <div 
                    v-for="outfit in outfits" 
                    :key="outfit.id"
                    class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-slate-100"
                >
                    <!-- Image -->
                    <div class="aspect-[4/5] bg-slate-100 relative overflow-hidden">
                        <img 
                            :src="outfit.thumbnail_url || (outfit.products && outfit.products[0]?.images[0]?.url) || '/images/placeholder-outfit.jpg'" 
                            referrerpolicy="no-referrer"
                            :alt="outfit.title"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                            loading="lazy"
                        >
                        <!-- Creator Badge -->
                        <div class="absolute bottom-3 left-3 flex items-center gap-2 bg-white/90 backdrop-blur px-2 py-1 rounded-full text-xs font-bold text-slate-800 shadow-sm">
                            <img :src="outfit.user?.avatar || '/images/default-avatar.png'" referrerpolicy="no-referrer" class="w-5 h-5 rounded-full object-cover">
                            {{ outfit.user?.name }}
                        </div>
                        
                        <!-- Overlay actions -->
                        <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                            <router-link :to="{name: 'OutfitDetail', params: {id: outfit.id}}" class="p-3 bg-white rounded-full text-zinc-900 hover:scale-110 transition-transform shadow-lg">
                                <i class="pi pi-eye"></i>
                            </router-link>
                        </div>
                    </div>

                    <!-- Simplified Footer -->
                    <div class="p-4">
                        <h3 class="font-bold text-slate-900 truncate">{{ outfit.title }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ outfit.items_count }} items</p>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-20 text-slate-400">
                <i class="pi pi-compass text-4xl mb-4 opacity-50"></i>
                <p>No se encontraron outfits recientes.</p>
            </div>
        </div>

        <!-- Brands Ticker -->
        <div class="py-12 border-t border-slate-200 bg-white">
            <div class="text-center mb-8">
                 <h3 class="text-sm font-bold text-slate-400 tracking-widest uppercase">Browse by Brand</h3>
            </div>
             <div class="flex justify-center gap-12 opacity-50 grayscale hover:grayscale-0 transition-all duration-500 flex-wrap px-4">
                 <!-- Just simple text placehhodlers resembling logos for speed -->
                 <span class="text-2xl font-black font-display italic">NIKE</span>
                 <span class="text-2xl font-black font-display">STUSSY</span>
                 <span class="text-2xl font-black font-display tracking-tighter">BALENCIAGA</span>
                 <span class="text-2xl font-black font-display">ARC'TERYX</span>
                 <span class="text-2xl font-black font-display font-serif">Carhartt</span>
             </div>
        </div>
    </div>
</template>

<style scoped>
.animate-marquee {
    animation: marquee 20s linear infinite;
}
@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
</style>
