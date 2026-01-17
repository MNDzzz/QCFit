import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCanvasStore = defineStore('canvas', () => {
    // Estado del canvas
    const canvasItems = ref([]);
    const selectedId = ref(null);
    const nextZIndex = ref(1);
    const loadedOutfitId = ref(null); // ID del outfit cargado (para Remix)

    // Actions
    function addItem(product, imageUrl, imageId = null) {
        const newItem = {
            id: `item-${Date.now()}`,
            productId: product.id,
            productName: product.name,
            imageUrl: imageUrl,
            imageId: imageId, // ID de la imagen seleccionada
            x: 100 + (canvasItems.value.length * 20), // Offset para ver múltiples items
            y: 100 + (canvasItems.value.length * 20),
            width: 200,
            height: 200,
            rotation: 0,
            scaleX: 1,
            scaleY: 1,
            zIndex: nextZIndex.value++,
            isFlipped: false,
        };

        canvasItems.value.push(newItem);
        selectedId.value = newItem.id;
    }

    /**
     * Cargar un outfit existente en el canvas (Remix).
     * Recibe los datos de la API con productos y atributos pivote.
     * 
     * @param {Object} outfitData - Datos del outfit desde OutfitResource
     */
    function loadOutfit(outfitData) {
        // Limpiar canvas actual
        clearCanvas();

        // Guardar ID del outfit que estamos remixeando
        loadedOutfitId.value = outfitData.id;

        // Procesar cada item del outfit
        if (outfitData.items && Array.isArray(outfitData.items)) {
            outfitData.items.forEach((item) => {
                // Buscar la imagen correcta del producto
                let imageUrl = '';
                if (item.product && item.product.images && item.product.images.length > 0) {
                    // Si hay imageId, buscar esa imagen específica
                    if (item.imageId) {
                        const selectedImage = item.product.images.find(img => img.id === item.imageId);
                        imageUrl = selectedImage ? selectedImage.url : item.product.images[0].url;
                    } else {
                        // Sino, usar la primera imagen QC o la primera disponible
                        const qcImage = item.product.images.find(img => img.type === 'qc');
                        imageUrl = qcImage ? qcImage.url : item.product.images[0].url;
                    }
                }

                // Crear el item con los datos pivote del outfit original
                const canvasItem = {
                    id: `item-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                    productId: item.product_id,
                    productName: item.product?.name || 'Producto',
                    imageUrl: imageUrl,
                    imageId: item.imageId,
                    x: item.x || 100,
                    y: item.y || 100,
                    width: 200,
                    height: 200,
                    rotation: item.rotation || 0,
                    scaleX: item.scaleX || 1,
                    scaleY: item.scaleY || 1,
                    zIndex: item.zIndex || nextZIndex.value++,
                    isFlipped: item.isFlipped || false,
                };

                canvasItems.value.push(canvasItem);

                // Actualizar el máximo z-index
                if (canvasItem.zIndex >= nextZIndex.value) {
                    nextZIndex.value = canvasItem.zIndex + 1;
                }
            });
        }
    }

    function removeItem(id) {
        const index = canvasItems.value.findIndex(item => item.id === id);
        if (index !== -1) {
            canvasItems.value.splice(index, 1);
            selectedId.value = null;
        }
    }

    function updateItem(id, updates) {
        const item = canvasItems.value.find(i => i.id === id);
        if (item) {
            Object.assign(item, updates);
        }
    }

    function selectItem(id) {
        selectedId.value = id;
    }

    function bringToFront(id) {
        const item = canvasItems.value.find(i => i.id === id);
        if (item) {
            item.zIndex = nextZIndex.value++;
        }
    }

    function sendToBack(id) {
        const item = canvasItems.value.find(i => i.id === id);
        if (item) {
            item.zIndex = 0;
            // Reordenar todos los demás
            canvasItems.value.forEach(i => {
                if (i.id !== id && i.zIndex > 0) {
                    i.zIndex++;
                }
            });
        }
    }

    function flipItem(id) {
        const item = canvasItems.value.find(i => i.id === id);
        if (item) {
            item.scaleX *= -1;
            item.isFlipped = !item.isFlipped;
        }
    }

    function clearCanvas() {
        canvasItems.value = [];
        selectedId.value = null;
        nextZIndex.value = 1;
        loadedOutfitId.value = null;
    }

    // Computed
    const selectedItem = computed(() => {
        return canvasItems.value.find(item => item.id === selectedId.value);
    });

    const sortedItems = computed(() => {
        return [...canvasItems.value].sort((a, b) => a.zIndex - b.zIndex);
    });

    // ¿Estamos editando un outfit existente?
    const isRemixMode = computed(() => {
        return loadedOutfitId.value !== null;
    });

    return {
        canvasItems,
        selectedId,
        selectedItem,
        sortedItems,
        loadedOutfitId,
        isRemixMode,
        addItem,
        loadOutfit,
        removeItem,
        updateItem,
        selectItem,
        bringToFront,
        sendToBack,
        flipItem,
        clearCanvas
    };
});

