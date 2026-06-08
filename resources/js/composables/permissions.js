import { ref } from 'vue'
import * as yup from 'yup'
import axios from 'axios'
import { useToast } from './useToast'
import { useCrud } from './useCrud'

export default function usePermissions() {
  const toast = useToast()

  const {
    items: permissions,
    itemList: permissionList,
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
  } = useCrud('/api/permissions', 'Permiso', 'permisos')

  const initialPermission = { id: null, name: '' }
  const permission = ref({ ...initialPermission })

  const permissionSchema = yup.object({
    name: yup.string().trim().required('El nombre es obligatorio').min(3, 'Debe tener al menos 3 caracteres')
  })

  const resetPermission = () => {
    permission.value = { ...initialPermission }
    clearErrors()
  }

  const setPermission = (data = {}) => {
    permission.value = {
      id: data.id ?? null,
      name: data.name ?? ''
    }
    clearErrors()
  }

  const upsertPermissionRecord = (record) => upsertRecord(record)

  const getPermissions = async (params = {}) => {
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

  const getPermissionList = async () => getItemList('/api/permissions')

  const createPermission = async () => createItem(permissionSchema, { name: permission.value.name })
  
  const updatePermission = async () => updateItem(permission.value.id, permissionSchema, { name: permission.value.name })

  // Específico: Obtener y actualizar permisos por rol
  const getRolePermissions = async (roleId) => {
    if (!roleId) return []
    try {
      const response = await withLoading(() => axios.get(`/api/role-permissions/${roleId}`))
      return response.data?.data ?? response.data ?? []
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudieron obtener los permisos del rol' })
      return []
    }
  }

  const updateRolePermissions = async (roleId, permissionIds = []) => {
    try {
      const response = await withLoading(() => axios.put('/api/role-permissions', {
        role_id: roleId,
        permissions: JSON.stringify(permissionIds)
      }))
      toast.crud.updated('Permisos del rol')
      return response.data?.data ?? response.data ?? []
    } catch (error) {
      handleRequestError(error, { fallbackMessage: 'No se pudieron actualizar los permisos' })
      throw error
    }
  }

  return {
    permissions,
    permission,
    permissionList,
    isLoading,
    errors,
    hasError,
    getError,
    resetPermission,
    setPermission,
    upsertPermissionRecord,
    getPermissions,
    getPermissionList,
    getRolePermissions,
    updateRolePermissions,
    createPermission,
    updatePermission,
    deletePermission: deleteItem
  }
}
