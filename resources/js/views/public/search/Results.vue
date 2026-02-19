<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import ProductCard from '@/components/ui/ProductCard.vue';

const route = useRoute();
const results = ref([]);
const loading = ref(false);
const searchQuery = ref('');

// Sidebar Mock States
const filters = ref({
    marketplaces: { weidian: true, taobao: false, '1688': true },
    categories: ['Shoes', 'Sneakers', 'High Top'],
    brands: ['Nike', 'Jordan', 'Adidas', 'Balenciaga'],
    qcOnly: true
});

const searchdev = async () => {
    searchQuery.value = route.query.q || '';
    if (!searchQuery.value) return;
    
    loading.value = true;
    try {
        const res = await axios.get('/api/search', {
            params: { q: searchQuery.value }
        });
        
        // Si la API devuelve un objeto paginado Laravel, extraer data
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
}

const addToStudio = (product) => {
    console.log("Adding to studio", product);
    alert(`Added ${product.name} to Studio (Mock)`);
    // Logic to add to pinia store studio would go here
    // studioStore.addItem(product)
}

onMounted(() => {
    searchdev();
});

watch(() => route.query.q, () => {
    searchdev();
});
</script>

<template>
    <div class="min-h-screen bg-stone-50 pb-20 font-sans">
        <!-- Header Strip -->
        <div class="bg-white border-b border-slate-200 py-4 px-4 sticky top-16 z-30 shadow-sm">
            <div class="max-w-[1600px] mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
                 <div>
                    <div class="flex items-center text-xs text-slate-400 mb-1 space-x-2">
                        <span>Home</span> <i class="pi pi-angle-right text-[10px]"></i>
                        <span>Search</span> <i class="pi pi-angle-right text-[10px]"></i>
                        <span class="text-slate-600">{{ searchQuery }}</span>
                    </div>
                    <h1 class="text-2xl font-display font-bold text-slate-900">
                        Results for "{{ searchQuery }}" 
                        <span class="text-slate-400 font-sans text-lg font-normal ml-2 bg-slate-100 px-2 py-0.5 rounded-full text-base">{{ results.length }} items</span>
                    </h1>
                 </div>

                 <div class="flex items-center gap-4">
                      <div class="flex bg-slate-100 p-1 rounded-lg">
                          <button class="px-4 py-1.5 bg-violet-600 text-white rounded-md text-xs font-bold shadow-sm">PRODUCTS</button>
                          <button class="px-4 py-1.5 text-slate-500 hover:text-slate-900 rounded-md text-xs font-bold transition-colors">OUTFITS</button>
                      </div>
                      
                       <div class="flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-1.5 cursor-pointer hover:border-slate-300 bg-white">
                           <span class="text-xs text-slate-500">Sort by:</span>
                           <span class="text-sm font-medium text-slate-800">Relevance</span>
                           <i class="pi pi-chevron-down text-xs text-slate-400"></i>
                       </div>
                 </div>
            </div>
        </div>

        <div class="max-w-[1600px] mx-auto px-4 py-8 flex gap-8">
            <!-- Sidebar -->
            <aside class="hidden lg:block w-64 shrink-0 space-y-6">
                <!-- Marketplaces -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-900 mb-4 text-sm flex justify-between cursor-pointer">
                        Marketplace <i class="pi pi-chevron-up text-xs"></i>
                    </h3>
                    <div class="space-y-2">
                        <label v-for="(val, key) in filters.marketplaces" :key="key" class="flex items-center gap-3 cursor-pointer group">
                             <div class="relative flex items-center">
                                <input type="checkbox" checked class="peer h-4 w-4 cursor-pointer appearance-none rounded border border-slate-300 shadow-sm checked:bg-violet-600 checked:border-violet-600 transition-all" />
                                <i class="pi pi-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-[10px] text-white opacity-0 peer-checked:opacity-100 pointer-events-none"></i>
                             </div>
                             <span class="text-sm text-slate-600 group-hover:text-slate-900 capitalize">{{ key }}</span>
                        </label>
                    </div>
                </div>

                 <!-- Category -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-900 mb-4 text-sm flex justify-between cursor-pointer">
                        Category <i class="pi pi-chevron-up text-xs"></i>
                    </h3>
                     <div class="space-y-1 ml-1">
                         <div v-for="cat in filters.categories" :key="cat" class="flex items-center gap-2 py-1 cursor-pointer hover:bg-slate-50 rounded px-2 -ml-2 text-sm text-slate-600">
                             <i class="pi pi-angle-right text-xs text-slate-400"></i>
                             {{ cat }}
                         </div>
                     </div>
                </div>

                <!-- Price Range (Mock) -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-900 mb-4 text-sm">Price Range</h3>
                    <div class="h-1 bg-slate-200 rounded-full mb-4 relative">
                        <div class="absolute left-0 w-1/2 h-full bg-violet-600 rounded-full"></div>
                        <div class="absolute left-1/2 w-4 h-4 bg-white border-2 border-violet-600 rounded-full -top-1.5 shadow transform -translate-x-2 cursor-pointer"></div>
                    </div>
                    <div class="flex justify-between text-xs font-mono text-slate-500">
                        <span>¥200</span>
                        <span>¥800</span>
                    </div>
                </div>

                 <!-- Brands -->
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="font-bold text-slate-900 mb-3 text-sm">Brands</h3>
                    <div class="relative mb-3">
                        <i class="pi pi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input type="text" placeholder="Search brands..." class="w-full bg-slate-50 border border-slate-200 rounded-lg py-2 pl-8 pr-3 text-xs focus:outline-none focus:border-violet-500">
                    </div>
                     <div class="space-y-2 max-h-40 overflow-y-auto custom-scrollbar">
                        <label v-for="brand in filters.brands" :key="brand" class="flex items-center gap-3 cursor-pointer group">
                             <div class="relative flex items-center">
                                <input type="checkbox" class="peer h-4 w-4 cursor-pointer appearance-none rounded border border-slate-300 shadow-sm checked:bg-violet-600 checked:border-violet-600 transition-all" />
                                <i class="pi pi-check absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 text-[10px] text-white opacity-0 peer-checked:opacity-100 pointer-events-none"></i>
                             </div>
                             <span class="text-sm text-slate-600 group-hover:text-slate-900">{{ brand }}</span>
                        </label>
                    </div>
                </div>

                 <!-- QC Availability -->
                 <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                     <div>
                         <h3 class="font-bold text-slate-900 text-sm">QC Photos Only</h3>
                         <p class="text-[10px] text-slate-400">Verified by robots</p>
                     </div>
                     <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" value="" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                     </label>
                 </div>

            </aside>

            <!-- Main Grid -->
            <main class="flex-1">
                 <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <!-- Skeletons... -->
                 </div>
                 
                 <div v-else-if="results.length > 0" class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                     <ProductCard 
                        v-for="product in results" 
                        :key="product.id" 
                        :product="product"
                        @add-to-studio="addToStudio"
                     />
                 </div>
                 
                 <div v-else class="flex flex-col items-center justify-center py-20 bg-white rounded-xl border border-dashed border-slate-300 text-center">
                      <div class="bg-slate-50 p-6 rounded-full mb-4">
                          <i class="pi pi-search text-3xl text-slate-400"></i>
                      </div>
                      <h2 class="text-xl font-bold text-slate-800">No results found</h2>
                      <p class="text-slate-500 text-sm max-w-sm mt-2">Does not exist in our database yet? Try pasting the link directly in the Home page.</p>
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
