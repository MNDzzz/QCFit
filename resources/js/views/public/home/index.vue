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
        <!-- Hero Section -->
        <div class="relative bg-slate-950 pt-40 pb-32 px-4 text-center overflow-hidden border-b border-white/5">
            <!-- Background Gradients -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1200px] h-[600px] bg-violet-600/20 blur-[150px] rounded-full pointer-events-none mix-blend-screen"></div>
            <div class="absolute bottom-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>
            
            <div class="relative z-10 max-w-5xl mx-auto">
                <!-- Floating Cards Animation -->
                <div class="pointer-events-none select-none absolute inset-0 flex items-center justify-center z-0 opacity-50 md:opacity-100">
                     <!-- Left Card: Jordan -->
                     <div class="absolute left-[10%] top-1/2 -translate-y-1/2 -translate-x-12 w-64 md:w-80 animate-float-slow">
                         <div class="relative transform -rotate-12 hover:rotate-0 transition-transform duration-700">
                             <div class="absolute inset-0 bg-violet-500/20 blur-xl rounded-full"></div>
                             <img src="/images/hero/jordan.png" alt="Jordan 4" class="relative z-10 drop-shadow-2xl rounded-2xl w-full">
                             <!-- Floating UI Badge -->
                             <div class="absolute -top-4 -right-4 bg-slate-900/90 backdrop-blur border border-slate-700 rounded-lg px-3 py-2 shadow-xl animate-bounce-slow">
                                 <div class="text-[10px] text-slate-400 font-mono">QC VERIFIED</div>
                                 <div class="text-xs font-bold text-white">AJ4 Black Cat</div>
                             </div>
                         </div>
                     </div>

                     <!-- Right Card: Dunk -->
                     <div class="absolute right-[10%] top-1/2 -translate-y-1/2 translate-x-12 w-64 md:w-80 animate-float-delayed">
                         <div class="relative transform rotate-12 hover:rotate-0 transition-transform duration-700">
                             <div class="absolute inset-0 bg-blue-500/20 blur-xl rounded-full"></div>
                             <img src="/images/hero/dunk.png" alt="Nike Dunk" class="relative z-10 drop-shadow-2xl rounded-2xl w-full">
                             <!-- Floating UI Badge -->
                             <div class="absolute -bottom-4 -left-4 bg-slate-900/90 backdrop-blur border border-slate-700 rounded-lg px-3 py-2 shadow-xl animate-bounce-slow" style="animation-delay: 1s;">
                                 <div class="text-[10px] text-slate-400 font-mono">QC VERIFIED</div>
                                 <div class="text-xs font-bold text-white">Dunk Low Kasina</div>
                             </div>
                         </div>
                     </div>
                </div>

                <!-- Main Content -->
                <div class="relative z-10 pt-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900/50 border border-slate-800 backdrop-blur mb-8 animate-fade-in">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-violet-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-violet-500"></span>
                        </span>
                        <span class="text-xs font-medium text-slate-300">v2.0 Now Live</span>
                    </div>

                    <h1 class="text-5xl md:text-8xl font-display font-bold text-white mb-8 tracking-tight leading-none drop-shadow-lg">
                        Find QC Photos <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 via-fuchsia-400 to-indigo-400">Before You Buy</span>
                    </h1>
                    <p class="text-lg md:text-xl text-slate-400 mb-12 max-w-2xl mx-auto font-light leading-relaxed">
                        The ultimate tool for Weidian, Taobao & 1688 reps. <br class="hidden md:block"/>
                        Search millions of real warehouse photos instantly.
                    </p>
                    
                    <div class="max-w-2xl mx-auto transform transition-all hover:scale-[1.02] duration-300">
                        <SmartSearch />
                    </div>

                    <div class="mt-12 flex items-center justify-center gap-8 text-slate-500 text-sm font-medium">
                        <span class="flex items-center gap-2"><i class="pi pi-check-circle text-emerald-500"></i> No Sign Up Required</span>
                        <span class="flex items-center gap-2"><i class="pi pi-bolt text-amber-500"></i> Instant Search</span>
                        <span class="flex items-center gap-2"><i class="pi pi-mobile text-blue-500"></i> Mobile Ready</span>
                    </div>
                </div>
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

.animate-float-slow {
    animation: float 6s ease-in-out infinite;
}
.animate-float-delayed {
    animation: float 7s ease-in-out infinite 1s;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.animate-bounce-slow {
    animation: bounce 3s infinite;
}
</style>
