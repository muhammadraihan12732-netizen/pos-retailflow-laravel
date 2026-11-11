<?php

if (! function_exists('rupiah')) {
    /**
     * Format angka menjadi Rupiah, mis. 12500 => "Rp 12.500".
     */
    function rupiah(float|int|string|null $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }
}
