<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

// Estado
const outfit = ref(null);
const loading = ref(true);
const error = ref(null);

// Cargar outfit al montar el componente
onMounted(async () => {
    await fetchOutfit();
});

/**
 * Obtener los datos del outfit desde la API.
 */
async function fetchOutfit() {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(`/api/outfits/${route.params.id}`);
        outfit.value = response.data.data || response.data;
    } catch (err) {
        console.error('Error cargando outfit:', err);
        
        if (err.response?.status === 404) {
            error.value = 'Este outfit no existe o ha sido eliminado.';
        } else {
            error.value = 'Error al cargar el outfit. Intenta nuevamente.';
        }
    } finally {
        loading.value = false;
    }
}

/**
 * Navegar al Studio con el outfit pre-cargado (Remix).
 */
function handleRemix() {
    router.push({
        name: 'Studio',
        query: { outfit_id: outfit.value.id }
    });
}

/**
 * Navegar al detalle de un producto.
 */
function goToProduct(productId) {
    router.push({
        name: 'ProductDetail',
        params: { id: productId }
    });
}

// Computed: Primera imagen QC disponible para preview
const previewImage = computed(() => {
    if (!outfit.value?.items?.length) return null;
    
    // Buscar la primera imagen QC de cualquier producto
    for (const item of outfit.value.items) {
        if (item.product?.images?.length) {
            const qcImage = item.product.images.find(img => img.type === 'qc');
            if (qcImage) return qcImage.url;
            return item.product.images[0].url;
        }
    }
    return null;
});

// Computed: Fecha formateada
const formattedDate = computed(() => {
    if (!outfit.value?.created_at) return '';
    
    const date = new Date(outfit.value.created_at);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});
</script>

