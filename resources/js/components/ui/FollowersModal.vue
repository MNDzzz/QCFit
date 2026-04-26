<template>
    <Dialog 
        v-model:visible="isVisible" 
        :modal="true" 
        :header="type === 'followers' ? 'Seguidores' : 'Siguiendo'"
        class="w-full md:w-[450px]"
        :pt="{
            root: { class: 'bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl overflow-hidden' },
            header: { class: 'bg-white dark:bg-zinc-900 text-slate-900 dark:text-white border-b border-slate-100 dark:border-zinc-800 px-6 py-4' },
            content: { class: 'bg-white dark:bg-zinc-900 p-0' }
        }"
        @hide="onHide"
    >
        <!-- Loading State -->
        <div v-if="loading" class="p-6 flex flex-col gap-4">
            <div v-for="n in 5" :key="n" class="flex items-center gap-4">
                <Skeleton shape="circle" size="3rem" />
                <div class="flex-1">
                    <Skeleton width="10rem" height="1.2rem" class="mb-2" />
                    <Skeleton width="6rem" height="0.8rem" />
                </div>
            </div>
        </div>

        <!-- Users List -->
        <div v-else-if="users.length > 0" class="max-h-[60vh] overflow-y-auto">
            <div 
                v-for="user in users" 
                :key="user.id"
                class="flex items-center gap-4 p-4 border-b border-slate-50 dark:border-zinc-800/50 hover:bg-slate-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer"
                @click="goToProfile(user.id)"
            >
                <Avatar 
                    :image="user.avatar || '/images/placeholder-user.jpg'" 
                    size="large" 
                    shape="circle" 
                    class="shadow-sm border border-slate-200 dark:border-zinc-700"
                />
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-900 dark:text-white truncate">{{ user.name }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 truncate">@{{ user.alias || 'user' }}</p>
                </div>
                
                <!-- Action Button -->
                <div v-if="!user.is_me" @click.stop="toggleFollow(user)">
                    <button 
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition-all"
                        :class="user.is_following 
                            ? 'bg-slate-100 dark:bg-zinc-800 text-slate-800 dark:text-white hover:bg-red-50 hover:text-red-500 hover:border-red-100 border border-transparent' 
                            : 'bg-violet-600 text-white hover:bg-violet-700'"
                    >
                        {{ user.is_following ? 'Siguiendo' : 'Seguir' }}
                    </button>
                </div>
            </div>

            <!-- Load More -->
            <div v-if="hasMore" class="p-4 flex justify-center">
                <button 
                    @click="loadMore" 
                    class="text-violet-600 dark:text-violet-400 font-medium text-sm hover:underline flex items-center gap-2"
                    :disabled="loadingMore"
                >
                    <i class="pi pi-spinner pi-spin" v-if="loadingMore"></i>
                    {{ loadingMore ? 'Cargando...' : 'Cargar más' }}
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="p-8 text-center flex flex-col items-center">
            <div class="w-16 h-16 bg-slate-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mb-4">
                <i class="pi pi-users text-2xl text-slate-400"></i>
            </div>
            <h3 class="font-semibold text-slate-900 dark:text-white mb-1">Sin usuarios</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ type === 'followers' ? 'Aún no tiene seguidores.' : 'Aún no sigue a nadie.' }}
            </p>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import { authStore } from '@/store/auth';

const props = defineProps({
    visible: Boolean,
    userId: {
        type: [Number, String],
        required: true
    },
    type: {
        type: String, // 'followers' or 'following'
        default: 'followers'
    }
});

const emit = defineEmits(['update:visible']);

const router = useRouter();
const toast = useToast();
const auth = authStore();

const isVisible = ref(props.visible);
const users = ref([]);
const loading = ref(true);
const loadingMore = ref(false);
const currentPage = ref(1);
const hasMore = ref(false);

watch(() => props.visible, (newVal) => {
    isVisible.value = newVal;
    if (newVal) {
        users.value = [];
        currentPage.value = 1;
        fetchUsers();
    }
});

watch(isVisible, (newVal) => {
    emit('update:visible', newVal);
});

const fetchUsers = async () => {
    loading.value = currentPage.value === 1;
    loadingMore.value = currentPage.value > 1;
    
    try {
        const endpoint = `/api/public/user/${props.userId}/${props.type}?page=${currentPage.value}`;
        const response = await axios.get(endpoint);
        
        if (currentPage.value === 1) {
            users.value = response.data.data;
        } else {
            users.value = [...users.value, ...response.data.data];
        }
        
        hasMore.value = response.data.current_page < response.data.last_page;
    } catch (error) {
        console.error(`Error fetching ${props.type}:`, error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los usuarios.', life: 3000 });
    } finally {
        loading.value = false;
        loadingMore.value = false;
    }
};

const loadMore = () => {
    if (hasMore.value && !loadingMore.value) {
        currentPage.value++;
        fetchUsers();
    }
};

const goToProfile = (id) => {
    isVisible.value = false;
    setTimeout(() => {
        // En Vue 3, ir a la misma ruta actualiza la URL pero puede no recargar
        // si se usan parámetros. El watch() en Show.vue lo manejará.
        router.push(`/u/${id}`); 
    }, 150);
};

const toggleFollow = async (user) => {
    if (!auth.authenticated) {
        toast.add({ severity: 'info', summary: 'Acceso Restringido', detail: 'Debes iniciar sesión para seguir a usuarios.', life: 3000 });
        return;
    }

    const previousState = user.is_following;
    user.is_following = !previousState;

    try {
        await axios.post('/api/follow', { user_id: user.id });
    } catch (error) {
        user.is_following = previousState;
        console.error('Error following user:', error);
    }
};

const onHide = () => {
    users.value = [];
};
</script>
