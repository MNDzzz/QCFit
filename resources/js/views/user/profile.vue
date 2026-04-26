<template>
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-8">Ajustes de Perfil</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar / Avatar -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-zinc-700 text-center">
                    <div class="relative group mx-auto w-32 h-32 mb-4">
                        <img 
                            :src="profile.avatar || '/images/placeholder-user.jpg'" 
                            class="w-full h-full object-cover rounded-full border-4 border-slate-50 dark:border-zinc-900 shadow-md"
                            alt="Avatar"
                        />
                        <div class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-full opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <i class="pi pi-camera text-white text-xl"></i>
                        </div>
                        <!-- Hidden File Input -->
                        <input 
                            type="file" 
                            @change="handleAvatarUpload" 
                            class="absolute inset-0 opacity-0 cursor-pointer"
                            accept="image/*"
                        />
                    </div>
                    <h2 class="font-bold text-lg text-slate-900 dark:text-white">{{ profile.name }}</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">@{{ profile.alias || 'user' }}</p>
                </div>

                <div class="bg-violet-50 dark:bg-violet-900/20 rounded-2xl p-6 border border-violet-100 dark:border-violet-900/50">
                    <h3 class="font-bold text-violet-900 dark:text-violet-400 text-sm mb-2 uppercase tracking-wider">Tu Identidad</h3>
                    <p class="text-sm text-violet-700 dark:text-violet-300 leading-relaxed">
                        Tu alias y bio son públicos. Los demás usuarios verán esta información cuando visiten tu perfil.
                    </p>
                </div>
            </div>

            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <form @submit.prevent="saveChanges" class="bg-white dark:bg-zinc-800 rounded-2xl p-8 shadow-sm border border-slate-200 dark:border-zinc-700 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Nombre Completo</label>
                            <input 
                                v-model="profile.name" 
                                type="text"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent outline-none transition-all dark:text-white"
                                :class="{ 'border-red-500': hasError('name') }"
                            />
                            <p v-if="hasError('name')" class="text-xs text-red-500 mt-1">{{ getError('name') }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Alias (@)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">@</span>
                                <input 
                                    v-model="profile.alias" 
                                    type="text"
                                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent outline-none transition-all dark:text-white"
                                    placeholder="tunombre"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Correo Electrónico</label>
                        <input 
                            v-model="profile.email" 
                            type="email"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent outline-none transition-all dark:text-white"
                            :class="{ 'border-red-500': hasError('email') }"
                        />
                         <p v-if="hasError('email')" class="text-xs text-red-500 mt-1">{{ getError('email') }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">Biografía</label>
                        <textarea 
                            v-model="profile.bio" 
                            rows="4"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-zinc-900 border border-slate-200 dark:border-zinc-700 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent outline-none transition-all dark:text-white resize-none"
                            placeholder="Cuéntanos algo sobre ti..."
                        ></textarea>
                    </div>

                    <div class="pt-4 flex justify-end gap-4">
                        <button 
                            type="button" 
                            @click="getProfile"
                            class="px-6 py-2.5 text-slate-600 dark:text-slate-400 font-semibold hover:bg-slate-100 dark:hover:bg-zinc-700 rounded-xl transition-colors"
                        >
                            Descartar
                        </button>
                        <button 
                            type="submit"
                            :disabled="isLoading"
                            class="px-8 py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-bold rounded-xl shadow-lg shadow-violet-500/30 transition-all active:scale-95 disabled:opacity-50 flex items-center gap-2"
                        >
                            <i v-if="isLoading" class="pi pi-spin pi-spinner text-sm"></i>
                            {{ isLoading ? 'Guardando...' : 'Guardar Cambios' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import useProfile from "@/composables/profile";
import axios from 'axios';
import { useToast } from "@/composables/useToast";

const { profile, getProfile, updateProfile, isLoading, hasError, getError } = useProfile();
const toast = useToast();

onMounted(() => {
    getProfile();
});

async function saveChanges() {
    try {
        await updateProfile();
    } catch (e) {
        // Errores ya manejados en el composable
    }
}

async function handleAvatarUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('image', file);

    try {
        const response = await axios.post('/api/users/updateimg', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        // Actualizar el avatar en el perfil local y en el store
        profile.value.avatar = response.data.user.avatar;
        toast.success('Éxito', 'Avatar actualizado correctamente');
    } catch (e) {
        toast.error('Error', 'No se pudo subir la imagen');
    }
}
</script>
