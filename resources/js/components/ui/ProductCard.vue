<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
});

const isHovered = ref(false);

const qcImage = computed(() => {
    return props.product.images?.find(img => img.type === 'qc')?.url 
        || props.product.images?.[0]?.url 
        || '';
});

const marketingImage = computed(() => {
    // Try to find a non-qc image, or fallback to same image if only one exists
    return props.product.images?.find(img => img.type !== 'qc')?.url 
        || props.product.images?.[1]?.url 
        || qcImage.value;
});

const currentImage = computed(() => {
    return isHovered.value ? marketingImage.value : qcImage.value;
});

const price = computed(() => {
    // Mock price randomization if not present, for UI completeness
    return props.product.price || Math.floor(Math.random() * 400) + 150;
});
</script>

<template>
    <div 
        class="group bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300 relative"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
    >
        <!-- Image Container -->
        <div class="aspect-square bg-slate-100 relative overflow-hidden flex items-center justify-center p-4">
            <!-- Badge -->
            <div 
                class="absolute top-2 left-2 z-10"
                v-if="!isHovered"
            >
                <span class="bg-emerald-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm flex items-center gap-1">
                    <i class="pi pi-camera" style="font-size: 0.6rem"></i> QC
                </span>
            </div>

            <!-- Images -->
            <img 
                :src="qcImage" 
                class="absolute inset-0 w-full h-full object-cover transition-opacity duration-300"
                :class="{'opacity-0': isHovered && marketingImage !== qcImage, 'opacity-100': !isHovered || marketingImage === qcImage}"
                alt="QC"
            >
            <img 
                v-if="marketingImage !== qcImage"
                :src="marketingImage" 
                class="absolute inset-0 w-full h-full object-cover transition-opacity duration-300"
                 :class="{'opacity-100': isHovered, 'opacity-0': !isHovered}"
                 alt="Original"
            >

           <!-- Quick Add Button -->
            <div class="absolute bottom-3 right-3 z-20 translate-y-10 group-hover:translate-y-0 transition-transform duration-300">
                <button 
                    @click.stop="$emit('add-to-studio', product)"
                    class="h-10 w-10 bg-violet-600 hover:bg-violet-700 text-white rounded-full shadow-lg flex items-center justify-center transition-colors"
                >
                    <i class="pi pi-plus text-lg font-bold"></i>
                </button>
            </div>
        </div>

        <!-- Details -->
        <div class="p-3">
            <div class="flex justify-between items-start mb-1">
                <h3 class="font-sans font-bold text-slate-800 text-sm truncate pr-2 flex-1 leading-tight">{{ product.name }}</h3>
            </div>
            
            <p class="text-xs text-slate-400 mb-2">{{ product.brand || 'Unknown Brand' }}</p>

            <div class="flex justify-between items-end border-t border-slate-100 pt-2">
                <span class="font-mono font-bold text-slate-900 text-lg">¥{{ price }}</span>
                <i class="pi pi-shopping-bag text-slate-400" style="font-size: 0.8rem"></i> <!-- icon placeholder -->
                 <span class="text-[10px] text-slate-400 border border-slate-200 rounded px-1">Weidian</span>
            </div>
        </div>
    </div>
</template>
