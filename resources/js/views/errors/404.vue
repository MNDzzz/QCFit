<template>
    <!-- Página 404 - Maquetada con Bootstrap 5 (scoped) -->
    <div class="bs-scope bs-error-page min-vh-100 d-flex align-items-center justify-content-center">
        <div class="text-center px-4">
            <!-- Número 404 grande -->
            <div class="mb-4">
                <span class="display-1 fw-bold" style="color: #7c3aed; font-size: 8rem; line-height: 1;">404</span>
            </div>

            <h2 class="fw-bold fs-3 mb-2">
                <span class="text-danger">Oops!</span> Page not found.
            </h2>
            <p class="lead text-muted mb-4" style="max-width: 400px; margin: 0 auto;">
                The page you're looking for doesn't exist or has been moved.
            </p>

            <router-link to="/" class="btn btn-primary btn-lg rounded-3 fw-bold px-5">
                Go Home
            </router-link>
        </div>
    </div>
</template>

<script setup>
// Página 404 - Componente estático
</script>
