<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { usePreferenceStore } from '@/store/preference';
import { useHead } from '@vueuse/head';
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue';

const route = useRoute();
const preferenceStore = usePreferenceStore();

const product = ref(null);
const loading = ref(true);
const activeImage = ref('');
const isQcMode = ref(true); // Toggle logic

const breadcrumbItems = computed(() => {
    if (!product.value) return [];
    return [
        { label: 'Home', to: { name: 'public.home' } },
        { label: 'Explore', to: { name: 'public.search' } },
        { label: product.value.category?.name || 'Category', to: product.value.category ? { name: 'public.search', query: { category: product.value.category.name } } : null },
        { label: product.value.brand?.name || (typeof product.value.brand === 'string' ? product.value.brand : 'Product') }
    ];
});

onMounted(async () => {
    fetchProduct();
});

async function fetchProduct() {
    loading.value = true;
    try {
        const id = route.params.id;
        const res = await axios.get(`/api/products/${id}`);
        product.value = res.data.data || res.data;
        updateActiveImage();

        // SEO Meta Tags
        useHead({
            title: computed(() => product.value ? `${product.value.name} - QCFit` : 'Product - QCFit'),
            meta: [
                {
                    name: 'description',
                    content: computed(() => `Find QC photos for ${product.value?.name}.`)
                },
                {
                    property: 'og:title',
                    content: computed(() => product.value?.name)
                },
                {
                    property: 'og:image',
                    content: computed(() => activeImage.value || '/images/og-default.jpg')
                }
            ]
        });
    } catch (e) {
        console.error("Error loading product", e);
    } finally {
        loading.value = false;
    }
}

// Watch toggle to update image
watch(isQcMode, () => {
    updateActiveImage();
});

function updateActiveImage() {
    if (!product.value || !product.value.images) return;
    
    // Logic: Find first image of selected type. If none, fallback to any.
    const type = isQcMode.value ? 'qc' : 'original'; // Assuming 'original' is the type for non-qc
    // Note: In DB seed, we might not have explicit 'original' type, usually just !qc. 
    // Adapting logic:
    let img = null;
    if (isQcMode.value) {
        img = product.value.images.find(i => i.type === 'qc');
    } else {
        img = product.value.images.find(i => i.type !== 'qc');
    }

    if (img) {
        activeImage.value = img.url;
    } else if (product.value.images.length > 0) {
        activeImage.value = product.value.images[0].url; // Fallback
    }
}

const currentImages = computed(() => {
    if (!product.value?.images) return [];
    // If QC mode, show QC images first, then others if needed, or filter strictly?
    // Let's filter strictly for the carousel to match the "Toggle" concept
    const typeTarget = isQcMode.value ? 'qc' : 'original';
    const images = product.value.images.filter(i => isQcMode.value ? i.type === 'qc' : i.type !== 'qc');
    return images.length > 0 ? images : product.value.images;
});

const handleW2C = () => {
    if (!product.value) return;
    const link = preferenceStore.getAffiliateLink(product.value);
    window.open(link, '_blank');
};

const agents = [
    { label: 'Pandabuy', value: 'pandabuy' },
    { label: 'CNFans', value: 'cnfans' },
    { label: 'Mulebuy', value: 'mulebuy' },
    { label: 'Hoobuy', value: 'hoobuy' },
];
</script>

