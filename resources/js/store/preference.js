import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const usePreferenceStore = defineStore('preference', () => {
    // State
    const preferredAgent = ref(localStorage.getItem('preferredAgent') || 'cnfans');

    // Actions
    function setAgent(agent) {
        preferredAgent.value = agent;
        localStorage.setItem('preferredAgent', agent);
    }

    /**
     * Genera un enlace de afiliado basado en el producto.
     * @param {Object} product - Objeto producto con marketplace, source_id, original_link
     * @returns {String} - URL de afiliado
     */
    function getAffiliateLink(product) {
        if (!product) return '#';

        // Si recibimos un producto completo del backend
        if (typeof product === 'object') {
            let { marketplace, source_id, original_link } = product;

            // Normalize marketplace
            let shopType = marketplace ? marketplace.toLowerCase() : '';

            // Fix: Mapear 'tmall' a 'taobao' para agentes que no distinguen
            if (shopType === 'tmall') shopType = 'taobao';

            if (shopType && source_id) {
                return generateAffiliateLinkLocal(shopType, source_id);
            }

            // Fallback: Si tenemos link original pero no datos estructurados, devolver original
            // TODO: Podríamos intentar parsear el link original aquí también
            return original_link || '#';
        }

        // Fallback si solo recibimos una URL directa (legacy)
        if (typeof product === 'string') {
            return product;
        }

        return '#';
    }

    /**
     * Genera el enlace localmente
     */
    function generateAffiliateLinkLocal(shopType, sourceId) {
        const refCode = 'QCFIT_ACADEMIC'; // Código de referido
        const agent = preferredAgent.value;

        // Parametros comunes
        const params = `shop_type=${shopType}&id=${sourceId}&ref=${refCode}`;

        switch (agent) {
            case 'mulebuy':
                return `https://mulebuy.com/product/?${params}`;
            case 'hoobuy':
                return `https://hoobuy.com/product/?${params}`;
            case 'pandabuy':
                return `https://pandabuy.com/product/?${params}`;
            case 'cnfans':
            default:
                // CNFans a veces usa 'invite' en lugar de 'ref', verificaremos documentación. 
                // Usaremos 'ref' por consistencia con lo pedido.
                return `https://cnfans.com/product/?${params}`;
        }
    }

    return {
        preferredAgent,
        setAgent,
        getAffiliateLink
    };
});
