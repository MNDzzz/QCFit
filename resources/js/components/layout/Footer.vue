<template>
    <footer class="bg-slate-950 border-t border-slate-900 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="col-span-1 md:col-span-1">
                    <span class="text-2xl font-display font-bold bg-clip-text text-transparent bg-gradient-to-r from-violet-400 to-indigo-400 mb-4 block">
                        QCFit.
                    </span>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        The ultimate tool for finding quality check photos from Weidian, Taobao & 1688. 
                        Build your outfits with real photos before you buy.
                    </p>
                </div>
                
                <div>
                    <h4 class="text-white font-bold mb-4 font-display">Discover</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><router-link :to="{ name: 'public.explore' }" class="hover:text-violet-400 transition-colors">Trending Outfits</router-link></li>
                        <li><router-link :to="{ name: 'public.search', query: { q: 'shoes' } }" class="hover:text-violet-400 transition-colors">Popular Shoes</router-link></li>
                        <li><router-link :to="{ name: 'public.home' }" class="hover:text-violet-400 transition-colors">New Arrivals</router-link></li>
                    </ul>
                </div>

                 <div>
                    <h4 class="text-white font-bold mb-4 font-display">Studio</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><router-link :to="{ name: 'app.canvas' }" class="hover:text-violet-400 transition-colors">Create Outfit</router-link></li>
                        <li><router-link :to="{ name: 'public.explore' }" class="hover:text-violet-400 transition-colors">Remix Community</router-link></li>
                        <li><a href="#" class="hover:text-violet-400 transition-colors">My Wardrobe (WIP)</a></li>
                    </ul>
                </div>

                 <div>
                    <h4 class="text-white font-bold mb-4 font-display">Legal</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="#" class="hover:text-violet-400 transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-violet-400 transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-violet-400 transition-colors">Affiliate Disclosure</a></li>
                    </ul>
                </div>
            </div>

            <!-- SEO / Disclaimer Block -->
            <div class="border-t border-slate-900 pt-8 mt-8">
                 <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-slate-600 text-center md:text-left max-w-2xl">
                        QCFit is a search engine and visualization tool. We do not sell any products. 
                        All product images are sourced from third-party marketplaces. 
                        We may earn a commission when you use our partner links (Affiliate Disclosure).
                    </p>
                    <div class="text-slate-500 text-sm font-mono">
                        © 2026 QCFit Inc.
                    </div>
                 </div>
                 <div class="flex justify-center mt-8">
                     <i class="pi pi-star-fill text-slate-800 text-4xl animate-pulse"></i>
                 </div>
            </div>
        </div>
    </footer>
</template>