<template>
    <div class="min-h-screen bg-stone-50 font-sans pb-20">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center items-center h-screen">
            <div class="text-center">
                <i class="pi pi-spin pi-spinner text-4xl text-violet-600 mb-4"></i>
                <p class="text-slate-600">Cargando outfit...</p>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="flex flex-col justify-center items-center h-screen text-center px-4">
            <i class="pi pi-exclamation-circle text-6xl text-red-400 mb-4"></i>
            <h2 class="text-2xl font-bold text-slate-800 mb-2">¡Oops!</h2>
            <p class="text-slate-600 mb-6">{{ error }}</p>
            <router-link to="/" class="px-6 py-3 bg-violet-600 text-white rounded-full font-bold hover:bg-violet-500 transition-colors">
                Volver al inicio
            </router-link>
        </div>

        <!-- Outfit Detail -->
        <div v-else-if="outfit" class="max-w-6xl mx-auto px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <nav class="flex items-center text-sm text-slate-400 mb-4">
                    <router-link to="/" class="hover:text-violet-600">Inicio</router-link>
                    <i class="pi pi-chevron-right mx-2 text-xs"></i>
                    <span class="text-slate-700">Outfit</span>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
                <div class="grid lg:grid-cols-2 gap-0">
                    
                    <!-- Left: Outfit Preview (Collage o Thumbnail) -->
                    <div class="relative bg-gradient-to-br from-slate-100 to-slate-200 p-8 min-h-[500px] flex items-center justify-center">
                        <!-- Collage de productos -->
                        <div class="relative w-full h-96 bg-white rounded-2xl shadow-inner overflow-hidden">
                            <!-- Si hay thumbnail_url, mostrarla -->
                            <img 
                                v-if="outfit.thumbnail_url" 
                                :src="outfit.thumbnail_url" 
                                :alt="outfit.title"
                                class="w-full h-full object-cover"
                            />
                            
                            <!-- Si no, mostrar collage de productos -->
                            <div v-else class="grid grid-cols-2 gap-2 p-4 h-full">
                                <div 
                                    v-for="(item, index) in outfit.items?.slice(0, 4)" 
                                    :key="item.product_id"
                                    class="relative rounded-xl overflow-hidden bg-slate-100"
                                >
                                    <img 
                                        v-if="item.product?.images?.length"
                                        :src="item.product.images[0].url" 
                                        class="w-full h-full object-cover"
                                        :alt="item.product.name"
                                    />
                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                        <i class="pi pi-image text-2xl"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Badge de cantidad de items -->
                        <div class="absolute top-6 right-6 bg-slate-900/80 backdrop-blur-sm text-white px-4 py-2 rounded-full text-sm font-bold">
                            {{ outfit.items_count || outfit.items?.length || 0 }} prendas
                        </div>
                    </div>

                    <!-- Right: Outfit Info -->
                    <div class="p-8 lg:p-12 flex flex-col">
                        <!-- User Info -->
                        <div v-if="outfit.user" class="flex items-center gap-3 mb-6">
                            <img 
                                :src="outfit.user.avatar || '/images/default-avatar.png'" 
                                class="w-12 h-12 rounded-full object-cover border-2 border-slate-200"
                                :alt="outfit.user.name"
                            />
                            <div>
                                <h4 class="font-bold text-slate-800">{{ outfit.user.alias || outfit.user.name }}</h4>
                                <p class="text-sm text-slate-500">{{ formattedDate }}</p>
                            </div>
                        </div>

                        <!-- Title & Description -->
                        <h1 class="text-3xl lg:text-4xl font-black text-slate-900 mb-4">
                            {{ outfit.title }}
                        </h1>
                        
                        <p v-if="outfit.description" class="text-lg text-slate-600 mb-8">
                            {{ outfit.description }}
                        </p>

                        <!-- Stats -->
                        <div class="flex gap-6 mb-8">
                            <div class="text-center">
                                <span class="block text-2xl font-bold text-violet-600">
                                    {{ outfit.items_count || outfit.items?.length || 0 }}
                                </span>
                                <span class="text-xs text-slate-500 uppercase tracking-wide">Productos</span>
                            </div>
                        </div>

                        <!-- Remix Button (CTA Principal) -->
                        <button 
                            @click="handleRemix"
                            class="w-full py-4 bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white rounded-2xl font-bold text-lg shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all flex items-center justify-center gap-3 mb-4"
                        >
                            <i class="pi pi-refresh"></i>
                            Remix This Outfit
                        </button>

                        <p class="text-center text-sm text-slate-500 mb-8">
                            Abre este outfit en el Studio y personalízalo a tu gusto
                        </p>

                        <!-- Divider -->
                        <div class="border-t border-slate-100 my-6"></div>

                        <!-- Share -->
                        <div class="flex items-center justify-center gap-4">
                            <span class="text-sm text-slate-500">Compartir:</span>
                            <button class="w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors">
                                <i class="pi pi-link"></i>
                            </button>
                            <button class="w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors">
                                <i class="pi pi-twitter"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shop the Look Section -->
            <div v-if="outfit.items?.length" class="mt-12">
                <h2 class="text-2xl font-black text-slate-900 mb-6 flex items-center gap-3">
                    <i class="pi pi-shopping-bag text-violet-600"></i>
                    Shop the Look
                </h2>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div 
                        v-for="item in outfit.items" 
                        :key="item.product_id"
                        @click="goToProduct(item.product_id)"
                        class="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-xl cursor-pointer transition-all duration-300 transform hover:-translate-y-1"
                    >
                        <!-- Product Image -->
                        <div class="aspect-square overflow-hidden bg-slate-100 relative">
                            <img 
                                v-if="item.product?.images?.length"
                                :src="item.product.images[0].url" 
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                :alt="item.product.name"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                <i class="pi pi-image text-4xl"></i>
                            </div>
                            
                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                                <span class="text-white text-sm font-medium">Ver producto →</span>
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-4">
                            <p class="text-xs text-violet-600 font-bold uppercase mb-1">
                                {{ item.product?.brand || 'N/A' }}
                            </p>
                            <h3 class="font-bold text-slate-800 text-sm line-clamp-2">
                                {{ item.product?.name || 'Producto' }}
                            </h3>
                            <p class="text-slate-500 text-xs mt-1 capitalize">
                                {{ item.product?.marketplace || 'weidian' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
