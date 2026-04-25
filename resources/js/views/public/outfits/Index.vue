<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const outfits = ref([]);
const loading = ref(true);

onMounted(async () => {
    try {
        const res = await axios.get('/api/outfits');
        outfits.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Explore Outfits</h1>
            <p class="text-gray-500 mb-8">Get inspired by community creations and remix them.</p>

            <div v-if="loading" class="flex justify-center py-20">
                <i class="pi pi-spin pi-spinner text-4xl text-indigo-600"></i>
            </div>

            <div v-else-if="outfits.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div 
                    v-for="outfit in outfits" 
                    :key="outfit.id"
                    class="bg-white rounded-2xl shadow-lg overflow-hidden group hover:shadow-xl transition-all cursor-pointer"
                    @click="$router.push({ name: 'public.outfit.show', params: { id: outfit.id } })"
                >
                    <!-- Preview -->
                    <div class="h-64 bg-gray-200 relative overflow-hidden">
                        <div v-if="outfit.products && outfit.products.length" class="w-full h-full flex flex-wrap">
                             <img 
                                v-for="prod in outfit.products.slice(0, 4)" 
                                :key="prod.id"
                                :src="prod.images && prod.images[0] ? prod.images[0].url : ''" 
                                referrerpolicy="no-referrer"
                                class="w-1/2 h-1/2 object-cover"
                            >
                        </div>
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                            No preview available
                        </div>
                        
                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <button 
                                @click.stop="$router.push({name: 'public.studio', query: { outfit_id: outfit.id }})"
                                class="bg-white text-indigo-600 font-bold py-2 px-6 rounded-full transform scale-90 group-hover:scale-100 transition-transform flex items-center gap-2"
                            >
                                <i class="pi pi-refresh"></i>
                                REMIX THIS OUTFIT
                            </button>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold text-lg truncate">{{ outfit.name || 'Untitled Outfit' }}</h3>
                            <button class="text-gray-400 hover:text-red-500"><i class="pi pi-heart"></i></button>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-700">
                                {{ outfit.user ? outfit.user.name.substring(0,1) : '?' }}
                            </div>
                            <span class="text-sm text-gray-500">{{ outfit.user ? outfit.user.name : 'Unknown' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-20 bg-white rounded-xl shadow-inner">
                <h2 class="text-xl text-gray-400 font-semibold">No public outfits yet.</h2>
                <p class="text-gray-400 mt-2">Be the first to create one!</p>
                <button 
                    @click="$router.push({name: 'public.studio'})"
                    class="mt-4 bg-indigo-600 text-white px-6 py-2 rounded-full font-bold"
                >
                    Create Outfit
                </button>
            </div>
        </div>
    </div>
</template>
