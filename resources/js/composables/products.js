import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useCrud } from './useCrud'

export default function useProducts() {
    const toast = useToast()

    // 1. Extraemos la lógica base
    const {
        items: products,
        isLoading,
        errors,
        hasError,
        getError,
        clearErrors,
        handleRequestError,
        getItems,
        createItem,
        updateItem,
        deleteItem
    } = useCrud('/api/products', 'Producto', 'productos')

    // 2. Estado específico de productos
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

    // 3. Validación
    const productSchema = yup.object({
        name: yup.string().required('El nombre del producto es obligatorio'),
        external_id: yup.string().nullable(),
        original_link: yup.string().url('Formato de URL inválido').nullable(),
    })

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
     * Construye un objeto FormData para poder subir imágenes junto con los datos
     */
    const buildFormData = () => {
        const fd = new FormData()
        fd.append('name', product.value.name || '')
        if (product.value.external_id) fd.append('external_id', product.value.external_id)
        if (product.value.original_link) fd.append('original_link', product.value.original_link)
        if (product.value.category_id) fd.append('category_id', product.value.category_id)
        if (product.value.brand_id) fd.append('brand_id', product.value.brand_id)
        if (product.value.source_id) fd.append('source_id', product.value.source_id)

        if (product.value.images_upload?.length) {
            product.value.images_upload.forEach(file => {
                fd.append('images_upload[]', file)
            })
        }

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
        return getItems(params, defaultParams)
    }

    // Usamos el 'customRequestFn' de useCrud para pasarle nuestra petición FormData
    const createProduct = async () => {
        const fd = buildFormData()
        return createItem(productSchema, product.value, () => 
            axios.post('/api/products', fd, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
        )
    }

    // Para PUT con FormData en Laravel hay que enviar un POST y añadir _method = 'PUT'
    const updateProduct = async () => {
        const fd = buildFormData()
        fd.append('_method', 'PUT') 
        return updateItem(product.value.id, productSchema, product.value, () => 
            axios.post(`/api/products/${product.value.id}`, fd, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
        )
    }

    /**
     * Alternar favorito (Armario)
     */
    const toggleFavorite = async (id) => {
        try {
            const response = await axios.post(`/api/favorites/${id}`)
            const isFavorite = response.data.is_favorite
            
            if (isFavorite) {
                toast.success('Añadido a Favoritos')
            } else {
                toast.info('Eliminado de Favoritos')
            }
            
            return response.data
        } catch (error) {
            console.error('Error toggling favorite:', error)
            toast.error('No se pudo actualizar el Armario')
            throw error
        }
    }

    /**
     * Búsqueda pública de productos
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
        deleteProduct: deleteItem
    }
}
