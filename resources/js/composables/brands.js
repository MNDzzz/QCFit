import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useCrud } from './useCrud'

/**
 * Composable específico para gestionar Marcas.
 * Utilizamos el composable genérico useCrud para las operaciones básicas
 * y añadimos funciones específicas como 'getBrandProducts'.
 */
export default function useBrands() {
    const toast = useToast()

    // 1. Extraemos la lógica CRUD genérica
    const {
        items: brands,
        itemList: brandList,
        isLoading,
        errors,
        hasError,
        getError,
        clearErrors,
        handleRequestError,
        withLoading,
        upsertRecord,
        getItems,
        getItemList,
        createItem,
        updateItem,
        deleteItem
    } = useCrud('/api/brands', 'Marca', 'marcas')

    // 2. Estado local y configuración inicial
    const initialBrand = { id: null, name: '', slug: '', logo_url: '', description: '' }
    const brand = ref({ ...initialBrand })

    // 3. Reglas de validación exclusivas para Marcas
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

    // 4. Funciones auxiliares para el formulario
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

    const upsertBrandRecord = (record) => upsertRecord(record)

    // 5. Adaptadores para las funciones genéricas de obtener, crear y actualizar
    const getBrands = async (params = {}) => {
        const defaultParams = {
            page: 1,
            search_global: '',
            order_column: 'created_at',
            order_direction: 'desc'
        }
        return getItems(params, defaultParams)
    }

    const getBrandList = async () => getItemList('/api/brand-list')

    const createBrand = async () => {
        return createItem(brandSchema, brand.value)
    }

    const updateBrand = async () => {
        return updateItem(brand.value.id, brandSchema, brand.value)
    }

    // 6. FUNCIONES ESPECÍFICAS DE MARCAS
    // Estas no se pueden abstraer en useCrud porque son únicas para este recurso.
    
    
    // Obtenemos productos asociados a una marca
    
    const getBrandProducts = async (brandId) => {
        try {
            const response = await withLoading(() => axios.get(`/api/brands/${brandId}/products`))
            return response.data?.data ?? []
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: 'No se pudieron cargar los productos de esta marca',
                onGenericError: (message) => toast.error('Error', message)
            })
            return []
        }
    }

    
    //  Reasignamos un producto a una marca diferente
    
    const updateProductBrand = async (originBrandId, productId, targetBrandId) => {
        try {
            const response = await withLoading(() => 
                axios.put(`/api/brands/${originBrandId}/products/${productId}`, {
                    brand_id: targetBrandId
                })
            )
            toast.success('Éxito', 'Producto reasignado correctamente')
            return response.data
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: 'No se pudo reasignar el producto',
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    return {
        // Variables
        brands,
        brand,
        brandList,
        isLoading,
        errors,
        hasError,
        getError,
        
        // Métodos de formulario
        resetBrand,
        setBrand,
        upsertBrandRecord,
        
        // Métodos CRUD
        getBrands,
        getBrandList,
        createBrand,
        updateBrand,
        deleteBrand: deleteItem,
        
        // Métodos específicos
        getBrandProducts,
        updateProductBrand
    }
}
