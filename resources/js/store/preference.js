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
        // Si recibimos un producto completo del backend
        if (product && product.marketplace && product.source_id) {
            return generateAffiliateLinkLocal(product.marketplace, product.source_id);
        }

        // Fallback si solo recibimos una URL directa (legacy)
        if (typeof product === 'string') {
            return product;
        }

        return '#';
    }

    /**
     * Genera el enlace localmente (replica lógica del AffiliateService)
     */
    function generateAffiliateLinkLocal(marketplace, sourceId) {
        const refCode = 'QCFIT_ACADEMIC'; // Código de referido académico
        const agent = preferredAgent.value;
        const shopType = marketplace.toLowerCase(); // weidian, taobao, 1688

        switch (agent) {
            case 'mulebuy':
                return `https://mulebuy.com/product/?shop_type=${shopType}&id=${sourceId}&ref=${refCode}`;

            case 'hoobuy':
                return `https://hoobuy.com/product/?shop_type=${shopType}&id=${sourceId}&ref=${refCode}`;

            case 'pandabuy':
                return `https://pandabuy.com/product/?shop_type=${shopType}&id=${sourceId}&ref=${refCode}`;

            case 'cnfans':
            default:
                return `https://cnfans.com/product/?shop_type=${shopType}&id=${sourceId}&ref=${refCode}`;
        }
    }

    return {
        preferredAgent,
        setAgent,
        getAffiliateLink
    };
});
