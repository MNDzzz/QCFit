<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

import { useToast } from "primevue/usetoast";

const query = ref('');
const router = useRouter();
const loading = ref(false);
const toast = useToast();

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
                toast.add({ 
                    severity: 'success', 
                    summary: 'Producto Detectado', 
                    detail: `${result.data.title} (Simulación de Importación)`, 
                    life: 5000 
                });
            } else {
                // Fallback
                router.push({ name: 'public.search', query: { q: query.value } });
            }

        } catch (error) {
            console.error(error);
            // Si falla, search normal
            router.push({ name: 'public.search', query: { q: query.value } });
        }

    } else {
        // CASE TEXT: Internal Search directly to results page
        router.push({ name: 'public.search', query: { q: query.value } });
    }

    loading.value = false;
};
</script>

<template>
    <div class="w-full max-w-2xl mx-auto">
        <div class="relative group">
            <!-- Glow Effect -->
            <div class="absolute -inset-1 bg-gradient-to-r from-violet-600 to-indigo-600 rounded-full blur opacity-30 group-hover:opacity-60 transition duration-1000 group-hover:duration-200"></div>
            
            <!-- Main Input Container -->
            <div class="relative flex items-center bg-white rounded-full p-1 shadow-2xl ring-1 ring-slate-900/5">
                <i class="pi pi-search text-slate-400 ml-5 text-xl"></i>
                <input 
                    v-model="query"
                    @keyup.enter="handleSearch"
                    type="text" 
                    class="w-full px-4 py-3 bg-transparent text-slate-900 placeholder-slate-400 focus:outline-none text-base font-medium"
                    placeholder="Paste Weidian Link or search 'Travis Scott J1'..."
                    :disabled="loading"
                >
                <button 
                    @click="handleSearch"
                    class="bg-violet-600 hover:bg-violet-500 text-white px-8 py-2.5 rounded-full font-bold tracking-wide transition-all transform hover:scale-105 shadow-lg shadow-violet-600/30"
                    :disabled="loading"
                >
                    <span v-if="!loading">Search</span>
                    <i v-else class="pi pi-spin pi-spinner"></i>
                </button>
            </div>
        </div>
    </div>
</template>

