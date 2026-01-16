<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const query = ref('');
const router = useRouter();
const loading = ref(false);

const handleSearch = async () => {
    if (!query.value) return;

    loading.value = true;
    
    // Regex for basic URL detection (Taobao, Weidian, 1688, or generico http)
    const urlRegex = /^(http|https):\/\/[^ "]+$/;

    if (urlRegex.test(query.value)) {
        // CASE URL: Mock Scraping Service redirection
        console.log('URL detected, redirecting to scraping service...', query.value);
        
        // In a real app, you might call a backend endpoint here to scrape first
        // or redirect to a specific 'product-view' page that handles the scraping on load
        // For now, let's simulate a redirection to a product detail mock
        
        // Extract ID (very basic mock logic)
        const idMatch = query.value.match(/id=(\d+)/); 
        const mockId = idMatch ? idMatch[1] : 'temp-' + Date.now();
        
        router.push({ name: 'ProductDetail', params: { id: mockId }, query: { source_url: query.value } });

    } else {
        // CASE TEXT: Internal Search
        console.log('Text query detected, searching internal DB...', query.value);
        
        // Redirect to search results page
        router.push({ name: 'SearchResults', query: { q: query.value } });
    }

    loading.value = false;
};
</script>

<template>
    <div class="w-full max-w-2xl mx-auto">
        <div class="relative">
            <input 
                v-model="query"
                @keyup.enter="handleSearch"
                type="text" 
                class="w-full px-6 py-4 rounded-full border-2 border-gray-200 focus:border-indigo-500 focus:outline-none shadow-lg text-lg transition-all"
                placeholder="Busca marcas (Nike, Stussy) o pega un link de Taobao/Weidian..."
                :disabled="loading"
            >
            <button 
                @click="handleSearch"
                class="absolute right-2 top-2 bottom-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 rounded-full font-bold transition-colors flex items-center"
                :disabled="loading"
            >
                <span v-if="!loading">Buscar</span>
                <span v-else>...</span>
            </button>
        </div>
        <div class="mt-2 text-center text-sm text-gray-500">
            Inteligencia Artificial lista para escanear tus productos.
        </div>
    </div>
</template>
