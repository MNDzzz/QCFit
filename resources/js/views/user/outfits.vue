<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { inject } from 'vue';

const toast = useToast();
// Use SweetAlert2 for nice modals
const swal = inject('$swal');

const outfits = ref([]);
const loading = ref(true);

onMounted(() => {
    fetchOutfits();
});

async function fetchOutfits() {
    loading.value = true;
    try {
        const response = await axios.get('/api/my-outfits');
        outfits.value = response.data.data || response.data;
    } catch (e) {
        console.error('Error fetching outfits:', e);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar tus outfits.', life: 3000 });
    } finally {
        loading.value = false;
    }
}

function confirmDelete(outfitId) {
    swal({
        title: '¿Estás seguro?',
        text: "Esta acción eliminará permanentemente tu outfit.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteOutfit(outfitId);
        }
    });
}

async function deleteOutfit(id) {
    try {
        await axios.delete(`/api/outfits/${id}`);
        // Eliminar del array localmente
        outfits.value = outfits.value.filter(o => o.id !== id);
        
        swal(
            '¡Eliminado!',
            'Tu outfit ha sido eliminado correctamente.',
            'success'
        );
    } catch (e) {
        console.error('Error deleting outfit:', e);
        swal(
            'Error',
            'Hubo un problema al eliminar el outfit.',
            'error'
        );
    }
}
</script>

<template>
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Mis Outfits</h1>
                <p class="text-slate-500 dark:text-slate-400">Gestiona y edita tus creaciones</p>
            </div>
            <router-link :to="{ name: 'public.studio' }" class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white font-medium rounded-lg shadow transition-colors flex items-center gap-2">
                <i class="pi pi-plus"></i>
                Crear Nuevo Outfit
            </router-link>
        </div>

        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="i in 4" :key="i" class="bg-white dark:bg-zinc-800 rounded-xl overflow-hidden shadow-sm border border-slate-100 dark:border-zinc-700">
                <Skeleton width="100%" height="250px" />
                <div class="p-4">
                    <Skeleton width="70%" height="1.5rem" class="mb-2" />
                    <Skeleton width="40%" height="1rem" />
                </div>
            </div>
        </div>

        <div v-else-if="outfits.length === 0" class="text-center py-20 bg-white dark:bg-zinc-800 rounded-2xl border border-slate-100 dark:border-zinc-700 shadow-sm">
            <div class="w-16 h-16 bg-slate-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-image text-slate-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-2">Aún no tienes outfits</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-6">Crea tu primer outfit en el Studio usando los productos que más te gusten.</p>
            <router-link :to="{ name: 'public.studio' }" class="px-6 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-full font-medium hover:opacity-90 transition-opacity">
                Ir al Studio
            </router-link>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div 
                v-for="outfit in outfits" 
                :key="outfit.id"
                class="bg-white dark:bg-zinc-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all group border border-slate-100 dark:border-zinc-700 flex flex-col"
            >
                <!-- Imagen -->
                <div class="aspect-[4/5] bg-slate-100 dark:bg-zinc-900 relative overflow-hidden group">
                    <img 
                        :src="outfit.thumbnail_url || '/images/placeholder-outfit.jpg'" 
                        referrerpolicy="no-referrer"
                        :alt="outfit.title"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                    >
                    
                    <!-- Overlay Options (Desktop Hover) -->
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-3 backdrop-blur-[2px]">
                        <router-link 
                            :to="{name: 'public.outfit.show', params: {id: outfit.id}}" 
                            class="px-5 py-2 w-32 bg-white/10 hover:bg-white/20 border border-white/30 text-white rounded-full flex items-center justify-center gap-2 transition-colors text-sm font-medium backdrop-blur-md"
                        >
                            <i class="pi pi-eye"></i> Ver
                        </router-link>
                        <router-link 
                            :to="{name: 'public.studio', query: {outfit_id: outfit.id}}" 
                            class="px-5 py-2 w-32 bg-white/10 hover:bg-white/20 border border-white/30 text-white rounded-full flex items-center justify-center gap-2 transition-colors text-sm font-medium backdrop-blur-md"
                        >
                            <i class="pi pi-pencil"></i> Editar
                        </router-link>
                        <button 
                            @click="confirmDelete(outfit.id)"
                            class="px-5 py-2 w-32 bg-red-500/80 hover:bg-red-500 text-white border border-red-400/50 rounded-full flex items-center justify-center gap-2 transition-colors text-sm font-medium backdrop-blur-md"
                        >
                            <i class="pi pi-trash"></i> Eliminar
                        </button>
                    </div>
                </div>

                <!-- Info -->
                <div class="p-4 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 dark:text-white truncate" :title="outfit.title">{{ outfit.title }}</h3>
                        <p v-if="outfit.description" class="text-sm text-slate-500 dark:text-slate-400 line-clamp-1 mt-1">
                            {{ outfit.description }}
                        </p>
                    </div>
                    
                    <div class="flex justify-between items-center mt-4 text-xs text-slate-500 dark:text-slate-400">
                        <span>{{ new Date(outfit.created_at).toLocaleDateString() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
