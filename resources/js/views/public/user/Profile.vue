<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { authStore } from '@/store/auth';

const route = useRoute();
const store = authStore();

const profile = ref(null);
const isFollowing = ref(false);
const loading = ref(true);

onMounted(async () => {
    fetchProfile();
});

async function fetchProfile() {
    try {
        const id = route.params.id;
        const res = await axios.get(`/api/public/user/${id}`);
        profile.value = res.data.user;
        isFollowing.value = res.data.is_following;
    } catch (e) {
        console.error("Error loading profile", e);
    } finally {
        loading.value = false;
    }
}

async function toggleFollow() {
    if (!store.user) {
        // Redirect to login or show alert
        alert("Inicia sesión para seguir a usuarios.");
        return;
    }

    try {
        const res = await axios.post('/api/follow', { user_id: profile.value.id });
        isFollowing.value = res.data.is_following;
        
        // Update stats locally
        if (isFollowing.value) {
            profile.value.followers_count++;
        } else {
            profile.value.followers_count--;
        }
    } catch (e) {
        console.error(e);
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <div v-if="loading" class="flex justify-center items-center h-screen">
            <i class="pi pi-spin pi-spinner text-4xl text-indigo-600"></i>
        </div>

        <div v-else-if="profile" class="max-w-5xl mx-auto py-8 px-4">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8 flex flex-col md:flex-row items-center gap-8 relative overflow-hidden">
                 <!-- Background decoration -->
                 <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-indigo-500 to-purple-600 opacity-20"></div>

                <div class="w-32 h-32 rounded-full border-4 border-white shadow-xl bg-gray-200 z-10 flex items-center justify-center text-4xl font-bold text-gray-400">
                    {{ profile.name.substring(0,1) }}
                </div>
                
                <div class="flex-1 text-center md:text-left z-10">
                    <h1 class="text-3xl font-extrabold text-gray-900">{{ profile.name }}</h1>
                    <p class="text-gray-500 font-medium mb-4">@{{ profile.alias || 'user' }}</p>
                    
                    <div class="flex justify-center md:justify-start gap-8 mb-6">
                        <div class="text-center">
                            <span class="block text-2xl font-bold text-gray-800">{{ profile.outfits_count }}</span>
                            <span class="text-xs text-gray-500 uppercase tracking-wide">Outfits</span>
                        </div>
                        <div class="text-center">
                            <span class="block text-2xl font-bold text-gray-800">{{ profile.followers_count }}</span>
                            <span class="text-xs text-gray-500 uppercase tracking-wide">Seguidores</span>
                        </div>
                        <div class="text-center">
                            <span class="block text-2xl font-bold text-gray-800">{{ profile.following_count }}</span>
                            <span class="text-xs text-gray-500 uppercase tracking-wide">Siguiendo</span>
                        </div>
                    </div>

                    <p class="text-gray-600 max-w-lg mx-auto md:mx-0">{{ profile.bio || 'Sin biografía.' }}</p>
                </div>

                <div class="z-10">
                    <button 
                        @click="toggleFollow"
                        class="px-8 py-3 rounded-full font-bold shadow-lg transition-all transform hover:-translate-y-1"
                        :class="isFollowing 
                            ? 'bg-gray-200 text-gray-800 hover:bg-gray-300' 
                            : 'bg-indigo-600 text-white hover:bg-indigo-700'"
                    >
                        {{ isFollowing ? 'Siguiendo' : 'Seguir' }}
                    </button>
                </div>
            </div>

            <!-- Outfits Grid -->
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                <i class="pi pi-objects-column"></i> Outfits Recientes
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div 
                    v-for="outfit in profile.outfits" 
                    :key="outfit.id"
                    class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all group"
                >
                    <div class="aspect-square bg-gray-100 flex flex-wrap relative">
                        <img 
                            v-if="outfit.products && outfit.products[0] && outfit.products[0].images"
                            :src="outfit.products[0].images[0].url" 
                            class="w-full h-full object-cover"
                        >
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Preview no disponible</div>

                         <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <button 
                                @click="$router.push({name: 'app.canvas', query: { outfit_id: outfit.id }})"
                                class="bg-white text-indigo-600 font-bold py-2 px-6 rounded-full"
                            >
                                Ver / Remix
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                         <h3 class="font-bold truncate">{{ outfit.name || 'Untitled Loop' }}</h3>
                         <p class="text-xs text-gray-400">{{ new Date(outfit.created_at).toLocaleDateString() }}</p>
                    </div>
                </div>
            </div>
             <div v-if="profile.outfits.length === 0" class="text-center py-10 text-gray-400">
                Este usuario aún no ha publicado outfits.
            </div>
        </div>
        
        <div v-else class="text-center py-20 text-gray-500">
            Usuario no encontrado.
        </div>
    </div>
</template>
