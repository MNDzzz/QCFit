import { ref, reactive, inject } from 'vue'
import { useRouter } from "vue-router";
import { AbilityBuilder, createMongoAbility } from '@casl/ability';
import { ABILITY_TOKEN } from '@casl/vue';
import { authStore } from "../store/auth";
import axios from 'axios';

let user = reactive({
    name: '',
    email: '',
})

export default function useAuth() {
    const processing = ref(false)
    const validationErrors = ref({})
    const router = useRouter()
    const swal = inject('$swal')
    const ability = inject(ABILITY_TOKEN)
    const auth = authStore()

    const loginForm = reactive({
        email: '',
        password: '',
        remember: false
    })

    const forgotForm = reactive({
        email: '',
    })

    const resetForm = reactive({
        email: '',
        token: '',
        password: '',
        password_confirmation: ''
    })

    const registerForm = reactive({
        name: '',
        email: '',
        password: '',
        password_confirmation: ''
    })

    // USO DE FUNCIONES JAVASCRIPT AVANZADAS: async/await y try/catch/finally en lugar de promesas anidadas
    const submitLogin = async () => {
        if (processing.value) return

        processing.value = true
        validationErrors.value = {}

        try {
            await axios.post('/login', loginForm)
            await auth.getUser()
            await loginUser()
            swal({
                icon: 'success',
                title: 'Login correcto',
                showConfirmButton: false,
                timer: 1500
            })
            await router.push({ name: 'public.home' })
        } catch (error) {
            // USO DE FUNCIONES JAVASCRIPT AVANZADAS: encadenamiento opcional
            if (error?.response?.data?.errors) {
                validationErrors.value = error.response.data.errors
            }
        } finally {
            processing.value = false
        }
    }

    const submitRegister = async () => {
        if (processing.value) return

        processing.value = true
        validationErrors.value = {}

        try {
            await axios.post('/register', registerForm)
            swal({
                icon: 'success',
                title: 'Registration successfully',
                showConfirmButton: false,
                timer: 1500
            })
            await router.push({ name: 'auth.login' })
        } catch (error) {
            if (error?.response?.data?.errors) {
                validationErrors.value = error.response.data.errors
            }
        } finally {
            processing.value = false
        }
    }

    const submitForgotPassword = async () => {
        if (processing.value) return

        processing.value = true
        validationErrors.value = {}

        try {
            await axios.post('/api/forget-password', forgotForm)
            swal({
                icon: 'success',
                title: 'We have emailed your password reset link! Please check your mail inbox.',
                showConfirmButton: false,
                timer: 1500
            })
        } catch (error) {
            if (error?.response?.data?.errors) {
                validationErrors.value = error.response.data.errors
            }
        } finally {
            processing.value = false
        }
    }

    const submitResetPassword = async () => {
        if (processing.value) return

        processing.value = true
        validationErrors.value = {}

        try {
            await axios.post('/api/reset-password', resetForm)
            swal({
                icon: 'success',
                title: 'Password successfully changed.',
                showConfirmButton: false,
                timer: 1500
            })
            await router.push({ name: 'auth.login' })
        } catch (error) {
            if (error?.response?.data?.errors) {
                validationErrors.value = error.response.data.errors
            }
        } finally {
            processing.value = false
        }
    }

    const loginUser = () => {
        console.log('GettingUserSignIn: loginUser')
        user = auth.user
        getAbilities()
    }

    const getUser = async () => {
        const auth = authStore();
        console.log('GettingUser')

        if (auth.authenticated) {
            await auth.getUser()
            await loginUser()
        }
    }

    const getUserSignIn = async () => {
        const auth = authStore();
        console.log('GettingUserSignIn')

        if (auth.authenticated) {
            await auth.getUserSignIn()
            await loginUser()
        }
    }

    const logout = async () => {
        if (processing.value) return

        processing.value = true

        try {
            await axios.post('/logout')
            user.name = ''
            user.email = ''
            auth.logout()
            router.push({ name: 'auth.login' })
        } catch (error) {
            // Manejo de errores omitido intencionadamente como en original
        } finally {
            processing.value = false
        }
    }

    const getAbilities = async () => {
        try {
            const response = await axios.get('/api/abilities')
            const permissions = response.data
            const { can, rules } = new AbilityBuilder(createMongoAbility)
            can(permissions)
            ability.update(rules)
        } catch (error) {
            console.error('Error fetching abilities', error)
        }
    }

    return {
        loginForm,
        registerForm,
        forgotForm,
        resetForm,
        validationErrors,
        processing,
        submitLogin,
        submitRegister,
        submitForgotPassword,
        submitResetPassword,
        user,
        getUser,
        getUserSignIn,
        logout,
        getAbilities
    }
}
