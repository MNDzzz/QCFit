import { ref } from 'vue'
import * as yup from 'yup'
import { useCrud } from './useCrud'


// Composable específico para gestionar las Categorías.
// Fíjate cómo gracias a `useCrud.js`, este archivo ahora solo contiene
// la lógica que es *exclusiva* de las categorías, no la fontanería de Axios.

export default function useCategories() {
  // 1. Inicializamos nuestro composable maestro pasándole los datos de esta entidad.
  // sacamos las herramientas que nos da useCrud y
  // renombramos 'items' a 'categories' para que quede más claro al leerlo.
  const {
    items: categories,
    itemList: categoryList,
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
  } = useCrud('/api/categories', 'Categoría', 'categorías')

  // 2. Estado local específico de Categorías
  const initialCategory = { id: null, name: '' }
  const category = ref({ ...initialCategory }) // Objeto que conectaremos al formulario de crear/editar

  // 3. Reglas de validación (Yup). Esto es único de cada entidad.
  const categorySchema = yup.object({
    name: yup
      .string()
      .trim()
      .required('El nombre es obligatorio')
      .min(3, 'Debe tener al menos 3 caracteres')
  })

  // 4. Funciones auxiliares para el formulario
  const resetCategory = () => {
    category.value = { ...initialCategory }
    clearErrors() // Limpia los mensajes en rojo del formulario
  }

  const setCategory = (data = {}) => {
    category.value = {
      id: data.id ?? null, // El ?? significa "si id no existe o es null, pon null" (encadenamiento opcional)
      name: data.name ?? ''
    }
    clearErrors()
  }

  const upsertCategoryRecord = (categoryRecord) => {
    upsertRecord(categoryRecord)
  }

  // 5. Envoltorios (Wrappers) para adaptar las funciones genéricas a nuestras necesidades
  const getCategories = async (params = {}) => {
    const defaultParams = {
      page: 1,
      search_id: '',
      search_title: '',
      search_global: '',
      order_column: 'created_at',
      order_direction: 'desc'
    }
    // Llamamos al getItems del crud pasándole nuestros parámetros por defecto
    return getItems(params, defaultParams)
  }

  const getCategoryList = async () => {
    return getItemList('/api/category-list')
  }

  const createCategory = async () => {
    // Le pasamos nuestro esquema de validación y los datos del formulario al crud maestro
    return createItem(categorySchema, { name: category.value.name })
  }

  const updateCategory = async () => {
    return updateItem(category.value.id, categorySchema, { name: category.value.name })
  }

  // 6. Exportamos lo que los componentes (las vistas de Vue) van a usar
  return {
    categories,
    category,
    categoryList,
    isLoading,
    errors,
    hasError,
    getError,
    resetCategory,
    setCategory,
    upsertCategoryRecord,
    getCategories,
    getCategoryList,
    createCategory,
    updateCategory,
    deleteCategory: deleteItem // Exportamos directamente el del crud, pero le cambiamos el nombre
  }
}
