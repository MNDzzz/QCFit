<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import SmartSearch from '@/components/SmartSearch.vue';
import LiveFeed from '@/components/LiveFeed.vue';
import ProductCard from '@/components/ui/ProductCard.vue';
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
        <div class="relative bg-[#0B0F19] pt-48 pb-40 px-6 text-center overflow-hidden border-b border-white/5">
            <!-- Background Gradients & Effects -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1600px] h-[800px] bg-violet-600/10 blur-[160px] rounded-full pointer-events-none mix-blend-screen opacity-50"></div>
            <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-blue-600/10 blur-[140px] rounded-full -translate-y-1/2 translate-x-1/2 pointer-events-none mix-blend-screen opacity-30"></div>
            
            <!-- Grid Pattern Overlay -->
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" 
                 style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 40px 40px;"></div>

            <div class="relative z-10 max-w-7xl mx-auto">
                <!-- Floating Elements (Visual Decoration) -->
                <div class="hidden lg:block pointer-events-none select-none absolute inset-0 z-0">
                     <!-- Left Floating Card -->
                     <div class="absolute left-[2%] xl:left-[8%] top-20 animate-float-slow transform -rotate-6">
                         <div class="bg-slate-900/40 backdrop-blur-xl rounded-2xl p-2 border border-white/10 shadow-2xl">
                             <div class="w-48 aspect-square bg-slate-800/50 rounded-xl overflow-hidden mb-3 relative group">
                                 <img src="/images/hero/dunk.png" alt="Nike Dunk" class="w-full h-full object-contain p-4 drop-shadow-2xl group-hover:scale-110 transition-transform duration-700">
                                 <div class="absolute top-2 right-2 px-1.5 py-0.5 bg-emerald-500 text-[8px] font-black text-white rounded">QC</div>
                             </div>
                             <div class="px-2 pb-2 text-left">
                                 <div class="h-2 w-24 bg-white/10 rounded mb-2"></div>
                                 <div class="flex justify-between items-center">
                                     <div class="h-3 w-12 bg-white/20 rounded"></div>
                                     <div class="h-4 w-4 bg-violet-500/20 rounded-full"></div>
                                 </div>
                             </div>
                         </div>
                     </div>

                     <!-- Right Floating Card -->
                     <div class="absolute right-[2%] xl:right-[8%] bottom-0 animate-float-delayed transform rotate-6">
                         <div class="bg-slate-900/40 backdrop-blur-xl rounded-2xl p-2 border border-white/10 shadow-2xl">
                             <div class="w-48 aspect-square bg-slate-800/50 rounded-xl overflow-hidden mb-3 relative group">
                                 <img src="/images/hero/jordan.png" alt="Jordan 4" class="w-full h-full object-contain p-4 drop-shadow-2xl group-hover:scale-110 transition-transform duration-700">
                                 <div class="absolute top-2 left-2 px-1.5 py-0.5 bg-emerald-500 text-[8px] font-black text-white rounded">QC</div>
                             </div>
                             <div class="px-2 pb-2 text-left">
                                 <div class="h-2 w-24 bg-white/10 rounded mb-2"></div>
                                 <div class="flex justify-between items-center">
                                     <div class="h-3 w-12 bg-white/20 rounded"></div>
                                     <div class="h-4 w-4 bg-orange-500/20 rounded-full"></div>
                                 </div>
                             </div>
                         </div>
                     </div>
                </div>

                <!-- Main Content -->
                <div class="relative z-10">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-violet-500/10 border border-violet-500/20 text-violet-400 text-xs font-bold mb-8 animate-fade-in sm:text-sm">
                        <span class="flex h-2 w-2 rounded-full bg-violet-500 animate-pulse"></span>
                        Trusted by 50,000+ QC inspectors
                    </div>

                    <h1 class="text-6xl md:text-8xl lg:text-9xl font-display font-black text-white mb-8 tracking-tighter leading-[0.9] drop-shadow-2xl">
                        Design Your <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-200 to-slate-500">Perfect Outfit</span>
                    </h1>
                    
                    <p class="text-lg md:text-xl text-slate-400 mb-12 max-w-2xl mx-auto font-medium leading-relaxed">
                        Search real QC photos from Weidian & Taobao. <br class="hidden sm:block"/>
                        Build outfits with our interactive studio.
                    </p>
                    
                    <div class="max-w-3xl mx-auto relative group">
                        <!-- Extra Search Decoration -->
                        <div class="absolute -top-12 -left-12 w-24 h-24 bg-blue-500/10 blur-2xl rounded-full"></div>
                        <div class="absolute -bottom-12 -right-12 w-24 h-24 bg-violet-500/10 blur-2xl rounded-full"></div>
                        
                        <div class="relative hover:scale-[1.02] transition-all duration-500 ease-out">
                            <SmartSearch />
                        </div>
                        
                        <!-- Search Hints -->
                        <div class="mt-6 flex flex-wrap justify-center gap-4 text-[10px] sm:text-xs text-slate-500 font-bold uppercase tracking-widest">
                            <span>Popular:</span>
                            <router-link :to="{ name: 'public.search', query: { q: 'Travis Scott' } }" class="text-slate-400 hover:text-white transition-colors">Travis Scott</router-link>
                            <router-link :to="{ name: 'public.search', query: { q: 'Nike SB' } }" class="text-slate-400 hover:text-white transition-colors">Nike SB</router-link>
                            <router-link :to="{ name: 'public.search', query: { brand: 'Essentials' } }" class="text-slate-400 hover:text-white transition-colors">Essentials</router-link>
                            <router-link :to="{ name: 'public.search', query: { brand: 'Arc\'teryx' } }" class="text-slate-400 hover:text-white transition-colors">Arc'teryx</router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticker de Feed en Vivo -->
        <LiveFeed />

        <!-- Sección de Populares -->
        <div class="max-w-7xl mx-auto px-6 py-24">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                <div class="flex flex-col gap-2">
                    <h2 class="text-4xl md:text-5xl font-display font-black text-slate-900 dark:text-white tracking-tight">
                        Trending Items
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400">Most inspected products this week</p>
                </div>
                
                <!-- Category Pills & More Button -->
                <div class="flex flex-col md:items-end gap-4">
                    <router-link :to="{ name: 'public.search', query: { type: 'products' } }" class="text-xs font-bold text-violet-600 hover:text-violet-700 hover:underline flex items-center gap-1 w-fit">
                        MORE ITEMS <i class="pi pi-arrow-right text-[10px]"></i>
                    </router-link>
                    <div class="flex flex-wrap gap-2">
                        <button @click="$router.push({ name: 'public.search' })" class="px-6 py-2.5 rounded-full bg-slate-950 dark:bg-white dark:text-slate-950 text-white font-bold text-xs shadow-xl transition-all">All</button>
                        <button @click="$router.push({ name: 'public.search', query: { category: 'Shoes' } })" class="px-6 py-2.5 rounded-full bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 font-bold text-xs border border-slate-200 dark:border-white/10 hover:border-slate-900 transition-all">Shoes</button>
                        <button @click="$router.push({ name: 'public.search', query: { category: 'Tops' } })" class="px-6 py-2.5 rounded-full bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 font-bold text-xs border border-slate-200 dark:border-white/10 hover:border-slate-900 transition-all">Tops</button>
                        <button @click="$router.push({ name: 'public.search', query: { category: 'Bottoms' } })" class="px-6 py-2.5 rounded-full bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 font-bold text-xs border border-slate-200 dark:border-white/10 hover:border-slate-900 transition-all">Bottoms</button>
                    </div>
                </div>
            </div>


            <!-- Products Grid (Reusing Logic but fetching Products) -->
            <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                 <div v-for="i in 4" :key="i" class="aspect-[4/5] bg-slate-100 rounded-2xl animate-pulse"></div>
            </div>
            
            <div v-else-if="products.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <!-- Special "Studio" Card -->
                <div 
                    @click="$router.push({ name: 'public.studio' })"
                    class="bg-violet-50 rounded-2xl p-4 border border-violet-100 flex flex-col items-center justify-center text-center group cursor-pointer hover:shadow-xl hover:ring-2 hover:ring-violet-500 transition-all relative overflow-hidden"
                >
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-500/10 to-transparent"></div>
                    <img src="/images/hero/dunk.png" class="w-24 mb-4 drop-shadow-lg group-hover:scale-110 transition-transform">
                    <button class="bg-violet-600 text-white px-4 py-2 rounded-full text-xs font-bold flex items-center gap-2 relative z-10">
                        <i class="pi pi-plus"></i> Studio
                    </button>
                    <p class="text-xs text-violet-600 font-bold mt-3 relative z-10">Crear Outfit</p>
                </div>

                <ProductCard 
                    v-for="product in products.slice(0, 7)" 
                    :key="product.id"
                    :product="product"
                />
            </div>
            
            <div v-else class="text-center py-20 text-slate-400">
                 <p>No products found.</p>
            </div>
        </div>

        <!-- Brands Grid -->
        <div class="max-w-7xl mx-auto px-4 py-12 mb-12">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-sm font-bold text-slate-400 tracking-widest uppercase">BROWSE BY BRAND</h3>
                <router-link :to="{ name: 'public.brands' }" class="text-xs font-bold text-violet-600 hover:text-violet-700 hover:underline flex items-center gap-1">
                    MORE BRANDS <i class="pi pi-arrow-right text-[10px]"></i>
                </router-link>
            </div>
             <div class="flex justify-center flex-wrap gap-4">
                 <div 
                    v-for="brand in ['NIKE', 'STUSSY', 'BALENCIAGA', 'ARC\'TERYX', 'CARHARTT', 'YEEZY']" 
                    :key="brand" 
                    class="w-32 h-20 bg-white rounded-xl border border-slate-200 flex items-center justify-center hover:border-slate-900 hover:shadow-lg transition-all cursor-pointer group"
                    @click="$router.push({ name: 'public.search', query: { brand: brand } })"
                >
                     <span class="font-black font-display text-slate-800 text-xl group-hover:scale-110 transition-transform">{{ brand }}</span>
                 </div>
             </div>
        </div>
        
        <!-- Trending Outfits (Moved down) -->
        <div class="bg-slate-50 py-16 border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-sm font-bold text-slate-400 tracking-widest uppercase">TRENDING OUTFITS</h3>
                    <router-link :to="{ name: 'public.search', query: { type: 'outfits' } }" class="text-xs font-bold text-violet-600 hover:text-violet-700 hover:underline flex items-center gap-1">
                        MORE OUTFITS <i class="pi pi-arrow-right text-[10px]"></i>
                    </router-link>
                </div>
                <!-- Simple 3-column grid for outfits -->
                 <div v-if="outfits.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div 
                        v-for="outfit in outfits.slice(0, 3)" 
                        :key="outfit.id" 
                        class="rounded-3xl overflow-hidden relative group aspect-square cursor-pointer"
                        @click="$router.push({ name: 'public.outfit.show', params: { id: outfit.id } })"
                    >
                        <img :src="outfit.thumbnail_url" referrerpolicy="no-referrer" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="bg-violet-600 text-white px-6 py-3 rounded-lg font-bold shadow-lg hover:scale-105 transition-transform flex items-center gap-2">
                                <i class="pi pi-bolt"></i> VER OUTFIT
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
