import { ref } from 'vue'
import * as yup from 'yup'
import { useCrud } from './useCrud'

export default function useSources() {
    const {
        items: sources,
        itemList: sourceList,
        isLoading,
        errors,
        hasError,
        getError,
        clearErrors,
        upsertRecord,
        getItems,
        getItemList,
        createItem,
        updateItem,
        deleteItem
    } = useCrud('/api/sources', 'Marketplace', 'marketplaces')

    const initialSource = { id: null, name: '', slug: '', logo_url: '', base_url: '' }
    const source = ref({ ...initialSource })

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

    const upsertSourceRecord = (record) => upsertRecord(record)

    const getSources = async (params = {}) => {
        const defaultParams = {
            page: 1,
            search_global: '',
            order_column: 'created_at',
            order_direction: 'desc'
        }
        return getItems(params, defaultParams)
    }

    const getSourceList = async () => getItemList('/api/source-list')

    const createSource = async () => createItem(sourceSchema, source.value)
    
    const updateSource = async () => updateItem(source.value.id, sourceSchema, source.value)

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
        deleteSource: deleteItem
    }
}