<template>
    <div class="min-h-screen bg-stone-50 font-sans pb-20">
        <div v-if="loading" class="flex justify-center items-center h-screen">
            <i class="pi pi-spin pi-spinner text-4xl text-violet-600"></i>
        </div>

        <div v-else-if="product">
            <!-- Header Strip -->
            <div class="bg-white border-b border-slate-200 py-4 px-4 sticky top-16 z-30 shadow-sm">
                <div class="max-w-6xl mx-auto">
                    <Breadcrumbs :items="breadcrumbItems" />
                </div>
            </div>

            <div class="max-w-6xl mx-auto px-4 py-8">
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden flex flex-col lg:flex-row">
                
                <!-- Left: Gallery Section -->
                <div class="lg:w-[60%] bg-slate-100 relative p-8 flex flex-col items-center justify-center">
                    
                    <!-- Toggle Switch Overlay -->
                    <div class="absolute top-6 left-1/2 -translate-x-1/2 bg-slate-900/90 backdrop-blur rounded-full p-1 flex items-center shadow-2xl z-20 border border-slate-700">
                        <button 
                            @click="isQcMode = true"
                            class="px-4 py-1.5 rounded-full text-xs font-bold transition-all flex items-center gap-2"
                            :class="isQcMode ? 'bg-slate-700 text-emerald-400 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
                        >
                            <i class="pi pi-camera" v-if="isQcMode"></i> QC PHOTOS
                        </button>
                        <button 
                            @click="isQcMode = false"
                             class="px-4 py-1.5 rounded-full text-xs font-bold transition-all flex items-center gap-2"
                            :class="!isQcMode ? 'bg-amber-100 text-amber-700 shadow-sm' : 'text-slate-500 hover:text-slate-300'"
                        >
                            <i class="pi pi-image" v-if="!isQcMode"></i> ORIGINAL
                        </button>
                    </div>

                    <!-- Main Image -->
                    <div class="relative w-full aspect-[4/3] flex items-center justify-center mb-8">
                        <!-- Ruler (Mock for QC feel) -->
                        <div v-if="isQcMode" class="absolute left-0 top-0 bottom-0 w-8 bg-[url('https://i.imgur.com/vW7qM9r.png')] bg-contain opacity-50"></div> <!-- Placeholder ruler pattern optional -->
                        
                        <img 
                            :src="activeImage" 
                            referrerpolicy="no-referrer"
                            class="max-h-full max-w-full object-contain filter drop-shadow-2xl transition-all duration-500"
                            :class="{'mix-blend-multiply': isQcMode}" 
                        >
                    </div>

                    <!-- Thumbnails -->
                    <div class="flex gap-3 overflow-x-auto pb-2 w-full justify-center px-4">
                        <button 
                            v-for="img in currentImages" 
                            :key="img.id"
                            @click="activeImage = img.url"
                            class="w-16 h-16 rounded-lg border-2 overflow-hidden shrink-0 transition-all hover:scale-105"
                            :class="activeImage === img.url ? 'border-violet-600 ring-2 ring-violet-200' : 'border-slate-200 opacity-60 hover:opacity-100'"
                        >
                            <img :src="img.url" referrerpolicy="no-referrer" class="w-full h-full object-cover">
                        </button>
                    </div>
                </div>

                <!-- Right: Product Info -->
                <div class="lg:w-[40%] p-8 lg:p-12 flex flex-col bg-white">

                    <h2 class="text-xs font-bold text-slate-400 tracking-widest uppercase mb-1">{{ product.brand?.name || (typeof product.brand === 'string' ? product.brand : 'Marca desconocida') }}</h2>
                    <h1 class="text-3xl lg:text-4xl font-display font-bold text-slate-900 mb-4 leading-tight">{{ product.name }}</h1>

                    <div class="flex items-end gap-3 mb-8 border-b border-slate-100 pb-8">
                        <span class="text-4xl font-mono font-bold text-slate-900">¥{{ product.price || 260 }}</span>
                        <div class="text-xs text-slate-500 mb-1.5 flex flex-col">
                             <span class="font-bold flex items-center gap-1 uppercase">
                                <i class="pi pi-shopping-bag text-slate-400"></i> 
                                {{ product.source?.name || (typeof product.marketplace === 'string' ? product.marketplace : (product.marketplace?.name || 'Marketplace')) }}
                             </span>
                             <span>ID Externo: {{ product.external_id || product.source_id }}</span>
                        </div>
                    </div>

                    <!-- Meta Grid -->
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                            <div class="text-xs text-slate-400 mb-1 flex items-center gap-1"><i class="pi pi-balance-scale"></i> Avg. Weight</div>
                            <div class="font-bold text-slate-700">1250g</div>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                            <div class="text-xs text-slate-400 mb-1 flex items-center gap-1"><i class="pi pi-box"></i> Volume</div>
                            <div class="font-bold text-slate-700">35x25x15cm</div>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-100">
                            <div class="text-xs text-slate-400 mb-1 flex items-center gap-1"><i class="pi pi-shop"></i> Seller</div>
                            <div class="font-bold text-slate-700">WTG</div>
                        </div>
                        <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                            <div class="text-xs text-emerald-600 mb-1 flex items-center gap-1"><i class="pi pi-check-circle"></i> QC Check</div>
                            <div class="font-bold text-emerald-700">Passed</div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <button 
                        @click="handleW2C"
                        class="w-full py-4 bg-violet-600 hover:bg-violet-700 text-white rounded-xl font-bold text-lg shadow-xl shadow-violet-200 transition-all flex items-center justify-center gap-2 mb-3"
                    >
                        <span>Buy via {{ preferenceStore.preferredAgent }}</span> 
                        <i class="pi pi-external-link text-sm"></i>
                    </button>

                    <button 
                        @click="$router.push({name: 'public.studio'})"
                         class="w-full py-3 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl font-bold transition-all flex items-center justify-center gap-2 mb-8"
                    >
                        <i class="pi pi-plus"></i> ADD TO STUDIO
                    </button>

                    <!-- Agent Widget -->
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-5 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-100 rounded-full blur-2xl"></div>
                        <h3 class="font-bold text-amber-900 text-sm mb-3">Agent Preference Widget</h3>
                        <p class="text-xs text-amber-700/70 mb-3">Select your preferred agent for auto-links:</p>
                        <div class="flex flex-wrap gap-2">
                             <button 
                                v-for="agent in agents" 
                                :key="agent.value"
                                @click="preferenceStore.setAgent(agent.value)"
                                class="px-3 py-1 rounded-md text-[10px] font-bold border transition-colors uppercase"
                                :class="preferenceStore.preferredAgent === agent.value 
                                    ? 'bg-amber-100 border-amber-300 text-amber-800' 
                                    : 'bg-white border-amber-100 text-amber-600 hover:border-amber-300'"
                             >
                                 {{ agent.label }}
                             </button>
                        </div>
                    </div>

                </div>
            </div>
            
            <!-- Seen in outfits -->
            <div class="mt-12">
                 <h3 class="text-sm font-bold text-slate-400 tracking-widest uppercase mb-6">SEEN IN THESE OUTFITS</h3>
                 <div class="grid grid-cols-2 md:grid-cols-5 gap-4 opacity-50">
                      <!-- Mock Placeholders -->
                      <div class="aspect-[3/4] bg-slate-200 rounded-lg"></div>
                      <div class="aspect-[3/4] bg-slate-200 rounded-lg"></div>
                      <div class="aspect-[3/4] bg-slate-200 rounded-lg"></div>
                      <div class="aspect-[3/4] bg-slate-200 rounded-lg"></div>
                      <div class="aspect-[3/4] bg-slate-200 rounded-lg"></div>
                 </div>
            </div>
        </div>
        </div>
    </div>
</template>
