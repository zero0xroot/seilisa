<?php
class PriceService {

    // default token ID
    private $defaultId = 'sei-network';

    /**
     * Get price in USD
     * @param string|null $symbol CoinGecko ID (e.g., 'sei-network', 'usd-coin')
     * @return float|null
     */
    public function priceUSD($symbol = null) {
        $id = $symbol ? strtolower($symbol) : $this->defaultId;

        $url = "https://api.coingecko.com/api/v3/simple/price?ids={$id}&vs_currencies=usd";
        $res = @file_get_contents($url);

        if (!$res) return null; // network issue

        $data = json_decode($res, true);

        if (isset($data[$id]['usd'])) {
            return $data[$id]['usd'];
        }

        // token not found
        return null;
    }
}
