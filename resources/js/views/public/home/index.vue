<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import SmartSearch from '@/components/SmartSearch.vue';
import LiveFeed from '@/components/LiveFeed.vue';
import { authStore } from '@/store/auth';

const useAuth = authStore();
const activeTab = ref('trending');
const outfits = ref([]);
const products = ref([]);
const loading = ref(false);

onMounted(() => {
    loadOutfits();
    loadProducts();
});

async function loadProducts() {
    try {
        const response = await axios.get('/api/products');
        products.value = response.data.data || [];
    } catch (e) {
        console.error('Error loading products', e);
    }
}

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
        <div class="relative bg-[#0B0F19] pt-40 pb-36 px-4 text-center overflow-hidden border-b border-white/5">
            <!-- Background Gradients -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1400px] h-[700px] bg-violet-600/20 blur-[180px] rounded-full pointer-events-none mix-blend-screen opacity-60"></div>
            
            <div class="relative z-10 max-w-5xl mx-auto">
                <!-- Floating Cards Animation -->
                <div class="pointer-events-none select-none absolute inset-0 flex items-center justify-center z-0 opacity-100">
                     <!-- Left Card: Dunk -->
                     <div class="absolute left-[5%] lg:left-[12%] top-1/2 -translate-y-1/2 -translate-x-12 w-56 animate-float-slow transform -rotate-12">
                         <div class="bg-[#1E293B] rounded-xl overflow-hidden shadow-2xl border border-slate-700/50 relative group">
                             <!-- Badge -->
                             <div class="absolute top-3 left-3 bg-emerald-500 text-white text-[10px] px-2 py-0.5 rounded font-bold uppercase flex items-center gap-1 z-20 shadow-lg">
                                 <i class="pi pi-check-circle text-[10px]"></i> QC Verified
                             </div>
                             <!-- Image -->
                             <div class="p-6 bg-gradient-to-b from-slate-700/50 to-slate-800/50">
                                <img src="/images/hero/dunk.png" alt="Nike Dunk" class="w-full drop-shadow-xl transform group-hover:scale-110 transition-transform duration-500">
                             </div>
                             <!-- Footer -->
                             <div class="bg-[#0F172A] p-3 text-left">
                                 <div class="text-white text-xs font-bold truncate leading-tight">Nike Dunk Low 'Grey Fog'</div>
                                 <div class="flex items-center justify-between mt-2">
                                     <span class="text-white font-bold text-lg">¥ 180</span>
                                     <span class="bg-orange-500/20 text-orange-500 p-1 rounded text-xs"><i class="pi pi-shopping-bag"></i></span>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Right Card: Jordan -->
                     <div class="absolute right-[5%] lg:right-[12%] top-1/2 -translate-y-1/2 translate-x-12 w-56 animate-float-delayed transform rotate-12">
                         <div class="bg-[#1E293B] rounded-xl overflow-hidden shadow-2xl border border-slate-700/50 relative group">
                             <!-- Badge -->
                             <div class="absolute top-3 left-3 bg-emerald-500 text-white text-[10px] px-2 py-0.5 rounded font-bold uppercase flex items-center gap-1 z-20 shadow-lg">
                                 <i class="pi pi-check-circle text-[10px]"></i> QC Verified
                             </div>
                             <!-- Image -->
                             <div class="p-6 bg-gradient-to-b from-slate-700/50 to-slate-800/50">
                                <img src="/images/hero/jordan.png" alt="Jordan 4" class="w-full drop-shadow-xl transform group-hover:scale-110 transition-transform duration-500">
                             </div>
                             <!-- Footer -->
                             <div class="bg-[#0F172A] p-3 text-left">
                                 <div class="text-white text-xs font-bold truncate leading-tight">Cantos Exxxxs <br> Travis Scott J1...</div>
                                 <div class="flex items-center justify-between mt-2">
                                     <span class="text-white font-bold text-lg">¥ 180</span>
                                     <span class="bg-red-500/20 text-red-500 p-1 rounded text-xs"><i class="pi pi-shopping-cart"></i></span>
                                 </div>
                             </div>
                         </div>
                     </div>
                </div>

                <!-- Main Content -->
                <div class="relative z-10 pt-8">
                    <h1 class="text-6xl md:text-8xl font-display font-bold text-white mb-6 tracking-tight leading-none drop-shadow-2xl">
                        The Ultimate Weidian <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-200 to-slate-400">& Taobao QC Finder</span>
                    </h1>
                    <p class="text-lg text-slate-400 mb-10 max-w-xl mx-auto font-light leading-relaxed">
                        Search millions of real photos and build outfits.
                    </p>
                    
                    <div class="hover:scale-[1.01] transition-transform duration-300">
                        <SmartSearch />
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticker de Feed en Vivo -->
        <LiveFeed />

        <!-- Sección de Populares -->
        <div class="max-w-7xl mx-auto px-4 py-16">
            <h2 class="text-3xl font-display font-bold text-slate-900 text-center mb-8">POPULAR RIGHT NOW</h2>
            
            <!-- Category Pills -->
            <div class="flex justify-center gap-4 mb-12">
                <button class="px-6 py-2 rounded-full bg-slate-900 text-white font-medium text-sm shadow-lg hover:shadow-xl transition-all border border-slate-700">All</button>
                <button class="px-6 py-2 rounded-full bg-white text-slate-600 font-medium text-sm shadow-sm border border-slate-200 hover:border-slate-300 hover:text-slate-900 transition-all flex items-center gap-2"><i class="pi pi-pause rotate-90 text-xs"></i> Shoes</button>
                <button class="px-6 py-2 rounded-full bg-white text-slate-600 font-medium text-sm shadow-sm border border-slate-200 hover:border-slate-300 hover:text-slate-900 transition-all flex items-center gap-2"><i class="pi pi-user text-xs"></i> Tops</button>
                <button class="px-6 py-2 rounded-full bg-white text-slate-600 font-medium text-sm shadow-sm border border-slate-200 hover:border-slate-300 hover:text-slate-900 transition-all flex items-center gap-2"><i class="pi pi-align-justify text-xs"></i> Bottoms</button>
                <button class="px-6 py-2 rounded-full bg-white text-slate-600 font-medium text-sm shadow-sm border border-slate-200 hover:border-slate-300 hover:text-slate-900 transition-all flex items-center gap-2"><i class="pi pi-star text-xs"></i> Accessories</button>
            </div>

            <!-- Products Grid (Reusing Logic but fetching Products) -->
            <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                 <div v-for="i in 4" :key="i" class="aspect-[4/5] bg-slate-100 rounded-2xl animate-pulse"></div>
            </div>
            
            <div v-else-if="products.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Special "Studio" Card (Mocked as first item or inserted) -->
                <div class="bg-violet-50 rounded-2xl p-4 border border-violet-100 flex flex-col items-center justify-center text-center group cursor-pointer hover:shadow-xl hover:ring-2 hover:ring-violet-500 transition-all relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-500/10 to-transparent"></div>
                    <img src="/images/hero/dunk.png" class="w-24 mb-4 drop-shadow-lg group-hover:scale-110 transition-transform">
                    <button class="bg-violet-600 text-white px-4 py-2 rounded-full text-xs font-bold flex items-center gap-2 relative z-10">
                        <i class="pi pi-plus"></i> Studio
                    </button>
                    <p class="text-xs text-violet-600 font-bold mt-3 relative z-10">Create Outfit</p>
                </div>

                <div 
                    v-for="product in products.slice(0, 7)" 
                    :key="product.id"
                    class="group bg-white rounded-2xl p-3 shadow-sm hover:shadow-2xl hover:-translate-y-1 transition-all border border-slate-100 relative"
                >
                    <!-- Image -->
                    <div class="aspect-square bg-slate-50 rounded-xl relative overflow-hidden mb-3">
                        <img 
                            :src="product.images[0]?.url || '/images/placeholder.jpg'" 
                            referrerpolicy="no-referrer"
                            :alt="product.name"
                            class="w-full h-full object-contain mix-blend-multiply p-4 transition-transform duration-500 group-hover:scale-105"
                            loading="lazy"
                        >
                    </div>

                    <!-- Info -->
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm truncate">{{ product.name }}</h3>
                        <p class="text-xs text-slate-500 mb-2 truncate">Nike Dunk Low 'Grey Fog'</p>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-900 font-extrabold text-lg">¥ {{ product.price || 180 }}</span>
                            <img src="https://img.alicdn.com/tps/i4/TB1j5K.LXXXXXb.XVXXxGsw1VXX-112-46.png" class="h-4 opacity-50 grayscale group-hover:grayscale-0 transition-all">
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-20 text-slate-400">
                 <p>No products found.</p>
            </div>
        </div>

        <!-- Brands Grid -->
        <div class="max-w-7xl mx-auto px-4 py-12 mb-12">
            <h3 class="text-center text-sm font-bold text-slate-400 tracking-widest uppercase mb-8">BROWSE BY BRAND</h3>
             <div class="flex justify-center flex-wrap gap-4">
                 <div v-for="brand in ['NIKE', 'STUSSY', 'BALENCIAGA', 'ARC\'TERYX', 'CARHARTT', 'YEEZY']" :key="brand" class="w-32 h-20 bg-white rounded-xl border border-slate-200 flex items-center justify-center hover:border-slate-900 hover:shadow-lg transition-all cursor-pointer group">
                     <span class="font-black font-display text-slate-800 text-xl group-hover:scale-110 transition-transform">{{ brand }}</span>
                 </div>
             </div>
        </div>
        
        <!-- Trending Outfits (Moved down) -->
        <div class="bg-slate-50 py-16 border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4">
                <h3 class="text-center text-sm font-bold text-slate-400 tracking-widest uppercase mb-8">TRENDING OUTFITS</h3>
                <!-- Simple 3-column grid for outfits -->
                 <div v-if="outfits.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="outfit in outfits.slice(0, 3)" :key="outfit.id" class="rounded-3xl overflow-hidden relative group aspect-square">
                        <img :src="outfit.thumbnail_url" referrerpolicy="no-referrer" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="bg-violet-600 text-white px-6 py-3 rounded-lg font-bold shadow-lg hover:scale-105 transition-transform flex items-center gap-2">
                                <i class="pi pi-bolt"></i> REMIX THIS FIT
                            </button>
                        </div>
                    </div>
                 </div>
                 
                 <div v-else class="text-center py-20 text-slate-400">
                    <i class="pi pi-compass text-4xl mb-4 opacity-50"></i>
                    <p>No trending outfits found.</p>
                </div>
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
