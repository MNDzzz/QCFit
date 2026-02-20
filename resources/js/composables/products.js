import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

export default function useProducts() {
    const products = ref([])
    const initialProduct = {
        id: null,
        name: '',
        external_id: '',
        original_link: '',
        category_id: null,
        brand_id: null,
        source_id: null,
        images: []
    }
    const product = ref({ ...initialProduct })
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

    const productSchema = yup.object({
        name: yup.string().required('El nombre es obligatorio'),
        external_id: yup.string().required('El ID de marketplace es obligatorio'),
        original_link: yup.string().url('URL inválida').required('El enlace original es necesario'),
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

    const resetProduct = () => {
        product.value = { ...initialProduct }
        clearErrors()
    }

    const setProduct = (data = {}) => {
        product.value = {
            id: data.id ?? null,
            name: data.name ?? '',
            external_id: data.external_id ?? '',
            original_link: data.original_link ?? '',
            category_id: data.category_id ?? null,
            brand_id: data.brand_id ?? null,
            source_id: data.source_id ?? null,
            images: data.images ?? []
        }
        clearErrors()
    }

    const getProducts = async (params = {}) => {
        const defaultParams = {
            page: 1,
            search_global: '',
            category_id: '',
            brand_id: '',
            order_column: 'created_at',
            order_direction: 'desc'
        }

        const query = new URLSearchParams({ ...defaultParams, ...params }).toString()
        const response = await axios.get(`/api/products?${query}`)
        products.value = response.data?.data ?? []
        return response.data
    }

    const createProduct = async () => {
        const { isValid } = await validate(productSchema, product.value)
        if (!isValid) return

        try {
            const response = await withLoading(() =>
                axios.post('/api/products', product.value)
            )
            toast.crud.created('Producto')
            return response.data.data
        } catch (error) {
            handleRequestError(error, { fallbackMessage: 'No se pudo crear el producto' })
        }
    }

    const updateProduct = async () => {
        const { isValid } = await validate(productSchema, product.value)
        if (!isValid) return

        try {
            const response = await withLoading(() =>
                axios.put(`/api/products/${product.value.id}`, product.value)
            )
            toast.crud.updated('Producto')
            return response.data.data
        } catch (error) {
            handleRequestError(error, { fallbackMessage: 'No se pudo actualizar el producto' })
        }
    }

    const deleteProduct = async (id) => {
        try {
            const response = await withLoading(() => axios.delete(`/api/products/${id}`))
            products.value = products.value.filter(p => p.id !== id)
            toast.crud.deleted('Producto')
            return response
        } catch (error) {
            handleRequestError(error, { fallbackMessage: 'No se pudo eliminar el producto' })
        }
    }

    return {
        products,
        product,
        isLoading,
        errors,
        hasError,
        getError,
        resetProduct,
        setProduct,
        getProducts,
        createProduct,
        updateProduct,
        deleteProduct
    }
}
