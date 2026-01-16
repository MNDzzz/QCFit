<?php

namespace App\Services;

class AffiliateService
{
    protected const REF_CODE_CNFANS = 'QCFIT_ACADEMIC';
    protected const REF_CODE_MULEBUY = 'QCFIT_ACADEMIC';

    /**
     * Genera un enlace de afiliado para un agente específico.
     *
     * @param string $marketplace 'weidian', 'taobao', '1688'
     * @param string $sourceId ID del producto en el marketplace
     * @param string $agent 'cnfans', 'mulebuy'
     * @return string
     */
    public function generateAffiliateLink(string $marketplace, string $sourceId, string $agent = 'cnfans'): string
    {
        $refCode = ($agent === 'mulebuy') ? self::REF_CODE_MULEBUY : self::REF_CODE_CNFANS;

        // Normalizar nombre del marketplace para los parámetros del agente
        // CNFans usa: weidian, ali, taobao, 1688
        $shopType = strtolower($marketplace);

        switch ($agent) {
            case 'mulebuy':
                return "https://mulebuy.com/product/?shop_type={$shopType}&id={$sourceId}&ref={$refCode}";

            case 'cnfans':
            default:
                return "https://cnfans.com/product/?shop_type={$shopType}&id={$sourceId}&ref={$refCode}";
        }
    }
}
