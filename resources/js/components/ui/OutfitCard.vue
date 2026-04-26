<script setup>
import { useRouter } from 'vue-router';

const props = defineProps({
    outfit: {
        type: Object,
        required: true
    }
});

const router = useRouter();

const viewOutfit = () => {
    router.push({ name: 'public.outfit.show', params: { id: props.outfit.id } });
};

const remixOutfit = () => {
    router.push({ name: 'public.studio', query: { outfit_id: props.outfit.id } });
};
</script>

<template>
    <div 
        class="bg-white rounded-2xl shadow-lg overflow-hidden group hover:shadow-xl transition-all cursor-pointer flex flex-col h-full"
        @click="viewOutfit"
    >
        <!-- Preview -->
        <div class="h-64 bg-gray-200 relative overflow-hidden shrink-0">
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
                Sin vista previa
            </div>
            
            <!-- Overlay -->
            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <button 
                    @click.stop="remixOutfit"
                    class="bg-white text-indigo-600 font-bold py-2 px-6 rounded-full transform scale-90 group-hover:scale-100 transition-transform flex items-center gap-2"
                >
                    <i class="pi pi-refresh"></i>
                    REMIX
                </button>
            </div>
        </div>

        <div class="p-4 flex-1 flex flex-col justify-between">
            <div class="flex items-start justify-between mb-2 gap-2">
                <h3 class="font-bold text-sm text-slate-800 line-clamp-2">{{ outfit.title || outfit.name || 'Outfit sin nombre' }}</h3>
                <button class="text-gray-400 hover:text-red-500 shrink-0"><i class="pi pi-heart"></i></button>
            </div>
            <div class="flex items-center gap-2 mt-auto pt-2 border-t border-slate-100">
                <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-700 overflow-hidden">
                    <img v-if="outfit.user && outfit.user.avatar" :src="outfit.user.avatar" class="w-full h-full object-cover">
                    <span v-else>{{ outfit.user ? outfit.user.name.substring(0,1) : '?' }}</span>
                </div>
                <span class="text-xs text-gray-500 truncate">{{ outfit.user ? outfit.user.name : 'Unknown' }}</span>
            </div>
        </div>
    </div>
</template>
