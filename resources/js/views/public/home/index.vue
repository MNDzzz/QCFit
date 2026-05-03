<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';
import SmartSearch from '@/components/SmartSearch.vue';
import ProductCard from '@/components/ui/ProductCard.vue';
import useProducts from '@/composables/products';
import { authStore } from '@/store/auth';

const useAuth = authStore();
const activeTab = ref('trending');
const outfits = ref([]);
const products = ref([]);
const loading = ref(false);
const { toggleFavorite } = useProducts();
const tickerItems = ref([]);

const categories = [
    { label: 'All', value: 'all', icon: '✦' },
    { label: 'Shoes', value: 'Shoes', icon: '👟' },
    { label: 'Tops', value: 'Tops', icon: '👕' },
    { label: 'Bottoms', value: 'Bottoms', icon: '👖' },
    { label: 'Accessories', value: 'Accessories', icon: '🎒' },
];
const activeCategory = ref('all');

// Marcas para la sección Browse by Brand (8 marcas para cuadrícula perfecta)
const brandList = [
    { name: 'KENZO', display: 'KENZO' },
    { name: 'NIKE', display: 'NIKE' },
    { name: 'ADIDAS', display: 'adidas' },
    { name: 'BALENCIAGA', display: 'BB' },
    { name: "ARC'TERYX", display: "ARC'TERYX" },
    { name: 'CARHARTT', display: 'CARHARTT' },
    { name: 'STUSSY', display: 'STÜSSY' },
    { name: 'SUPREME', display: 'Supreme' },
];

// Productos filtrados por categoría seleccionada
const filteredProducts = computed(() => {
    if (activeCategory.value === 'all') return products.value;
    return products.value.filter(p => {
        const catName = p.category?.name || '';
        
        switch (activeCategory.value.toLowerCase()) {
            case 'shoes':
                return catName === 'Sneakers';
            case 'tops':
                return ['Hoodies', 'Jackets', 'Sweaters', 'T-Shirts'].includes(catName);
            case 'bottoms':
                return catName === 'Pants';
            case 'accessories':
                return catName === 'Accessories';
            default:
                return true;
        }
    });
});

onMounted(() => {
    loadOutfits();
    loadProducts();
    loadTicker();
});

async function loadProducts() {
    try {
        const response = await axios.get('/api/products');
        products.value = response.data.data || [];
    } catch (e) {
        console.error('Error loading products', e);
    }
}

