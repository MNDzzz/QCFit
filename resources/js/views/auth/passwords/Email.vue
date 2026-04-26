<template>
    <!-- Página de Recuperar Contraseña - Maquetada con Bootstrap 5 (scoped) -->
    <div class="bs-scope bs-auth-page min-vh-100 d-flex align-items-center justify-content-center py-5 px-3">
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
                            <div v-if="validationErrors?.email" class="invalid-feedback" style="display:block;">
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
                                ← Back to Login
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
