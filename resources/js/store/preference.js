import { defineStore } from 'pinia';
import { ref } from 'vue';

export const usePreferenceStore = defineStore('preference', () => {
    // State
    const preferredAgent = ref(localStorage.getItem('preferredAgent') || 'cnfans');

    // Actions
    function setAgent(agent) {
        preferredAgent.value = agent;
        localStorage.setItem('preferredAgent', agent);
    }

    function getAffiliateLink(originalUrl) {
        // Logic to rewrite URL based on agent
        // This is a placeholder logic based on the requirements
        // "Affiliate Hijacking"
        
        let baseUrl = '';
        let affiliateCode = '';

        switch (preferredAgent.value) {
            case 'cnfans':
                baseUrl = 'https://cnfans.com/product/?shop_type=taobao&id='; 
                affiliateCode = '&ref=MY_CNFANS_CODE';
                break;
            case 'mulebuy':
                baseUrl = 'https://mulebuy.com/product/?shop_type=taobao&id=';
                affiliateCode = '&ref=MY_MULEBUY_CODE';
                break;
            case 'hoobuy':
                baseUrl = 'https://hoobuy.com/product/?shop_type=taobao&id=';
                affiliateCode = '&ref=MY_HOOBUY_CODE';
                break;
            default:
                return originalUrl; // Fallback
        }

        // Simplistic extraction of ID from original URL (assuming Taobao for now as example)
        // Real implementation would need robust regex for Weidian/1688/Taobao
        const idMatch = originalUrl.match(/id=(\d+)/);
        if (idMatch && idMatch[1]) {
            return `${baseUrl}${idMatch[1]}${affiliateCode}`;
        }

        return originalUrl;
    }

    return {
        preferredAgent,
        setAgent,
        getAffiliateLink
    };
});