async function loadTicker() {
    try {
        const response = await axios.get('/api/feed/live');
        tickerItems.value = response.data.data || [];
    } catch (e) {
        // Fallback: usar productos como ticker
        tickerItems.value = products.value.slice(0, 8);
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

async function addToStudio(product) {
    await toggleFavorite(product.id);
}
</script>

<template>
    <div class="min-h-screen bg-stone-50 flex flex-col font-sans">
        <!-- ===== HERO SECTION (SIN TOCAR) ===== -->
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
        <!-- ===== FIN HERO ===== -->

        <!-- ===== TICKER MARQUEE (JUST CHECKED) ===== -->
        <div class="w-full bg-[#0B0F19] border-y border-white/5 py-4 overflow-hidden relative">
            <div class="flex animate-ticker-scroll gap-8 whitespace-nowrap" v-if="tickerItems.length > 0">
                <template v-for="(img, idx) in tickerItems" :key="'t1-' + idx">
                    <div
                        class="inline-flex items-center gap-3 bg-slate-900/60 border border-white/5 rounded-xl px-4 py-2.5 min-w-[260px] cursor-pointer hover:border-violet-500/30 transition-colors"
                        @click="$router.push({ name: 'public.product.show', params: { id: img.product_id || img.id } })"
                    >
                        <div class="w-12 h-12 rounded-lg bg-slate-800 overflow-hidden flex-shrink-0">
                            <img :src="img.url || img.images?.[0]?.url" referrerpolicy="no-referrer" class="w-full h-full object-cover" v-if="img.url || img.images?.[0]?.url">
                            <div v-else class="w-full h-full flex items-center justify-center"><i class="pi pi-image text-slate-600"></i></div>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] font-bold text-violet-400 uppercase tracking-wider flex items-center gap-1">
                                <i class="pi pi-check-circle text-[8px]"></i> JUST CHECKED
                            </span>
                            <span class="text-white text-xs font-medium truncate max-w-[160px]">{{ img.title || img.name || 'Product' }}</span>
                            <span class="text-slate-500 text-[10px]">{{ img.date_human || 'recently' }}</span>
                        </div>
                    </div>
                </template>
                <!-- Duplicado para scroll infinito -->
                <template v-for="(img, idx) in tickerItems" :key="'t2-' + idx">
                    <div
                        class="inline-flex items-center gap-3 bg-slate-900/60 border border-white/5 rounded-xl px-4 py-2.5 min-w-[260px] cursor-pointer hover:border-violet-500/30 transition-colors"
                        @click="$router.push({ name: 'public.product.show', params: { id: img.product_id || img.id } })"
                    >
                        <div class="w-12 h-12 rounded-lg bg-slate-800 overflow-hidden flex-shrink-0">
                            <img :src="img.url || img.images?.[0]?.url" referrerpolicy="no-referrer" class="w-full h-full object-cover" v-if="img.url || img.images?.[0]?.url">
                            <div v-else class="w-full h-full flex items-center justify-center"><i class="pi pi-image text-slate-600"></i></div>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] font-bold text-violet-400 uppercase tracking-wider flex items-center gap-1">
                                <i class="pi pi-check-circle text-[8px]"></i> JUST CHECKED
                            </span>
                            <span class="text-white text-xs font-medium truncate max-w-[160px]">{{ img.title || img.name || 'Product' }}</span>
                            <span class="text-slate-500 text-[10px]">{{ img.date_human || 'recently' }}</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ===== POPULAR RIGHT NOW ===== -->
        <div class="bg-white py-20">
            <div class="max-w-6xl mx-auto px-6">
                <h2 class="text-center text-2xl md:text-3xl font-display font-black text-slate-900 tracking-tight mb-8 uppercase">
                    Popular Right Now
                </h2>

                <!-- Categorías centradas -->
                <div class="flex justify-center flex-wrap gap-2 mb-12">
                    <button
                        v-for="cat in categories"
                        :key="cat.value"
                        @click="activeCategory = cat.value"
                        class="px-5 py-2 rounded-full text-xs font-bold border transition-all"
                        :class="activeCategory === cat.value
                            ? 'bg-slate-900 text-white border-slate-900 shadow-lg'
                            : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                    >
                        <span v-if="cat.icon" class="mr-1">{{ cat.icon }}</span>
                        {{ cat.label }}
                    </button>
                </div>

                <!-- Products Grid 4 columnas -->
                <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-5">
                     <div v-for="i in 8" :key="i" class="aspect-square bg-slate-100 rounded-xl animate-pulse"></div>
                </div>

                <div v-else-if="filteredProducts.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <ProductCard
                        v-for="product in filteredProducts.slice(0, 8)"
                        :key="product.id"
                        :product="product"
                        @add-to-studio="addToStudio"
                    />
                </div>

                <div v-else class="text-center py-16 text-slate-400">
                    <i class="pi pi-search text-3xl mb-3 opacity-40 block"></i>
                    <p>No products found.</p>
                </div>
            </div>
        </div>

        <!-- ===== BROWSE BY BRAND ===== -->
        <div class="bg-white border-t border-slate-100 py-20">
            <div class="max-w-6xl mx-auto px-6">
                <h3 class="text-center text-sm font-bold text-slate-400 tracking-[0.2em] uppercase mb-10">BROWSE BY BRAND</h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4 w-full">
                    <div
                        v-for="brand in brandList"
                        :key="brand.name"
                        class="w-full h-16 md:h-20 bg-white rounded-xl border border-slate-200 flex items-center justify-center hover:border-slate-900 hover:shadow-lg transition-all cursor-pointer group"
                        @click="$router.push({ name: 'public.search', query: { brand: brand.name } })"
                    >
                        <span class="font-black font-display text-slate-800 text-sm md:text-base group-hover:scale-110 transition-transform tracking-tight">{{ brand.display }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== TRENDING OUTFITS ===== -->
        <div class="bg-slate-50 border-t border-slate-200 py-20">
            <div class="max-w-6xl mx-auto px-6">
                <h3 class="text-center text-sm font-bold text-slate-400 tracking-[0.2em] uppercase mb-12">TRENDING OUTFITS</h3>

                <div v-if="outfits.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="outfit in outfits.slice(0, 3)"
                        :key="outfit.id"
                        class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all group border border-slate-100"
                    >
                        <!-- User Info Header -->
                        <div class="flex items-center gap-2 px-4 py-3 border-b border-slate-50">
                            <router-link
                                :to="{ name: 'public.profile', params: { id: outfit.user?.id || 0 } }"
                                class="flex items-center gap-2 min-w-0"
                            >
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0 ring-2 ring-violet-200">
                                    {{ outfit.user?.name?.charAt(0) || 'U' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ outfit.user?.alias || outfit.user?.name || 'User' }}</p>
                                    <p class="text-[10px] text-slate-400">{{ outfit.items_count || 0 }} items</p>
                                </div>
                            </router-link>
                            <span class="ml-auto bg-emerald-50 text-emerald-600 text-[10px] font-bold px-2 py-0.5 rounded-full">● Live</span>
                        </div>

                        <!-- Outfit Image -->
                        <div
                            class="aspect-[4/5] bg-slate-100 relative overflow-hidden cursor-pointer"
                            @click="$router.push({ name: 'public.outfit.show', params: { id: outfit.id } })"
                        >
                            <img
                                :src="outfit.thumbnail_url"
                                referrerpolicy="no-referrer"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                            >
                            <!-- Botón REMIX THIS FIT -->
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <button class="bg-violet-600 hover:bg-violet-500 text-white px-5 py-2.5 rounded-full text-xs font-bold shadow-xl flex items-center gap-2 transform hover:scale-105 transition-all">
                                    <i class="pi pi-bolt"></i> REMIX THIS FIT
                                </button>
                            </div>
                        </div>

                        <!-- Stats Footer -->
                        <div class="flex items-center justify-between px-4 py-3">
                            <div class="flex items-center gap-4 text-slate-400">
                                <span class="flex items-center gap-1 text-xs">
                                    <i class="pi pi-heart text-[11px]"></i> {{ Math.floor(Math.random() * 50) + 5 }}
                                </span>
                                <span class="flex items-center gap-1 text-xs">
                                    <i class="pi pi-comment text-[11px]"></i> {{ Math.floor(Math.random() * 15) }}
                                </span>
                            </div>
                            <span class="text-[10px] text-slate-400">{{ outfit.title || 'Untitled' }}</span>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16 text-slate-400">
                    <i class="pi pi-compass text-3xl mb-3 opacity-40 block"></i>
                    <p>No trending outfits found.</p>
                </div>
            </div>
        </div>

        <!-- ===== FOOTER ===== -->
        <footer class="bg-[#0B0F19] text-white pt-20 pb-10">
            <div class="max-w-6xl mx-auto px-6">
                <!-- Info Columns -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-16">
                    <div>
                        <h4 class="text-sm font-bold text-white mb-4 tracking-wider">What is QCFit?</h4>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            QCFit is a platform that lets you search for real QC (Quality Control) photos from Weidian, Taobao and 1688 sellers. Compare products before buying and make smarter purchase decisions.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white mb-4 tracking-wider">How to find QC?</h4>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Simply paste a Weidian or Taobao link in the search bar, or type the product name. We'll show you real inspection photos from our global community of QC inspectors.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white mb-4 tracking-wider">Outfit Builder</h4>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Use our interactive Studio to drag and drop products onto a canvas and create your dream outfit. Share it with the community, remix other people's looks, and get inspired.
                        </p>
                    </div>
                </div>

                <!-- Logo & Links -->
                <div class="border-t border-slate-800 pt-10 flex flex-col items-center gap-6">
                    <img src="/images/qcfit.svg" alt="QCFit" class="h-10 w-auto opacity-80">

                    <div class="flex items-center gap-6 text-sm text-slate-400">
                        <a href="#" class="hover:text-white transition-colors">Terms</a>
                        <a href="#" class="hover:text-white transition-colors">Privacy</a>
                        <a href="#" class="hover:text-white transition-colors">Contact us</a>
                    </div>

                    <!-- Social Icons -->
                    <div class="flex items-center gap-4">
                        <a href="#" class="w-9 h-9 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-all">
                            <i class="pi pi-discord text-sm"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-all">
                            <i class="pi pi-instagram text-sm"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-all">
                            <i class="pi pi-youtube text-sm"></i>
                        </a>
                        <a href="#" class="w-9 h-9 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-all">
                            <i class="pi pi-twitter text-sm"></i>
                        </a>
                    </div>

                    <p class="text-xs text-slate-600 mt-4">&copy; {{ new Date().getFullYear() }} QCFit | Your style, your rules.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Animación del ticker marquee */
.animate-ticker-scroll {
    animation: ticker-scroll 40s linear infinite;
}
@keyframes ticker-scroll {
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
