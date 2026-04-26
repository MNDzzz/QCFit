<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue';

const brands = ref([]);
const loading = ref(false);
const router = useRouter();

const breadcrumbItems = [
    { label: 'Home', to: { name: 'public.home' } },
    { label: 'Brands' }
];

onMounted(async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/brand-list');
        brands.value = res.data.data || res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
});

const searchByBrand = (brandName) => {
    router.push({ name: 'public.search', query: { brand: brandName } });
};
</script>

<template>
    <div class="min-h-screen bg-stone-50 pb-20 font-sans">
        <!-- Header Strip -->
        <div class="bg-white border-b border-slate-200 py-4 px-4 sticky top-16 z-30 shadow-sm">
            <div class="max-w-[1600px] mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
                 <div>
                    <Breadcrumbs :items="breadcrumbItems" />
                    <h1 class="text-2xl font-display font-bold text-slate-900 mt-1">
                        Todas las Marcas
                    </h1>
                 </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 pt-12">
            <p class="text-center text-slate-500 mb-12">
                Explora productos y outfits agrupados por tus marcas favoritas.
            </p>

            <div v-if="loading" class="flex justify-center py-20">
                <i class="pi pi-spin pi-spinner text-4xl text-violet-600"></i>
            </div>
            
            <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div 
                    v-for="brand in brands" 
                    :key="brand.id" 
                    class="h-20 bg-white rounded-xl border border-slate-200 flex items-center justify-center hover:border-slate-900 hover:shadow-lg transition-all cursor-pointer group px-4"
                    @click="searchByBrand(brand.name)"
                >
                    <span class="font-black font-display text-slate-800 text-lg group-hover:scale-110 transition-transform truncate text-center">{{ brand.name }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
