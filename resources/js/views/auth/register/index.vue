<template>
    <!-- Página de Register - Maquetada con Bootstrap 5 (scoped) -->
    <div class="bs-scope bs-auth-page min-vh-100 d-flex align-items-center justify-content-center py-5 px-3">
        <div style="max-width: 600px; width: 100%;">
            <!-- Logo y título -->
            <div class="text-center mb-4">
                <img src="/images/qcfit.svg" alt="QCFit Logo" class="mb-3" style="height: 48px; width: auto;">
                <h2 class="fw-bold mb-1">{{ $t('register') }}</h2>
                <p class="text-muted">Sign up to get started</p>
            </div>

            <!-- Formulario con Card de Bootstrap -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form @submit.prevent="submitRegister">
                        <!-- Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">{{ $t('name') }}</label>
                            <input
                                id="name"
                                type="text"
                                class="form-control form-control-lg rounded-3"
                                v-model="registerForm.name"
                                placeholder="Full name"
                                :class="{ 'is-invalid': validationErrors?.name }"
                            />
                            <div v-if="validationErrors?.name" class="invalid-feedback" style="display:block;">
                                {{ validationErrors.name[0] }}
                            </div>
                        </div>



                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">{{ $t('email') }}</label>
                            <input
                                id="email"
                                type="email"
                                class="form-control form-control-lg rounded-3"
                                v-model="registerForm.email"
                                placeholder="your@email.com"
                                :class="{ 'is-invalid': validationErrors?.email }"
                            />
                            <div v-if="validationErrors?.email" class="invalid-feedback" style="display:block;">
                                {{ validationErrors.email[0] }}
                            </div>
                        </div>

                        <!-- Password y Confirm Password (row / col-md-6) -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="password" class="form-label fw-semibold">{{ $t('password') }}</label>
                                <input
                                    id="password"
                                    type="password"
                                    class="form-control form-control-lg rounded-3"
                                    v-model="registerForm.password"
                                    placeholder="••••••••"
                                    :class="{ 'is-invalid': validationErrors?.password }"
                                />
                                <div v-if="validationErrors?.password" class="invalid-feedback" style="display:block;">
                                    {{ validationErrors.password[0] }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label fw-semibold">{{ $t('confirm_password') }}</label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    class="form-control form-control-lg rounded-3"
                                    v-model="registerForm.password_confirmation"
                                    placeholder="••••••••"
                                    :class="{ 'is-invalid': validationErrors?.password_confirmation }"
                                />
                                <div v-if="validationErrors?.password_confirmation" class="invalid-feedback" style="display:block;">
                                    {{ validationErrors.password_confirmation[0] }}
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
                            {{ $t('register') }}
                        </button>

                        <!-- Login link -->
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">
                                Already have an account?
                                <router-link
                                    :to="{ name: 'auth.login' }"
                                    class="text-primary text-decoration-none fw-semibold"
                                >
                                    Log in here
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

const { registerForm, validationErrors, processing, submitRegister } = useAuth();
</script>
