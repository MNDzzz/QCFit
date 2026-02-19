<script setup>
import { ref } from 'vue';
import { usePreferenceStore } from '@/store/preference';
import { authStore } from '@/store/auth';

const preferenceStore = usePreferenceStore();
const store = authStore(); // Renamed to 'store' to match typical usage or just use authStore() shorthand
const isOpen = ref(false);

const agents = [
    { label: 'CNFans', value: 'cnfans' },
    { label: 'CNFans', value: 'cnfans' },
    { label: 'Mulebuy', value: 'mulebuy' },
    { label: 'Hoobuy', value: 'hoobuy' },
];

function selectAgent(agent) {
    preferenceStore.setAgent(agent);
    isOpen.value = false;
}
</script>

<template>
    <nav class="fixed top-0 left-0 w-full z-50 bg-slate-950/80 backdrop-blur-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <router-link :to="{ name: 'public.home' }" class="flex items-center gap-2">
                        <span class="text-2xl font-display font-bold bg-clip-text text-transparent bg-gradient-to-r from-violet-400 to-indigo-400">
                            QCFit.
                        </span>
                    </router-link>
                </div>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center space-x-8">
                    <router-link :to="{ name: 'public.explore' }" class="text-stone-300 hover:text-white font-sans text-sm font-medium transition-colors">
                        Explore
                    </router-link>
                    <router-link :to="{ name: 'public.home' }" class="text-stone-300 hover:text-white font-sans text-sm font-medium transition-colors">
                        Brands
                    </router-link>
                    <router-link :to="{ name: 'app.canvas' }" class="text-stone-300 hover:text-white font-sans text-sm font-medium transition-colors">
                        Studio
                    </router-link>
                </div>

                <!-- Right Side Actions -->
                <div class="hidden md:flex items-center gap-4">
                    <!-- Agent Selector -->
                    <div class="relative group">
                        <button class="flex items-center gap-2 text-stone-300 hover:text-white text-sm font-medium bg-slate-800/50 px-3 py-1.5 rounded-full border border-slate-700 transition-colors">
                            <span class="text-xs text-slate-400">Agent:</span>
                            {{ preferenceStore.preferredAgent }}
                            <i class="pi pi-chevron-down text-xs"></i>
                        </button>
                        
                        <!-- Dropdown -->
                        <div class="hidden group-hover:block absolute right-0 mt-0 w-40 bg-slate-900 border border-slate-700 rounded-xl shadow-xl overflow-hidden py-1">
                            <button 
                                v-for="agent in agents" 
                                :key="agent.value"
                                @click="selectAgent(agent.value)"
                                class="block w-full text-left px-4 py-2 text-sm text-stone-300 hover:bg-slate-800 hover:text-white"
                                :class="{'text-violet-400': preferenceStore.preferredAgent === agent.value}"
                            >
                                {{ agent.label }}
                            </button>
                        </div>
                    </div>


                    <!-- Auth -->
                    <template v-if="!store.authenticated">
                        <router-link :to="{ name: 'auth.login' }" class="text-stone-300 hover:text-white text-sm font-medium">
                            Log in
                        </router-link>
                        <router-link :to="{ name: 'auth.register' }" class="bg-violet-600 hover:bg-violet-500 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg shadow-violet-500/20 transition-all transform hover:scale-105">
                            Join Free
                        </router-link>
                    </template>
                    <template v-else>
                         <router-link :to="{ name: 'public.profile', params: { id: store.user?.id || 0 } }" class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold border border-slate-700">
                                {{ store.user?.name ? store.user.name.charAt(0) : 'U' }}
                            </div>
                        </router-link>
                    </template>

                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button @click="isOpen = !isOpen" class="text-stone-300 hover:text-white">
                        <i class="pi pi-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div v-show="isOpen" class="md:hidden bg-slate-900 border-b border-slate-800">
             <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <router-link :to="{ name: 'public.explore' }" class="block px-3 py-2 rounded-md text-base font-medium text-stone-300 hover:text-white hover:bg-slate-800">Explore</router-link>
                <router-link :to="{ name: 'app.canvas' }" class="block px-3 py-2 rounded-md text-base font-medium text-stone-300 hover:text-white hover:bg-slate-800">Studio</router-link>
             </div>
        </div>
    </nav>
</template>
