import { ref } from 'vue'
import axios from 'axios'
import { useToast } from './useToast'

export default function useOutfits() {
  const outfits = ref([])
  const isLoading = ref(false)
  const toast = useToast()

  const withLoading = async (fn) => {
    if (isLoading.value) throw new Error('Operación en curso')
    isLoading.value = true
    try {
      return await fn()
    } finally {
      isLoading.value = false
    }
  }

  const getOutfits = async (params = {}) => {
    const defaultParams = {
      page: 1,
      order_column: 'created_at',
      order_direction: 'desc'
    }

    const query = new URLSearchParams({ ...defaultParams, ...params }).toString()
    const response = await axios.get(`/api/admin/outfits?${query}`)
    outfits.value = response.data?.data ?? response.data ?? []
    return response
  }

  const deleteOutfit = async (id) => {
    try {
      const response = await withLoading(() => axios.delete(`/api/admin/outfits/${id}`))
      outfits.value = outfits.value.filter(item => item.id !== id)
      toast.crud.deleted('Outfit')
      return response
    } catch (error) {
      const message = error.response?.data?.message || 'No se pudo eliminar el outfit'
      toast.error('Error', message)
    }
  }

  return {
    outfits,
    isLoading,
    getOutfits,
    deleteOutfit
  }
}
