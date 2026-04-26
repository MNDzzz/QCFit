<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { authStore } from '../../../store/auth';
import useAuth from '@/composables/auth';
import { useToast } from "primevue/usetoast";
import { useHead } from '@vueuse/head';
import FollowersModal from '@/components/ui/FollowersModal.vue';

const route = useRoute();
const auth = authStore();
const { logout } = useAuth();
const toast = useToast();

const user = ref(null);
const outfits = ref([]);
const loading = ref(true);
const followLoading = ref(false);
const error = ref(null);

// --- Filtrado y ordenación local (íntegramente en Vue, sin petición al servidor) ---
const outfitSearchQuery = ref('');
const outfitSortBy = ref('newest'); // 'newest', 'oldest', 'most_items', 'alpha'

// Computed que filtra y ordena el array local de outfits sin tocar la API
const filteredOutfits = computed(() => {
    let filtered = [...outfits.value];

    // 1. Filtrar por texto de búsqueda sobre el array local
    if (outfitSearchQuery.value.trim()) {
        const query = outfitSearchQuery.value.toLowerCase().trim();
        filtered = filtered.filter(outfit =>
            outfit.title?.toLowerCase().includes(query) ||
            outfit.description?.toLowerCase().includes(query)
        );
    }

    // 2. Ordenar el array resultante
    switch (outfitSortBy.value) {
        case 'oldest':
            filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            break;
        case 'most_items':
            filtered.sort((a, b) => (b.items_count || 0) - (a.items_count || 0));
            break;
        case 'alpha':
            filtered.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
            break;
        case 'newest':
        default:
            filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            break;
    }

    return filtered;
});

// Modal state
const showFollowersModal = ref(false);
const modalType = ref('followers');

function openFollowModal(type) {
    modalType.value = type;
    showFollowersModal.value = true;
}

// Pagination
const currentPage = ref(1);
const lastPage = ref(1);

onMounted(() => {
    fetchProfile(route.params.id);
});

// Watch route param changes (to reload if navigating from one profile to another)
watch(() => route.params.id, (newId) => {
    fetchProfile(newId);
});

async function fetchProfile(userId) {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get(`/api/public/user/${userId}`);
        
        user.value = response.data.user;

        // SEO Meta Tags
        useHead({
            title: computed(() => user.value ? `${user.value.name} (@${user.value.alias}) - QCFit` : 'Perfil - QCFit'),
            meta: [
                {
                    name: 'description',
                    content: computed(() => user.value?.bio || `Check out ${user.value?.name}'s outfits on QCFit.`)
                },
                {
                    property: 'og:title',
                    content: computed(() => user.value?.name)
                },
                {
                    property: 'og:image',
                    content: computed(() => user.value?.avatar || '/images/default-avatar.png')
                }
            ]
        });
        
        // Handle pagination structure verification with extreme safety
        const outfitsData = response.data.outfits;
        if (outfitsData?.data) {
            outfits.value = outfitsData.data;
            lastPage.value = outfitsData.meta?.last_page || 1;
        } else if (Array.isArray(outfitsData)) {
            outfits.value = outfitsData;
        } else {
            console.warn('Unexpected outfits structure, defaulting to empty array', outfitsData);
            outfits.value = [];
        }
    } catch (e) {
        console.error('Error fetching profile:', e);
        error.value = 'Usuario no encontrado o error de conexión.';
    } finally {
        loading.value = false;
    }
}

async function toggleFollow() {
    if (!user.value) return;
    
    // Optimistic Update
    const previousState = user.value.is_following;
    const previousCount = user.value.stats.followers_count;
    
    user.value.is_following = !previousState;
    user.value.stats.followers_count += (user.value.is_following ? 1 : -1);
    
    followLoading.value = true;
    
    try {
        await axios.post('/api/follow', { user_id: user.value.id });
        // Success
    } catch (e) {
        // Revert on error
        user.value.is_following = previousState;
        user.value.stats.followers_count = previousCount;
        
        if (e.response && e.response.status === 401) {
             toast.add({ severity: 'info', summary: 'Acceso Restringido', detail: 'Debes iniciar sesión para seguir a usuarios.', life: 3000 });
             // O redirigir a login
        } else {
             console.error('Error following user:', e);
        }
    } finally {
        followLoading.value = false;
    }
}

const isMe = computed(() => {
    return auth.authenticated && auth.user && auth.user.id === user.value?.id;
});

