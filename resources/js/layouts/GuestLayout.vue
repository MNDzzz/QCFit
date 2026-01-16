<template>
    <div class="min-h-screen flex flex-col bg-slate-50 dark:bg-slate-950">
        <Navbar/>
        <main class="flex-grow pt-16">
            <router-view></router-view>
        </main>
        <Footer />
    </div>
</template>
<script setup>
import Navbar from '@/components/layout/Navbar.vue'
import Footer from '@/components/layout/Footer.vue'
</script>
