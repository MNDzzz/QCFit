import { ref } from 'vue'
import * as yup from 'yup'
import { useCrud } from './useCrud'
import axios from 'axios'

export default function useRoles() {
  const {
    items: roles,
    isLoading,
    errors,
    hasError,
    getError,
    clearErrors,
    handleRequestError,
    withLoading,
    upsertRecord,
    getItems,
    createItem,
    updateItem,
    deleteItem
  } = useCrud('/api/roles', 'Rol', 'roles')

  const initialRole = { id: null, name: '' }
  const role = ref({ ...initialRole })

  const roleSchema = yup.object({
    name: yup
      .string()
      .trim()
      .required('El nombre es obligatorio')
      .min(3, 'Debe tener al menos 3 caracteres')
  })

  const resetRole = () => { 
    role.value = { ...initialRole } 
    clearErrors() 
  }
  
  const setRole = (data = {}) => {
    role.value = { 
      id: data.id ?? null, 
      name: data.name ?? '' 
    }
    clearErrors() 
  }

  // Especifico para roles: Cargar un solo rol
  const getRole = async (id) => {
    if (!id) return null
    try {
      const response = await withLoading(() => axios.get(`/api/roles/${id}`))
      const data = response.data?.data ?? response.data
      setRole(data)
      return data
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudo obtener el rol' })
      throw error
    }
  }

  const upsertRoleRecord = (roleRecord) => upsertRecord(roleRecord)

  const getRoles = (params = {}) => {
    const defaultParams = {
      page: 1,
      search_id: '',
      search_title: '',
      search_global: '',
      order_column: 'created_at',
      order_direction: 'desc'
    }
    return getItems(params, defaultParams)
  }

  const createRole = async () => createItem(roleSchema, { name: role.value.name })
  const updateRole = async () => updateItem(role.value.id, roleSchema, { name: role.value.name })

  return {
    roles,
    role,
    isLoading,
    errors,
    hasError,
    getError,
    upsertRoleRecord,
    resetRole,
    setRole,
    getRoles,
    getRole,
    createRole,
    updateRole,
    deleteRole: deleteItem,
  }
}
