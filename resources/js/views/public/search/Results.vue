<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import ProductCard from '@/components/ui/ProductCard.vue';
import useProducts from '@/composables/products';
import OutfitCard from '@/components/ui/OutfitCard.vue';
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue';

const route = useRoute();
const router = useRouter();

const results = ref([]);
const loading = ref(false);
const { toggleFavorite } = useProducts();
const searchQuery = ref(route.query.q || '');
const currentType = ref(route.query.type || 'products'); // 'products' o 'outfits'

const localSearchQuery = ref(route.query.q || '');
const submitLocalSearch = () => {
    router.push({
        query: {
            ...route.query,
            q: localSearchQuery.value.trim() || undefined
        }
    });
};

const breadcrumbItems = computed(() => {
    const items = [
        { label: 'Home', to: { name: 'public.home' } },
        { label: 'Explore', to: { name: 'public.explore' } }
    ];
    if (searchQuery.value) {
        items.push({ label: `Results for "${searchQuery.value}"` });
    }
    return items;
});

// Filtros disponibles (desde API)
const availableFilters = ref({
    marketplaces: [],
    categories: [],
    brands: []
});

// Filtros seleccionados
const activeFilters = ref({
    marketplaces: [],
    categories: [],
    brands: []
});

const loadFilters = async () => {
    try {
        const [cats, brands, sources] = await Promise.all([
            axios.get('/api/category-list'),
            axios.get('/api/brand-list'),
            axios.get('/api/source-list')
        ]);
        availableFilters.value.categories = cats.data.data || cats.data;
        availableFilters.value.brands = brands.data.data || brands.data;
        availableFilters.value.marketplaces = sources.data.data || sources.data;
        
        // Si hay filtros en la URL, setearlos
        if (route.query.category) {
            const catObj = availableFilters.value.categories.find(c => c.name.toLowerCase() === route.query.category.toLowerCase() || c.id == route.query.category);
            if (catObj) {
                activeFilters.value.categories = [catObj.id];
            }
        }
        if (route.query.brand) {
            // Buscamos si es un ID o nombre
            const brandObj = availableFilters.value.brands.find(b => b.name.toLowerCase() === route.query.brand.toLowerCase() || b.id == route.query.brand);
            if (brandObj) {
                activeFilters.value.brands = [brandObj.id];
            }
        }
    } catch (e) {
        console.error('Error cargando filtros:', e);
    }
};

const searchdev = async () => {
    searchQuery.value = route.query.q || '';
    currentType.value = route.query.type || 'products';
    
    loading.value = true;
    try {
        const params = { 
            q: searchQuery.value,
            type: currentType.value
        };
        
        if (activeFilters.value.categories.length > 0) {
            params.category = activeFilters.value.categories[0]; 
        }
        if (activeFilters.value.brands.length > 0) {
            params.brand = activeFilters.value.brands[0];
        } else if (route.query.brand) {
            params.brand_name = route.query.brand;
        }
        if (activeFilters.value.marketplaces.length > 0) {
            params.source = activeFilters.value.marketplaces[0];
        }

        const res = await axios.get('/api/search', { params });
        
        if (res.data.data) {
            results.value = res.data.data;
        } else {
            results.value = res.data;
        }
    } catch (e) {
        console.error('Error en búsqueda:', e);
        results.value = [];
    } finally {
        loading.value = false;
    }
};

const toggleType = (type) => {
    if (currentType.value === type) return;
    
    // Al cambiar el tipo, actualizamos la url
    router.push({
        query: {
            ...route.query,
            type: type
        }
    });
};

const toggleFilter = (filterGroup, id) => {
    const index = activeFilters.value[filterGroup].indexOf(id);
    if (index > -1) {
        activeFilters.value[filterGroup].splice(index, 1);
    } else {
        // Para simplificar, hacemos que sea single select, si quieres multi select quita esta linea:
        activeFilters.value[filterGroup] = [id];
    }
    // Update URL or search
    const newQuery = { ...route.query };
    if (activeFilters.value.categories.length > 0) {
        newQuery.category = availableFilters.value.categories.find(c=>c.id===activeFilters.value.categories[0])?.name || activeFilters.value.categories[0];
    } else {
        delete newQuery.category;
    }
    
    if (activeFilters.value.brands.length > 0) {
        newQuery.brand = availableFilters.value.brands.find(b=>b.id===activeFilters.value.brands[0])?.name || activeFilters.value.brands[0];
    } else {
        delete newQuery.brand;
    }
    
    router.push({ query: newQuery });
};

