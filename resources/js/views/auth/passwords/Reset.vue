<template>
    <!-- Página de Reset Password - Maquetada con Bootstrap 5 (scoped) -->
    <div class="bs-scope bs-auth-page min-vh-100 d-flex align-items-center justify-content-center py-5 px-3">
        <div style="max-width: 440px; width: 100%;">
            <!-- Logo y título -->
            <div class="text-center mb-4">
                <img src="/images/qcfit.svg" alt="QCFit Logo" class="mb-3" style="height: 48px; width: auto;">
                <h2 class="fw-bold mb-1">Reset Password</h2>
                <p class="text-muted">Enter your new password below</p>
            </div>

            <!-- Formulario con Card de Bootstrap -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 p-md-5">
                    <form @submit.prevent="submitResetPassword">
                        <!-- Email (readonly) -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">{{ $t('email') }}</label>
                            <input
                                id="email"
                                type="email"
                                class="form-control form-control-lg rounded-3"
                                v-model="resetForm.email"
                                readonly
                                disabled
                                :class="{ 'is-invalid': validationErrors?.email }"
                            />
                            <div v-if="validationErrors?.email" class="invalid-feedback" style="display:block;">
                                <div v-for="message in validationErrors.email" :key="message">
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">{{ $t('password') }}</label>
                            <input
                                id="password"
                                type="password"
                                class="form-control form-control-lg rounded-3"
                                v-model="resetForm.password"
                                placeholder="••••••••"
                                required
                                :class="{ 'is-invalid': validationErrors?.password }"
                            />
                            <div v-if="validationErrors?.password" class="invalid-feedback" style="display:block;">
                                <div v-for="message in validationErrors.password" :key="message">
                                    {{ message }}
                                </div>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">{{ $t('confirm_password') }}</label>
                            <input
                                id="password_confirmation"
                                type="password"
                                class="form-control form-control-lg rounded-3"
                                v-model="resetForm.password_confirmation"
                                placeholder="••••••••"
                                required
                                :class="{ 'is-invalid': validationErrors?.password_confirmation }"
                            />
                            <div v-if="validationErrors?.password_confirmation" class="invalid-feedback" style="display:block;">
                                <div v-for="message in validationErrors.password_confirmation" :key="message">
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
                            {{ $t('reset_password') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import useAuth from '@/composables/auth';
import { useRoute } from "vue-router";
import { onMounted } from "vue";

const route = useRoute();

onMounted(() => {
    resetForm.token = route.params.token;
    resetForm.email = route.query.email;
});

const { resetForm, validationErrors, processing, submitResetPassword } = useAuth();
</script>
