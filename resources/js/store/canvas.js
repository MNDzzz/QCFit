import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCanvasStore = defineStore('canvas', () => {
    // Estado del canvas
    const canvasItems = ref([]);
    const selectedId = ref(null);
    const nextZIndex = ref(1);

    // Actions
    function addItem(product, imageUrl) {
        const newItem = {
            id: `item-${Date.now()}`,
            productId: product.id,
            productName: product.name,
            imageUrl: imageUrl,
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
    }

    // Computed
    const selectedItem = computed(() => {
        return canvasItems.value.find(item => item.id === selectedId.value);
    });

    const sortedItems = computed(() => {
        return [...canvasItems.value].sort((a, b) => a.zIndex - b.zIndex);
    });

    return {
        canvasItems,
        selectedId,
        selectedItem,
        sortedItems,
        addItem,
        removeItem,
        updateItem,
        selectItem,
        bringToFront,
        sendToBack,
        flipItem,
        clearCanvas
    };
});
