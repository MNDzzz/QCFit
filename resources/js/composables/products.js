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
        images: [],
        images_upload: [],
        remove_image_ids: []
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
        name: yup.string().required('Product name is required'),
        external_id: yup.string().nullable(),
        original_link: yup.string().url('Invalid URL format').nullable(),
    })

    const withLoading = async (fn) => {
        if (isLoading.value) throw new Error('Operation in progress')
        isLoading.value = true
        try {
            return await fn()
        } finally {
            isLoading.value = false
        }
    }

    const resetProduct = () => {
        product.value = { ...initialProduct, images: [], images_upload: [], remove_image_ids: [] }
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
            images: data.images ?? [],
            images_upload: [],
            remove_image_ids: []
        }
        clearErrors()
    }

    /**
     * Build FormData from product data to support file uploads.
     */
    const buildFormData = () => {
        const fd = new FormData()
        fd.append('name', product.value.name || '')
        if (product.value.external_id) fd.append('external_id', product.value.external_id)
        if (product.value.original_link) fd.append('original_link', product.value.original_link)
        if (product.value.category_id) fd.append('category_id', product.value.category_id)
        if (product.value.brand_id) fd.append('brand_id', product.value.brand_id)
        if (product.value.source_id) fd.append('source_id', product.value.source_id)

        // Append uploaded files
        if (product.value.images_upload?.length) {
            product.value.images_upload.forEach(file => {
                fd.append('images_upload[]', file)
            })
        }

        // Append IDs of images to remove (for update)
        if (product.value.remove_image_ids?.length) {
            product.value.remove_image_ids.forEach(id => {
                fd.append('remove_image_ids[]', id)
            })
        }

        return fd
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
            const fd = buildFormData()
            const response = await withLoading(() =>
                axios.post('/api/products', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            )
            toast.crud.created('Product')
            return response.data.data
        } catch (error) {
            handleRequestError(error, { fallbackMessage: 'Could not create the product' })
        }
    }

    const updateProduct = async () => {
        const { isValid } = await validate(productSchema, product.value)
        if (!isValid) return

        try {
            const fd = buildFormData()
            fd.append('_method', 'PUT') // Laravel needs this for PUT with FormData
            const response = await withLoading(() =>
                axios.post(`/api/products/${product.value.id}`, fd, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
            )
            toast.crud.updated('Product')
            return response.data.data
        } catch (error) {
            handleRequestError(error, { fallbackMessage: 'Could not update the product' })
        }
    }

    const deleteProduct = async (id) => {
        try {
            const response = await withLoading(() => axios.delete(`/api/products/${id}`))
            products.value = products.value.filter(p => p.id !== id)
            toast.crud.deleted('Product')
            return response
        } catch (error) {
            handleRequestError(error, { fallbackMessage: 'Could not delete the product' })
        }
    }

    /**
     * Toggle favorite (Wardrobe)
     * @param {number|string} id - Product ID
     */
    const toggleFavorite = async (id) => {
        try {
            const response = await axios.post(`/api/favorites/${id}`)
            const isFavorite = response.data.is_favorite
            
            if (isFavorite) {
                toast.success('Added to Favourites')
            } else {
                toast.info('Removed from Favourites')
            }
            
            return response.data
        } catch (error) {
            console.error('Error toggling favorite:', error)
            toast.error('Could not update Wardrobe')
            throw error
        }
    }

    /**
     * Public product search via /api/search.
     * Reusable in both Admin Panel and Web Client (CanvasSidebar).
     * @param {string} query - Search term
     * @param {Object} extraParams - Additional params (limit, etc.)
     * @returns {Array} - Array of found products
     */
    const searchProducts = async (query, extraParams = {}) => {
        if (!query || !query.trim()) {
            products.value = []
            return []
        }

        isLoading.value = true
        try {
            const response = await axios.get('/api/search', {
                params: { q: query, ...extraParams }
            })
            const data = response.data?.data || response.data || []
            products.value = data
            return data
        } catch (error) {
            console.error('Error searching products:', error)
            products.value = []
            return []
        } finally {
            isLoading.value = false
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
        searchProducts,
        toggleFavorite,
        createProduct,
        updateProduct,
        deleteProduct
    }
}
