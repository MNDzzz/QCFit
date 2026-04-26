<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { usePreferenceStore } from '@/store/preference';
import { authStore } from '@/store/auth';
import useAuth from '@/composables/auth';
import { useLayout } from '@/composables/layout';

const router = useRouter();
const { toggleDarkMode, isDarkTheme } = useLayout();
const navSearchQuery = ref('');
const isNavSearchActive = ref(false);
const navSearchInput = ref(null);

const toggleNavSearch = () => {
    isNavSearchActive.value = !isNavSearchActive.value;
    if (isNavSearchActive.value) {
        setTimeout(() => navSearchInput.value?.focus(), 100);
    }
};

const submitNavSearch = () => {
    if (!navSearchQuery.value.trim()) return;
    router.push({ name: 'public.search', query: { q: navSearchQuery.value.trim() } });
    navSearchQuery.value = '';
    isNavSearchActive.value = false;
};

const preferenceStore = usePreferenceStore();
const store = authStore();
const { logout } = useAuth();
const isOpen = ref(false);

const agents = [
    { label: 'Pandabuy', value: 'pandabuy' },
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
                        <img src="/images/qcfit.svg" alt="QCFit Logo" class="h-9 w-auto" />
                    </router-link>
                </div>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center space-x-8" v-if="!isNavSearchActive">
                    <router-link :to="{ name: 'public.explore' }" class="text-stone-300 hover:text-white font-sans text-sm font-medium transition-colors">
                        Explore
                    </router-link>
                    <router-link :to="{ name: 'public.brands' }" class="text-stone-300 hover:text-white font-sans text-sm font-medium transition-colors">
                        Brands
                    </router-link>
                    <router-link :to="{ name: 'public.studio' }" class="text-stone-300 hover:text-white font-sans text-sm font-medium transition-colors">
                        Studio
                    </router-link>
                </div>

                <!-- Right Side Actions -->
                <div class="hidden md:flex items-center gap-4" v-if="!isNavSearchActive">
                    <!-- Search Icon Button -->
                    <button @click="toggleNavSearch" class="text-stone-300 hover:text-white transition-colors focus:outline-none">
                        <i class="pi pi-search text-lg"></i>
                    </button>

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

                    <!-- Dark Mode Toggle -->
                    <button 
                        @click="toggleDarkMode" 
                        class="text-stone-300 hover:text-white transition-colors focus:outline-none w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-800"
                        :title="isDarkTheme ? 'Light Mode' : 'Dark Mode'"
                    >
                        <i class="pi" :class="isDarkTheme ? 'pi-sun' : 'pi-moon'"></i>
                    </button>

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
                         <!-- Profile Dropdown -->
                         <div class="relative group">
                            <button class="flex items-center gap-2 focus:outline-none">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold border border-slate-700 hover:ring-2 hover:ring-violet-500 transition-all">
                                    {{ store.user?.name ? store.user.name.charAt(0) : 'U' }}
                                </div>
                                <i class="pi pi-chevron-down text-[10px] text-slate-400"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="hidden group-hover:block absolute right-0 mt-0 w-48 bg-slate-900 border border-slate-700 rounded-xl shadow-xl overflow-hidden py-1 z-50">
                                <router-link 
                                    :to="{ name: 'public.profile', params: { id: store.user?.id || 0 } }" 
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-stone-300 hover:bg-slate-800 hover:text-white transition-colors"
                                >
                                    <i class="pi pi-user text-xs"></i>
                                    Profile
                                </router-link>

                                <router-link 
                                    v-if="store.is('admin')"
                                    :to="{ name: 'admin.index' }" 
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-stone-300 hover:bg-slate-800 hover:text-white transition-colors"
                                >
                                    <i class="pi pi-cog text-xs"></i>
                                    Admin
                                </router-link>

                                <div class="h-px bg-slate-800 my-1 mx-2"></div>

                                <button 
                                    @click="logout"
                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors text-left"
                                >
                                    <i class="pi pi-sign-out text-xs"></i>
                                    Log Out
                                </button>
                            </div>
                        </div>
                    </template>

                </div>

                <!-- Expanded Search Bar (Visible when isNavSearchActive is true) -->
                <div v-if="isNavSearchActive" class="flex-1 flex items-center px-4 md:px-8 ml-4">
                     <form @submit.prevent="submitNavSearch" class="relative w-full flex items-center">
                         <i class="pi pi-search absolute left-4 text-slate-400 text-lg"></i>
                         <input 
                             v-model="navSearchQuery"
                             type="text" 
                             ref="navSearchInput"
                             placeholder="Search products, brands, links..." 
                             class="w-full bg-slate-800/50 text-white text-base font-medium border border-slate-700 rounded-full pl-12 pr-12 py-2.5 focus:outline-none focus:border-violet-500 focus:ring-1 focus:ring-violet-500 transition-all placeholder-slate-500"
                         >
                         <button type="button" @click="toggleNavSearch" class="absolute right-4 text-slate-400 hover:text-white transition-colors focus:outline-none">
                             <i class="pi pi-times text-lg"></i>
                         </button>
                     </form>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center gap-4" v-if="!isNavSearchActive">
                    <button @click="toggleNavSearch" class="text-stone-300 hover:text-white transition-colors focus:outline-none">
                        <i class="pi pi-search text-xl"></i>
                    </button>
                    <button @click="isOpen = !isOpen" class="text-stone-300 hover:text-white">
                        <i class="pi pi-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Menú Móvil -->
        <div v-show="isOpen" class="md:hidden bg-slate-900 border-b border-slate-800">
             <div class="px-4 pt-3 pb-4 space-y-1">
                <!-- Navegación principal -->
                <router-link @click="isOpen = false" :to="{ name: 'public.explore' }" class="block px-3 py-2.5 rounded-lg text-base font-medium text-stone-300 hover:text-white hover:bg-slate-800 transition-colors">Explore</router-link>
                <router-link @click="isOpen = false" :to="{ name: 'public.brands' }" class="block px-3 py-2.5 rounded-lg text-base font-medium text-stone-300 hover:text-white hover:bg-slate-800 transition-colors">Brands</router-link>
                <router-link @click="isOpen = false" :to="{ name: 'public.studio' }" class="block px-3 py-2.5 rounded-lg text-base font-medium text-stone-300 hover:text-white hover:bg-slate-800 transition-colors">Studio</router-link>

                <!-- Separador -->
                <div class="h-px bg-slate-800 my-2"></div>

                <!-- Agent Selector Móvil -->
                <div class="px-3 py-2">
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-2 font-semibold">Agent</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="agent in agents"
                            :key="agent.value"
                            @click="selectAgent(agent.value)"
                            class="px-3 py-1.5 rounded-full text-sm font-medium border transition-all"
                            :class="preferenceStore.preferredAgent === agent.value
                                ? 'bg-violet-600 text-white border-violet-500'
                                : 'text-stone-400 border-slate-700 hover:border-slate-500 hover:text-white'"
                        >
                            {{ agent.label }}
                        </button>
                    </div>
                </div>

                <!-- Dark Mode Toggle Móvil -->
                <button
                    @click="toggleDarkMode"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-base font-medium text-stone-300 hover:text-white hover:bg-slate-800 transition-colors"
                >
                    <i class="pi" :class="isDarkTheme ? 'pi-sun' : 'pi-moon'"></i>
                    {{ isDarkTheme ? 'Light Mode' : 'Dark Mode' }}
                </button>

                <!-- Separador -->
                <div class="h-px bg-slate-800 my-2"></div>

                <!-- Auth Móvil -->
                <template v-if="!store.authenticated">
                    <router-link
                        @click="isOpen = false"
                        :to="{ name: 'auth.login' }"
                        class="block px-3 py-2.5 rounded-lg text-base font-medium text-stone-300 hover:text-white hover:bg-slate-800 transition-colors"
                    >
                        <i class="pi pi-sign-in mr-2 text-sm"></i> Log in
                    </router-link>
                    <router-link
                        @click="isOpen = false"
                        :to="{ name: 'auth.register' }"
                        class="block mx-3 mt-2 text-center bg-violet-600 hover:bg-violet-500 text-white px-4 py-2.5 rounded-full text-sm font-bold shadow-lg shadow-violet-500/20 transition-all"
                    >
                        Join Free
                    </router-link>
                </template>
                <template v-else>
                    <router-link
                        @click="isOpen = false"
                        :to="{ name: 'public.profile', params: { id: store.user?.id || 0 } }"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-base font-medium text-stone-300 hover:text-white hover:bg-slate-800 transition-colors"
                    >
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ store.user?.name ? store.user.name.charAt(0) : 'U' }}
                        </div>
                        Profile
                    </router-link>
                    <router-link
                        v-if="store.is('admin')"
                        @click="isOpen = false"
                        :to="{ name: 'admin.index' }"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-base font-medium text-stone-300 hover:text-white hover:bg-slate-800 transition-colors"
                    >
                        <i class="pi pi-cog text-sm"></i> Admin
                    </router-link>
                    <button
                        @click="logout; isOpen = false"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-base font-medium text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors text-left"
                    >
                        <i class="pi pi-sign-out text-sm"></i> Log Out
                    </button>
                </template>
             </div>
        </div>
    </nav>
</template>
