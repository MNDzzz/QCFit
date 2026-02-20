import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useSources() {
    const sources = ref([])
    const sourceList = ref([])
    const initialSource = { id: null, name: '', slug: '', logo_url: '', base_url: '' }
    const source = ref({ ...initialSource })
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

    const sourceSchema = yup.object({
        name: yup
            .string()
            .trim()
            .required('El nombre es obligatorio')
            .min(2, 'Debe tener al menos 2 caracteres'),
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

    const resetSource = () => {
        source.value = { ...initialSource }
        clearErrors()
    }

    const setSource = (data = {}) => {
        source.value = {
            id: data.id ?? null,
            name: data.name ?? '',
            slug: data.slug ?? '',
            logo_url: data.logo_url ?? '',
            base_url: data.base_url ?? ''
        }
        clearErrors()
    }

    const upsertSourceRecord = (record) => {
        if (!record?.id) return
        sources.value = [
            record,
            ...sources.value.filter(item => item.id !== record.id)
        ]
    }

    const getSources = async (params = {}) => {
        const defaultParams = {
            page: 1,
            search_global: '',
            order_column: 'created_at',
            order_direction: 'desc'
        }

        const query = new URLSearchParams({ ...defaultParams, ...params }).toString()
        const response = await axios.get(`/api/sources?${query}`)
        sources.value = response.data?.data ?? []
        return response
    }

    const getSourceList = async () => {
        try {
            const response = await axios.get('/api/source-list')
            sourceList.value = response.data?.data ?? response.data ?? []
            return response
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: 'No se pudo obtener la lista de marketplaces',
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    const createSource = async () => {
        const { isValid } = await validate(sourceSchema, source.value)
        if (!isValid) {
            toast.error('Error de validación', 'Revisa los campos resaltados.')
            throw new Error('Validación')
        }

        try {
            const response = await withLoading(() =>
                axios.post('/api/sources', source.value)
            )
            const data = response.data?.data ?? response.data
            toast.crud.created('Marketplace')
            return data
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: 'No se pudo crear el marketplace',
                onValidationError: () =>
                    toast.error('Error de validación', 'Revisa los campos resaltados.'),
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    const updateSource = async () => {
        const { isValid } = await validate(sourceSchema, source.value)
        if (!isValid) {
            toast.error('Error de validación', 'Revisa los campos resaltados.')
            throw new Error('Validación')
        }

        try {
            const response = await withLoading(() =>
                axios.put(`/api/sources/${source.value.id}`, source.value)
            )
            const data = response.data?.data ?? response.data
            toast.crud.updated('Marketplace')
            return data
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: 'No se pudo actualizar el marketplace',
                onValidationError: () =>
                    toast.error('Error de validación', 'Revisa los campos resaltados.'),
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    const deleteSource = async (id) => {
        try {
            const response = await withLoading(() => axios.delete(`/api/sources/${id}`))
            sources.value = sources.value.filter(item => item.id !== id)
            toast.crud.deleted('Marketplace')
            return response
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: 'No se pudo eliminar el marketplace',
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    return {
        sources,
        source,
        sourceList,
        isLoading,
        errors,
        hasError,
        getError,
        resetSource,
        setSource,
        upsertSourceRecord,
        getSources,
        getSourceList,
        createSource,
        updateSource,
        deleteSource
    }
}
