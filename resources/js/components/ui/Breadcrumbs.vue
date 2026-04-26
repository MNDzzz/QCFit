<template>
    <div class="flex flex-wrap items-center text-xs text-slate-400 mb-2 space-x-2">
        <template v-for="(item, index) in items" :key="index">
            <router-link 
                v-if="item.to" 
                :to="item.to" 
                class="hover:text-violet-600 hover:underline transition-colors cursor-pointer font-medium"
            >
                {{ item.label }}
            </router-link>
            <span v-else class="text-slate-600 font-medium">{{ item.label }}</span>
            <i v-if="index < items.length - 1" class="pi pi-angle-right text-[10px] opacity-70 mx-1"></i>
        </template>
    </div>
</template>

<script setup>
defineProps({
    items: {
        type: Array,
        required: true,
        // Format: [{ label: 'Home', to: { name: 'public.home' } }, { label: 'Explore' }]
    }
});
</script>
