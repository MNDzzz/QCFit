import { useCrud } from './useCrud'

export default function useOutfits() {
  const {
    items: outfits,
    isLoading,
    getItems: getOutfits,
    deleteItem
  } = useCrud('/api/admin/outfits')

  // Envolvemos deleteItem para pasarle el nombre correcto para las notificaciones
  const deleteOutfit = async (id) => {
    return await deleteItem(id, 'Outfit')
  }

  return {
    outfits,
    isLoading,
    getOutfits,
    deleteOutfit
  }
}
