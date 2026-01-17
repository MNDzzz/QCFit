<template>
    <div class="min-h-screen bg-stone-50 flex flex-col font-sans">
        <!-- Hero Section -->
        <div class="bg-slate-950 pt-32 pb-20 px-4 text-center relative overflow-hidden ring-1 ring-white/10 border-b border-white/5">
            <!-- Background Gradients -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-violet-600/20 blur-[120px] rounded-full pointer-events-none"></div>
            
            <div class="relative z-10 max-w-4xl mx-auto">
                <div class="flex justify-center mb-12 gap-8">
                     <!-- Floating Cards Animation (Mock) -->
                     <div class="hidden md:block w-48 h-64 bg-slate-900/80 backdrop-blur border border-slate-700 rounded-xl transform -rotate-6 shadow-2xl p-3">
                         <div class="bg-emerald-500/20 text-emerald-400 text-xs font-mono py-1 px-2 rounded w-fit mb-2">QC VERIFIED</div>
                         <div class="h-32 bg-slate-800 rounded-lg mb-3 flex items-center justify-center text-slate-600"><i class="pi pi-image text-2xl"></i></div>
                         <div class="h-2 w-20 bg-slate-800 rounded mb-2"></div>
                         <div class="h-2 w-12 bg-slate-800 rounded"></div>
                     </div>
                     <div class="hidden md:block w-48 h-64 bg-slate-900/80 backdrop-blur border border-slate-700 rounded-xl transform rotate-6 shadow-2xl p-3 translate-y-8">
                         <div class="bg-emerald-500/20 text-emerald-400 text-xs font-mono py-1 px-2 rounded w-fit mb-2">QC VERIFIED</div>
                         <div class="h-32 bg-slate-800 rounded-lg mb-3 flex items-center justify-center text-slate-600"><i class="pi pi-image text-2xl"></i></div>
                         <div class="h-2 w-20 bg-slate-800 rounded mb-2"></div>
                         <div class="h-2 w-12 bg-slate-800 rounded"></div>
                     </div>
                </div>

                <h1 class="text-5xl md:text-7xl font-display font-bold text-white mb-6 tracking-tight leading-tight">
                    The Ultimate Weidian <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 to-indigo-400">& Taobao QC Finder</span>
                </h1>
                <p class="text-lg md:text-xl text-slate-400 mb-12 max-w-2xl mx-auto font-light">
                    Search millions of real photos and build outfits. Stop buying blind.
                </p>
                
                <SmartSearch />
            </div>
        </div>

        <!-- Live Feed Ticker -->
        <LiveFeed />

        <!-- Brands Ticker -->
        <div class="py-12 border-t border-slate-200 bg-white">
            <div class="text-center mb-8">
                 <h3 class="text-sm font-bold text-slate-400 tracking-widest uppercase">Browse by Brand</h3>
            </div>
             <div class="flex justify-center gap-12 opacity-50 grayscale hover:grayscale-0 transition-all duration-500 flex-wrap px-4">
                 <!-- Just simple text placehhodlers resembling logos for speed -->
                 <span class="text-2xl font-black font-display italic">NIKE</span>
                 <span class="text-2xl font-black font-display">STUSSY</span>
                 <span class="text-2xl font-black font-display tracking-tighter">BALENCIAGA</span>
                 <span class="text-2xl font-black font-display">ARC'TERYX</span>
                 <span class="text-2xl font-black font-display font-serif">Carhartt</span>
             </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import SmartSearch from '@/components/SmartSearch.vue';
import LiveFeed from '@/components/LiveFeed.vue';
import Skeleton from 'primevue/skeleton';

// No need for logic here as LiveFeed component handles itself
</script>

<style scoped>
.animate-marquee {
    animation: marquee 20s linear infinite;
}
@keyframes marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
</style>
