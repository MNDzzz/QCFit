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
    
    // Regex for basic URL detection (Taobao, Weidian, 1688)
    const urlRegex = /(weidian\.com|taobao\.com|1688\.com)/i;

    if (urlRegex.test(query.value)) {
        // CASE URL: API Call for "Smart Detection"
        try {
            const response = await axios.get('/api/search', {
                params: { q: query.value }
            });

            const result = response.data;

            if (result.type === 'single_product' && result.data.is_cached) {
                // Producto encontrado en DB -> Ir directamente
                router.push({ 
                    name: 'public.product.show', 
                    params: { id: result.data.id } 
                });
            } else if (result.type === 'single_product') {
                // Producto escrapeado pero no guardado (Demo)
                // TODO: En el futuro ir al "Import Wizard"
                alert(`Producto Detectado: ${result.data.title}\n(Simulación de Importación)`);
            } else {
                // Fallback
                router.push({ name: 'public.search.results', query: { q: query.value } });
            }

        } catch (error) {
            console.error(error);
            // Si falla, search normal
            router.push({ name: 'public.search.results', query: { q: query.value } });
        }

    } else {
        // CASE TEXT: Internal Search directly to results page
        router.push({ name: 'public.search.results', query: { q: query.value } });
    }

    loading.value = false;
};
</script>

<template>
    <div class="w-full max-w-3xl mx-auto">
        <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-violet-600 to-indigo-600 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
            <div class="relative flex items-center bg-slate-900 rounded-full border border-slate-700 shadow-2xl overflow-hidden p-1">
                <i class="pi pi-search text-slate-500 ml-4 text-xl"></i>
                <input 
                    v-model="query"
                    @keyup.enter="handleSearch"
                    type="text" 
                    class="w-full px-4 py-3 bg-transparent text-white placeholder-slate-500 focus:outline-none text-base font-sans"
                    placeholder="Paste Weidian Link or search 'Travis Scott J1'..."
                    :disabled="loading"
                >
                <button 
                    @click="handleSearch"
                    class="bg-violet-600 hover:bg-violet-500 text-white px-8 py-3 rounded-full font-bold font-display tracking-tight transition-all transform hover:scale-105 shadow-lg shadow-violet-900/20"
                    :disabled="loading"
                >
                    <span v-if="!loading">Search</span>
                    <i v-else class="pi pi-spin pi-spinner"></i>
                </button>
            </div>
        </div>
    </div>
</template>