</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-zinc-900 pb-20">
        <!-- Loading State -->
        <div v-if="loading" class="animate-fade-in">
             <!-- Header Skeleton -->
            <div class="h-48 md:h-64 bg-slate-200 dark:bg-zinc-800 w-full relative overflow-hidden"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-10">
                <div class="flex flex-col md:flex-row items-center md:items-end gap-6 mb-8">
                    <!-- Avatar Skeleton -->
                    <div class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white dark:border-zinc-900 bg-slate-200 dark:bg-zinc-800 overflow-hidden shadow-xl">
                        <Skeleton width="100%" height="100%" />
                    </div>

                    <!-- User Info Skeleton -->
                    <div class="flex-1 text-center md:text-left mb-4 md:mb-0 w-full max-w-md">
                        <Skeleton width="12rem" height="2rem" class="mb-2 mx-auto md:mx-0" />
                        <Skeleton width="8rem" height="1rem" class="mb-4 mx-auto md:mx-0" />
                        
                        <div class="flex items-center justify-center md:justify-start gap-6">
                            <Skeleton width="4rem" height="2rem" />
                            <Skeleton width="4rem" height="2rem" />
                            <Skeleton width="4rem" height="2rem" />
                        </div>
                    </div>
                </div>
                 <!-- Bio Skeleton -->
                 <div class="max-w-2xl mb-8">
                    <Skeleton width="100%" height="4rem" />
                 </div>
            </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="flex flex-col items-center justify-center h-screen px-4 text-center">
            <i class="pi pi-exclamation-triangle text-4xl text-red-500 mb-4"></i>
            <h2 class="text-2xl font-bold text-slate-800 dark:text-white mb-2">Error</h2>
            <p class="text-slate-500 mb-6">{{ error }}</p>
            <router-link to="/" class="px-6 py-2 bg-slate-200 hover:bg-slate-300 rounded-full text-slate-700 transition-colors">
                Volver al inicio
            </router-link>
        </div>

        <!-- Profile Content -->
        <div v-else class="animate-fade-in">
            <!-- Header / Cover -->
            <div class="h-48 md:h-64 bg-gradient-to-r from-violet-600 to-fuchsia-600 relative overflow-hidden">
                <div class="absolute inset-0 bg-black/10"></div>
                <!-- Decorative pattern -->
                <div class="absolute inset-0 opacity-20"
                    style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 20px 20px;">
                </div>

                <!-- Back to Home -->
                <div class="absolute top-4 left-4 z-10">
                    <router-link 
                        to="/"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/15 backdrop-blur-md text-white text-sm font-medium hover:bg-white/25 transition-all"
                    >
                        <i class="pi pi-arrow-left text-xs"></i>
                        Back to Home
                    </router-link>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-10">
                <div class="flex flex-col md:flex-row items-center md:items-end gap-6 mb-8">
                    <!-- Avatar -->
                    <div class="relative group">
                        <div class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white dark:border-zinc-900 overflow-hidden bg-white shadow-xl">
                            <img 
                                :src="user.avatar || '/images/placeholder-user.jpg'" 
                                referrerpolicy="no-referrer"
                                :alt="user.name"
                                class="w-full h-full object-cover"
                            >
                        </div>
                    </div>

                    <!-- User Info -->
                    <div class="flex-1 text-center md:text-left mb-4 md:mb-0">
                        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-1">
                            {{ user.name }}
                            <i v-if="user.is_verified" class="pi pi-check-circle text-blue-500 text-xl ml-1" title="Verificado"></i>
                        </h1>
                        <p class="text-slate-500 dark:text-slate-400 font-medium mb-3">@{{ user.alias || 'user' }}</p>
                        
                        <!-- Stats Row -->
                        <div class="flex items-center justify-center md:justify-start gap-6 text-sm">
                            <div class="text-center md:text-left">
                                <span class="block font-bold text-lg text-slate-900 dark:text-white">
                                    {{ user?.stats?.outfits_count || 0 }}
                                </span>
                                <span class="text-slate-500 dark:text-slate-400">Outfits</span>
                            </div>
                            <div 
                                class="text-center md:text-left cursor-pointer hover:opacity-80 transition-opacity"
                                @click="openFollowModal('followers')"
                            >
                                <span class="block font-bold text-lg text-slate-900 dark:text-white">
                                    {{ user?.stats?.followers_count || 0 }}
                                </span>
                                <span class="text-slate-500 dark:text-slate-400">Seguidores</span>
                            </div>
                            <div 
                                class="text-center md:text-left cursor-pointer hover:opacity-80 transition-opacity"
                                @click="openFollowModal('following')"
                            >
                                <span class="block font-bold text-lg text-slate-900 dark:text-white">
                                    {{ user?.stats?.following_count || 0 }}
                                </span>
                                <span class="text-slate-500 dark:text-slate-400">Siguiendo</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 mb-4 md:mb-2">
                         <button 
                            v-if="!isMe"
                            @click="toggleFollow"
                            class="px-8 py-2.5 rounded-full font-semibold transition-all shadow-md active:scale-95 flex items-center gap-2"
                            :class="user?.is_following 
                                ? 'bg-slate-200 dark:bg-zinc-700 text-slate-800 dark:text-white hover:bg-red-100 hover:text-red-500 hover:border-red-200 border border-transparent' 
                                : 'bg-black dark:bg-white text-white dark:text-black hover:bg-zinc-800 hover:dark:bg-slate-200'"
                         >
                            <i class="pi" :class="followLoading ? 'pi-spin pi-spinner' : (user?.is_following ? 'pi-check' : 'pi-user-plus')"></i>
                            {{ user?.is_following ? 'Siguiendo' : 'Seguir' }}
                         </button>

                         <template v-else>
                            <button class="px-6 py-2.5 rounded-full border border-slate-300 dark:border-zinc-700 font-medium hover:bg-slate-50 dark:hover:bg-zinc-800 transition-colors">
                                Editar Perfil
                            </button>
                            <button 
                                @click="logout"
                                class="px-6 py-2.5 rounded-full bg-red-50 dark:bg-red-950/30 border border-red-100 dark:border-red-900/50 text-red-600 dark:text-red-400 font-medium hover:bg-red-100 dark:hover:bg-red-950/50 transition-colors flex items-center gap-2"
                            >
                                <i class="pi pi-sign-out text-sm"></i>
                                Log Out
                            </button>
                         </template>
                    </div>
                </div>

                <!-- Bio -->
                <div v-if="user.bio" class="max-w-2xl mb-8 text-center md:text-left">
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed">{{ user.bio }}</p>
                </div>

                <!-- Tabs -->
                <div class="border-b border-slate-200 dark:border-zinc-800 mb-8">
                    <nav class="flex gap-8 justify-center md:justify-start">
                        <button class="pb-4 px-2 border-b-2 border-violet-600 text-violet-600 font-semibold transition-colors">
                            Outfits
                        </button>
                    </nav>
                </div>

                <!-- Barra de filtrado y ordenación local (sobre el array ya cargado) -->
                <div v-if="outfits && outfits.length > 0" class="flex flex-col sm:flex-row items-center gap-3 mb-6">
                    <!-- Buscador local sobre el array -->
                    <div class="relative w-full sm:w-64">
                        <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input
                            v-model="outfitSearchQuery"
                            type="text"
                            placeholder="Filtrar outfits..."
                            class="w-full pl-9 pr-4 py-2 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all"
                        />
                    </div>

                    <!-- Selector de ordenación local -->
                    <select
                        v-model="outfitSortBy"
                        class="px-3 py-2 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg text-sm text-slate-700 dark:text-white focus:outline-none focus:border-violet-500 cursor-pointer"
                    >
                        <option value="newest">Más recientes</option>
                        <option value="oldest">Más antiguos</option>
                        <option value="most_items">Más prendas</option>
                        <option value="alpha">Alfabético (A-Z)</option>
                    </select>

                    <!-- Contador de resultados filtrados -->
                    <span class="text-xs text-slate-400 ml-auto">
                        {{ filteredOutfits.length }} de {{ outfits.length }} outfits
                    </span>
                </div>

                <!-- Outfits Grid (usa el computed filteredOutfits en vez del array crudo) -->
                <div v-if="filteredOutfits.length > 0" class="grid grid-cols-2 lg:grid-cols-4 gap-6 animate-fade-in-up">
                    <div 
                        v-for="outfit in filteredOutfits" 
                        :key="outfit.id"
                        class="bg-white dark:bg-zinc-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all group border border-slate-100 dark:border-zinc-700/50"
                    >
                        <!-- Image -->
                        <div class="aspect-[4/5] bg-slate-100 relative overflow-hidden">
                            <img 
                                :src="outfit.thumbnail_url || '/images/placeholder-outfit.jpg'" 
                                referrerpolicy="no-referrer"
                                :alt="outfit.title"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                loading="lazy"
                            >
                            <!-- Overlay actions -->
                            <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                <router-link :to="{name: 'public.outfit.show', params: {id: outfit.id}}" class="p-3 bg-white rounded-full text-zinc-900 hover:scale-110 transition-transform shadow-lg" title="Ver Detalle">
                                    <i class="pi pi-eye"></i>
                                </router-link>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="font-bold text-slate-900 dark:text-white truncate mb-1">{{ outfit.title }}</h3>
                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                                <span>{{ new Date(outfit.created_at).toLocaleDateString() }}</span>
                                <span class="flex items-center gap-1">
                                    <i class="pi pi-clone"></i>
                                    {{ outfit.items_count || 0 }} items
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado vacío por filtro local -->
                <div v-else-if="outfits.length > 0 && filteredOutfits.length === 0" class="text-center py-16 bg-white dark:bg-zinc-800/50 rounded-2xl border border-slate-200 dark:border-zinc-700">
                    <i class="pi pi-filter-slash text-4xl text-slate-300 mb-3"></i>
                    <h3 class="text-lg font-semibold text-slate-800 dark:text-white mb-1">Sin coincidencias</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">No hay outfits que coincidan con "{{ outfitSearchQuery }}"</p>
                    <button @click="outfitSearchQuery = ''" class="mt-4 text-violet-600 font-semibold text-sm hover:underline">Limpiar filtro</button>
                </div>

                <!-- Estado vacío general -->
                <div v-else class="text-center py-20 bg-slate-50 dark:bg-zinc-800/50 rounded-2xl border-2 border-dashed border-slate-200 dark:border-zinc-700">
                    <div class="w-16 h-16 bg-slate-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-image text-slate-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">Sin outfits aún</h3>
                    <p class="text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                        Este usuario aún no ha publicado ningún outfit.
                    </p>
                </div>
            </div>
        </div>

        <!-- Followers/Following Modal -->
        <FollowersModal 
            v-if="user"
            v-model:visible="showFollowersModal" 
            :type="modalType" 
            :userId="user.id" 
        />
    </div>
</template>

