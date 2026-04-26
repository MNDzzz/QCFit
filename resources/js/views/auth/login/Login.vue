<template>
    <!-- Página de Login - Maquetada con Bootstrap 5 (scoped) -->
    <div class="bs-scope bs-auth-page min-vh-100 d-flex align-items-center justify-content-center py-5 px-3">
        <div style="max-width: 440px; width: 100%;">
            <!-- Logo y título -->
            <div class="text-center mb-4">
                <img src="/images/qcfit.svg" alt="QCFit Logo" class="mb-3" style="height: 48px; width: auto;">
                <h2 class="fw-bold mb-1">Welcome back</h2>
                <p class="text-muted">{{ $t('login') }} to continue</p>
            </div>

            <!-- Formulario con Card de Bootstrap -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form @submit.prevent="submitLogin">
                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">{{ $t('email') }}</label>
                            <input
                                id="email"
                                type="email"
                                class="form-control form-control-lg rounded-3"
                                v-model="loginForm.email"
                                placeholder="your@email.com"
                                :class="{ 'is-invalid': validationErrors?.email }"
                            />
                            <div v-if="validationErrors?.email" class="invalid-feedback" style="display:block;">
                                <div v-for="message in validationErrors.email" :key="message">
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">{{ $t('password') }}</label>
                            <input
                                id="password"
                                type="password"
                                class="form-control form-control-lg rounded-3"
                                v-model="loginForm.password"
                                placeholder="••••••••"
                                :class="{ 'is-invalid': validationErrors?.password }"
                            />
                            <div v-if="validationErrors?.password" class="invalid-feedback" style="display:block;">
                                <div v-for="message in validationErrors.password" :key="message">
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Remember me y Forgot password -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    v-model="loginForm.remember"
                                    id="remember"
                                />
                                <label class="form-check-label" for="remember">
                                    {{ $t('remember_me') }}
                                </label>
                            </div>
                            <router-link
                                :to="{ name: 'auth.forgot-password' }"
                                class="text-primary text-decoration-none fw-medium small"
                            >
                                {{ $t('forgot_password') }}
                            </router-link>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            class="btn btn-primary btn-lg w-100 rounded-3 fw-bold"
                            :disabled="processing"
                        >
                            <span v-if="processing" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            {{ $t('login') }}
                        </button>

                        <!-- Register link -->
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">
                                Don't have an account?
                                <router-link
                                    :to="{ name: 'auth.register' }"
                                    class="text-primary text-decoration-none fw-semibold"
                                >
                                    Sign up here
                                </router-link>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import useAuth from '@/composables/auth';

const { loginForm, validationErrors, processing, submitLogin } = useAuth();
</script>
