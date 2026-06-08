import { ref } from 'vue'
import axios from 'axios'
import { useToast } from './useToast'
import { useValidation } from './useValidation'

/**
 Este es el composable maestro. Creo este archivo para evitar repetir la misma lógica 
 de llamar a la API en cada uno de los recursos (Marcas, Categorías, Productos...).
 En lugar de escribir axios.get(), axios.post(), try/catch para manejar errores 
 varias veces por todo el proyecto, lo hacemos aquí una sola vez de forma genérica.
 @param {string} endpoint - La URL de nuestra API a la que vamos a atacar (ej: '/api/categories')
 @param {string} itemNameSingular - Nombre para mostrar en alertas cuando creamos uno (ej: 'Categoría')
 @param {string} itemNamePlural - Nombre para alertas cuando fallamos al traer la lista (ej: 'categorías')
 */
export function useCrud(endpoint, itemNameSingular, itemNamePlural) {
    //variables reactivas genéricas. 'items' guardará lo que sea que estemos pidiendo
    //(un array de categorías, marcas, etc.)
    const items = ref([])
    const itemList = ref([])
    
    //bandera para saber si estamos esperando respuesta del servidor
    const isLoading = ref(false)
    const toast = useToast()

    //Importamos nuestra lógica de validación y manejo de errores
    const {
        errors,
        validate,
        handleRequestError,
        clearErrors,
        hasError,
        getError
    } = useValidation()

    
    //  Función para evitar solapar peticiones. Si ya estamos cargando algo, 
    //  lanzamos un error para evitar doble clic en botones.
    //  Si no, marcamos isLoading a true, ejecutamos la petición, y al terminar (finally), 
    //  lo volvemos a poner a false pase lo que pase.
     
    const withLoading = async (fn) => {
        if (isLoading.value) throw new Error('Operación en curso, espera un segundo...')
        isLoading.value = true
        try {
            return await fn()
        } finally {
            isLoading.value = false
        }
    }

    
    //  actualizamos o insertamos un registro en nuestro array local 'items' sin tener que 
    //  volver a pedir toda la lista a la base de datos.
    
    const upsertRecord = (record) => {
        if (!record?.id) return
        items.value = [
            record,
            ...items.value.filter(item => item.id !== record.id)
        ]
    }

    
    //  Cargamos los registros paginados (Read de CRUD).
    
    const getItems = async (params = {}, defaultParams = {}) => {
        // Unimos los parámetros por defecto (página 1, orden) con los que envíe el usuario (búsquedas)
        const query = new URLSearchParams({ ...defaultParams, ...params }).toString()
        const response = await axios.get(`${endpoint}?${query}`)
        
        // Asignamos la respuesta. El '??' asegura que si data o data.data no existen, devuelva un array vacío.
        items.value = response.data?.data ?? response.data ?? []
        return response
    }

    
    // Cargamos una lista simple para selects/dropdowns (sin paginación).
    
    const getItemList = async (listEndpoint) => {
        try {
            const response = await axios.get(listEndpoint)
            itemList.value = response.data?.data ?? response.data ?? []
            return response
        } catch (error) {
            // Usamos nuestro manejador de errores centralizado
            handleRequestError(error, {
                fallbackMessage: `¡Ups! No pudimos obtener la lista de ${itemNamePlural}`,
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    /**
     * Guardamos un nuevo registro (Create de CRUD).
     * @param {Object} schema - El esquema de validación Yup
     * @param {Object} data - Los datos a enviar
     * @param {Function} customRequestFn - Opcional: si necesitamos enviar los datos de forma distinta (ej: FormData para imágenes)
     */
    const createItem = async (schema, data, customRequestFn = null) => {
        // 1. Validamos los datos en el frontend antes de enviarlos (ahorra llamadas al servidor)
        const { isValid } = await validate(schema, data)
        if (!isValid) {
            toast.error('Ojo, error de validación', 'Por favor, revisa los campos en rojo.')
            throw new Error('Validación fallida')
        }

        // 2. Intentamos enviarlos
        try {
            // si nos pasan una función customizada (como en productos), la usamos. Si no, hacemos un POST normal.
            const response = await withLoading(() =>
                customRequestFn ? customRequestFn() : axios.post(endpoint, data)
            )
            const responseData = response.data?.data ?? response.data
            
            //si todo va bien mostramos el mensaje de éxito.
            toast.crud.created(itemNameSingular)
            return responseData
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: `Error al intentar crear: ${itemNameSingular}`,
                onValidationError: () => toast.error('Error de validación', 'Revisa los campos resaltados.'),
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    
    // Actualizamos un registro existente (Update de CRUD).
    
    const updateItem = async (id, schema, data, customRequestFn = null) => {
        const { isValid } = await validate(schema, data)
        if (!isValid) {
            toast.error('Ojo, error de validación', 'Revisa los campos resaltados.')
            throw new Error('Validación fallida')
        }

        try {
            const response = await withLoading(() =>
                customRequestFn ? customRequestFn() : axios.put(`${endpoint}/${id}`, data)
            )
            const responseData = response.data?.data ?? response.data
            toast.crud.updated(itemNameSingular)
            return responseData
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: `Problema al actualizar: ${itemNameSingular}`,
                onValidationError: () => toast.error('Error de validación', 'Revisa los campos resaltados.'),
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    
    // Eliminamos un registro (Delete de CRUD).
    
    const deleteItem = async (id) => {
        try {
            const response = await withLoading(() => axios.delete(`${endpoint}/${id}`))
            // Lo quitamos del array local para que desaparezca de la tabla inmediatamente
            items.value = items.value.filter(item => item.id !== id)
            toast.crud.deleted(itemNameSingular)
            return response
        } catch (error) {
            handleRequestError(error, {
                fallbackMessage: `No se pudo eliminar ${itemNameSingular}`,
                onGenericError: (message) => toast.error('Error', message)
            })
        }
    }

    // Finalmente, exponemos todas estas piezas para que otros archivos las puedan usar
    return {
        items,
        itemList,
        isLoading,
        errors,
        hasError,
        getError,
        clearErrors,
        validate,
        handleRequestError,
        withLoading,
        upsertRecord,
        getItems,
        getItemList,
        createItem,
        updateItem,
        deleteItem
    }
}
