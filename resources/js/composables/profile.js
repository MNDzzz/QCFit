import { inject, ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'
import { authStore } from '../store/auth'

export default function useProfile() {
  const initialProfile = {
    name: '',
    email: '',
    alias: '',
    bio: '',
    avatar: null
  }

  const profile = ref({ ...initialProfile })
  const isLoading = ref(false)
  const swal = inject('$swal')
  const toast = useToast()
  const auth = authStore()
  const { errors, validate, clearErrors, hasError, getError, setFieldError } = useValidation()

  const profileSchema = yup.object({
    name: yup.string().trim().required('El nombre es obligatorio').min(3, 'Debe tener al menos 3 caracteres'),
    alias: yup.string().trim().nullable(),
    bio: yup.string().trim().nullable().max(500, 'La bio no puede exceder los 500 caracteres'),
    email: yup.string().trim().email('Email inválido').required('El email es obligatorio')
  })

  const withLoading = async (fn) => {
    if (isLoading.value) throw new Error('Operación en curso')
    isLoading.value = true
    try {
      return await fn()
    } finally {
      isLoading.value = false
    }
  }

  const resetProfile = () => {
    profile.value = { ...initialProfile }
    clearErrors()
  }

  const setProfile = (data = {}) => {
    profile.value = {
      name: data.name ?? '',
      email: data.email ?? '',
      alias: data.alias ?? '',
      bio: data.bio ?? '',
      avatar: data.avatar ?? null
    }
    clearErrors()
  }

  const getProfile = async () => {
    if (auth.user) {
        setProfile(auth.user)
    }
  }

  const updateProfile = async () => {
    const { isValid } = await validate(profileSchema, profile.value)
    if (!isValid) {
      toast.error('Error de validación', 'Revisa los campos resaltados.')
      throw new Error('Validación')
    }

    try {
      const response = await withLoading(() => axios.put(`/api/user`, {
        name: profile.value.name,
        alias: profile.value.alias,
        email: profile.value.email,
        bio: profile.value.bio
      }))
      
      const data = response.data?.data ?? response.data
      
      // Actualizar el store global con los nuevos datos
      auth.user = data
      
      toast.crud.updated('Perfil')
      return data
    } catch (error) {
      toast.error('Error', 'No se pudo actualizar el perfil')
      throw error
    }
  }  

  return {
    profile,
    errors,
    isLoading,
    hasError,
    getError,
    clearErrors,
    resetProfile,
    setProfile,
    getProfile,
    updateProfile
  }
}