const addToStudio = (product) => {
    toggleFavorite(product.id);
};

onMounted(async () => {
    await loadFilters();
    searchdev();
});

watch(() => route.query, () => {
    localSearchQuery.value = route.query.q || '';
    searchdev();
}, { deep: true });
</script>

<template>
    <div class="min-h-screen bg-stone-50 pb-20 font-sans">
        <!-- Header Strip -->
        <div class="bg-white border-b border-slate-200 py-4 px-4 sticky top-16 z-30 shadow-sm">
            <div class="max-w-[1600px] mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
                 <div>
                    <Breadcrumbs :items="breadcrumbItems" />
                    <h1 class="text-2xl font-display font-bold text-slate-900 mt-1">
                        {{ searchQuery ? `Results for "${searchQuery}"` : 'Explore' }} 
                        <span class="text-slate-400 font-sans text-lg font-normal ml-2 bg-slate-100 px-2 py-0.5 rounded-full text-base" v-if="!loading">{{ results.length }} items</span>
                    </h1>
                 </div>

                 <div class="flex flex-wrap items-center gap-4 mt-4 md:mt-0">
                      <!-- Buscador local -->
                      <form @submit.prevent="submitLocalSearch" class="relative w-full sm:w-auto flex items-center bg-white rounded-full p-1 border border-slate-200 shadow-sm focus-within:border-violet-500 focus-within:ring-1 focus-within:ring-violet-500 transition-all">
                          <i class="pi pi-search text-slate-400 ml-3 text-xs"></i>
                          <input 
                              v-model="localSearchQuery"
                              type="text" 
                              placeholder="Search keywords or paste link..." 
                              class="w-full sm:w-48 bg-transparent text-slate-800 text-sm font-medium pl-2 pr-2 py-1 focus:outline-none"
                          >
                          <button type="submit" class="bg-violet-600 hover:bg-violet-500 text-white px-4 py-1.5 rounded-full font-bold text-xs shadow-sm transition-colors">
                              Search
                          </button>
                      </form>

                      <div class="flex bg-slate-100 p-1 rounded-lg">
                          <button 
                            @click="toggleType('products')"
                            :class="currentType === 'products' ? 'bg-violet-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 transition-colors'"
                            class="px-4 py-1.5 rounded-md text-xs font-bold"
                          >
                            PRODUCTS
                          </button>
                          <button 
                            @click="toggleType('outfits')"
                            :class="currentType === 'outfits' ? 'bg-violet-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 transition-colors'"
                            class="px-4 py-1.5 rounded-md text-xs font-bold"
                          >
                            OUTFITS
                          </button>
                      </div>
                      
                       <div class="flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-1.5 cursor-pointer hover:border-slate-300 bg-white">
                           <span class="text-xs text-slate-500">Sort by:</span>
                           <span class="text-sm font-medium text-slate-800">Newest</span>
                           <i class="pi pi-chevron-down text-xs text-slate-400"></i>
                       </div>
                 </div>
            </div>
        </div>

        <div class="max-w-[1600px] mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8">
            <!-- Sidebar -->
            <aside class="w-full lg:w-64 shrink-0 space-y-6">
                <!-- Marketplaces -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm" v-if="availableFilters.marketplaces.length">
                    <h3 class="font-bold text-slate-900 mb-4 text-sm flex justify-between cursor-pointer">
                        Marketplace <i class="pi pi-chevron-up text-xs"></i>
                    </h3>
                    <div class="space-y-2">
                        <label v-for="source in availableFilters.marketplaces" :key="source.id" class="flex items-center gap-3 cursor-pointer group">
                             <div class="relative flex items-center">
                                <input 
                                    type="checkbox" 
                                    :checked="activeFilters.marketplaces.includes(source.id)"
                                    @change="toggleFilter('marketplaces', source.id)"
                                    class="peer h-4 w-4 cursor-pointer appearance-none rounded border border-slate-300 shadow-sm checked:bg-violet-600 checked:border-violet-600 transition-all" 
                                />
                                <i class="pi pi-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-[10px] text-white opacity-0 peer-checked:opacity-100 pointer-events-none"></i>
                             </div>
                             <span class="text-sm text-slate-600 group-hover:text-slate-900 capitalize">{{ source.name }}</span>
                        </label>
                    </div>
                </div>

                 <!-- Category -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm" v-if="availableFilters.categories.length">
                    <h3 class="font-bold text-slate-900 mb-4 text-sm flex justify-between cursor-pointer">
                        Category <i class="pi pi-chevron-up text-xs"></i>
                    </h3>
                     <div class="space-y-1 ml-1">
                         <div 
                            v-for="cat in availableFilters.categories" 
                            :key="cat.id" 
                            @click="toggleFilter('categories', cat.id)"
                            :class="activeFilters.categories.includes(cat.id) ? 'bg-violet-50 text-violet-700 font-medium' : 'text-slate-600 hover:bg-slate-50'"
                            class="flex items-center gap-2 py-1.5 cursor-pointer rounded px-2 -ml-2 text-sm transition-colors"
                         >
                             <i class="pi pi-angle-right text-xs" :class="activeFilters.categories.includes(cat.id) ? 'text-violet-500' : 'text-slate-400'"></i>
                             {{ cat.name }}
                         </div>
                     </div>
                </div>

                 <!-- Brands -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm" v-if="availableFilters.brands.length">
                    <h3 class="font-bold text-slate-900 mb-3 text-sm">Brands</h3>
                     <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar pr-2">
                        <label v-for="brand in availableFilters.brands" :key="brand.id" class="flex items-center gap-3 cursor-pointer group py-0.5">
                             <div class="relative flex items-center">
                                <input 
                                    type="checkbox" 
                                    :checked="activeFilters.brands.includes(brand.id)"
                                    @change="toggleFilter('brands', brand.id)"
                                    class="peer h-4 w-4 cursor-pointer appearance-none rounded border border-slate-300 shadow-sm checked:bg-violet-600 checked:border-violet-600 transition-all" 
                                />
                                <i class="pi pi-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-[10px] text-white opacity-0 peer-checked:opacity-100 pointer-events-none"></i>
                             </div>
                             <span class="text-sm text-slate-600 group-hover:text-slate-900">{{ brand.name }}</span>
                        </label>
                    </div>
                </div>
            </aside>

            <!-- Main Grid -->
            <main class="flex-1 w-full">
                 <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div v-for="i in 8" :key="i" class="aspect-[4/5] bg-slate-200 rounded-xl animate-pulse"></div>
                 </div>
                 
                 <div v-else-if="results.length > 0" class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                     <template v-if="currentType === 'products'">
                         <ProductCard 
                            v-for="product in results" 
                            :key="'prod_'+product.id" 
                            :product="product"
                            @add-to-studio="addToStudio"
                         />
                     </template>
                     <template v-else>
                         <OutfitCard 
                            v-for="outfit in results" 
                            :key="'outfit_'+outfit.id" 
                            :outfit="outfit"
                         />
                     </template>
                 </div>
                 
                 <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-xl border border-dashed border-slate-300 text-center">
                      <div class="bg-slate-50 p-6 rounded-full mb-4">
                          <i class="pi pi-search text-3xl text-slate-400"></i>
                      </div>
                      <h2 class="text-xl font-bold text-slate-800">No results found</h2>
                      <p class="text-slate-500 text-sm max-w-sm mt-2">Try adjusting your filters or search query.</p>
                      <button @click="$router.push('/')" class="mt-6 text-violet-600 font-bold hover:underline text-sm">Go Home</button>
                 </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Custom Scrollbar for brands list */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
