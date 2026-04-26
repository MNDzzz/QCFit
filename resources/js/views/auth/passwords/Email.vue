<template>
    <!-- Página de Recuperar Contraseña - Maquetada con Bootstrap 5 -->
    <div class="bs-auth-page min-vh-100 d-flex align-items-center justify-content-center py-5 px-3">
        <div style="max-width: 440px; width: 100%;">
            <!-- Logo y título -->
            <div class="text-center mb-4">
                <img src="/images/qcfit.svg" alt="QCFit Logo" class="mb-3" style="height: 48px; width: auto;">
                <h2 class="fw-bold mb-1">Forgot Password</h2>
                <p class="text-muted">Enter your email and we'll send you a reset link</p>
            </div>

            <!-- Formulario con Card de Bootstrap -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form @submit.prevent="submitForgotPassword">
                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">{{ $t('email') }}</label>
                            <input
                                id="email"
                                type="email"
                                class="form-control form-control-lg rounded-3"
                                v-model="forgotForm.email"
                                placeholder="your@email.com"
                                required
                                autofocus
                                autocomplete="username"
                                :class="{ 'is-invalid': validationErrors?.email }"
                            />
                            <div v-if="validationErrors?.email" class="invalid-feedback">
                                <div v-for="message in validationErrors.email" :key="message">
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            class="btn btn-primary btn-lg w-100 rounded-3 fw-bold"
                            :disabled="processing"
                        >
                            <span v-if="processing" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            {{ $t('send_password_reset_link') }}
                        </button>

                        <!-- Back to login -->
                        <div class="text-center mt-4">
                            <router-link
                                :to="{ name: 'auth.login' }"
                                class="text-muted text-decoration-none"
                            >
                                <i class="pi pi-arrow-left me-1" style="font-size: 0.75rem;"></i>
                                Back to Login
                            </router-link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import useAuth from '@/composables/auth'

const { forgotForm, validationErrors, processing, submitForgotPassword } = useAuth();
</script>

<style scoped>
/* Personalización del btn-primary para el estilo violeta */
.btn-primary {
    background-color: #7c3aed;
    border-color: #7c3aed;
}
.btn-primary:hover {
    background-color: #6d28d9;
    border-color: #6d28d9;
}
.btn-primary:focus {
    box-shadow: 0 0 0 0.25rem rgba(124, 58, 237, 0.5);
}

/* Form control focus violeta */
.form-control:focus {
    border-color: #7c3aed;
    box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.25);
}
</style>
