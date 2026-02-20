import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useBrands() {
    const brands = ref([])
    const brandList = ref([])
    const initialBrand = { id: null, name: '', slug: '', logo_url: '', description: '' }
    const brand = ref({ ...initialBrand })
    const isLoading = ref(false)
    const toast = useToast()

    const {
        errors,
        validate,
        handleRequestError,
        clearErrors,
        hasError,
        getError
    } = useValidation()

    const brandSchema = yup.object({
        name: yup
            .string()
            .trim()
            .required('El nombre de la marca es obligatorio')
            .min(1, 'Debe tener al menos 1 carácter'),
        slug: yup
            .string()
            .trim()
            .required('El slug es obligatorio')
            .matches(/^[a-z0-9-]+$/, 'Formato de slug inválido'),
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

    const resetBrand = () => {
        brand.value = { ...initialBrand }
        clearErrors()
    }

    const setBrand = (data = {}) => {
        brand.value = {
            id: data.id ?? null,
            name: data.name ?? '',
            slug: data.slug ?? '',
            logo_url: data.logo_url ?? '',
            description: data.description ?? ''
        }
        clearErrors()
    }

    const upsertBrandRecord = (record) => {
        if (!record?.id) return
        brands.value = [
            record,
            ...brands.value.filter(item => item.id !== record.id)
        ]
    }

    const getBrands = async (params = {}) => {
        const defaultParams = {
            page: 1,
            search_global: '',
            order_column: 'created_at',
            order_direction: 'desc'
        }

        const query = new URLSearchParams({ ...defaultParams, ...params }).toString()
        const response = await axios.get(`/api/brands?${query}`)
        brands.value = response.data?.data ?? []
        return response
    }

    const getBrandList = async () => {
        try {
            const response = await axios.get('/api/brand-list')
            brandList.value = response.data?.data ?? response.data ?? []
            return response
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: 'No se pudo obtener la lista de marcas',
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    const createBrand = async () => {
        const { isValid } = await validate(brandSchema, brand.value)
        if (!isValid) {
            toast.error('Error de validación', 'Revisa los campos resaltados.')
            throw new Error('Validación')
        }

        try {
            const response = await withLoading(() =>
                axios.post('/api/brands', brand.value)
            )
            const data = response.data?.data ?? response.data
            toast.crud.created('Marca')
            return data
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: 'No se pudo crear la marca',
                onValidationError: () =>
                    toast.error('Error de validación', 'Revisa los campos resaltados.'),
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    const updateBrand = async () => {
        const { isValid } = await validate(brandSchema, brand.value)
        if (!isValid) {
            toast.error('Error de validación', 'Revisa los campos resaltados.')
            throw new Error('Validación')
        }

        try {
            const response = await withLoading(() =>
                axios.put(`/api/brands/${brand.value.id}`, brand.value)
            )
            const data = response.data?.data ?? response.data
            toast.crud.updated('Marca')
            return data
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: 'No se pudo actualizar la marca',
                onValidationError: () =>
                    toast.error('Error de validación', 'Revisa los campos resaltados.'),
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    const deleteBrand = async (id) => {
        try {
            const response = await withLoading(() => axios.delete(`/api/brands/${id}`))
            brands.value = brands.value.filter(item => item.id !== id)
            toast.crud.deleted('Marca')
            return response
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: 'No se pudo eliminar la marca',
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    return {
        brands,
        brand,
        brandList,
        isLoading,
        errors,
        hasError,
        getError,
        resetBrand,
        setBrand,
        upsertBrandRecord,
        getBrands,
        getBrandList,
        createBrand,
        updateBrand,
        deleteBrand
    }
}
