<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const results = ref([]);
const loading = ref(false);
const searchQuery = ref('');

const searchdev = async () => {
    searchQuery.value = route.query.q || '';
    if (!searchQuery.value) return;
    
    loading.value = true;
    try {
        const res = await axios.get(`/api/products/search?q=${searchQuery.value}`);
        results.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    searchdev();
});

watch(() => route.query.q, () => {
    searchdev();
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold mb-6 text-gray-800">Resultados para: "{{ searchQuery }}"</h1>
            
            <div v-if="loading" class="py-12 flex justify-center">
                <i class="pi pi-spin pi-spinner text-4xl text-indigo-600"></i>
            </div>

            <div v-else-if="results.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div 
                    v-for="product in results" 
                    :key="product.id"
                    class="bg-white rounded-xl shadow hover:shadow-lg transition-all cursor-pointer overflow-hidden border border-gray-100"
                    @click="$router.push({name: 'ProductDetail', params: {id: product.id}})"
                >
                    <div class="aspect-square bg-gray-200">
                         <img 
                            v-if="product.images && product.images.length" 
                            :src="product.images[0].url" 
                            class="w-full h-full object-cover"
                        >
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-gray-900 truncate">{{ product.name }}</h3>
                        <p class="text-sm text-gray-500">{{ product.brand || 'No Brand' }}</p>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-20 bg-white rounded-xl shadow-inner">
                <h2 class="text-xl text-gray-400 font-semibold">No se encontraron productos.</h2>
                <p class="text-gray-400 mt-2">Prueba con "Nike", "Stussy" o revisa el feed.</p>
                <div class="mt-8">
                     <button @click="$router.push('/')" class="text-indigo-600 font-bold hover:underline">Volver al Inicio</button>
                </div>
            </div>
        </div>
    </div>
</template>
