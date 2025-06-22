<?php

namespace App\Repositories\Contracts;

interface PromoCodeRepositoryInterface
{
    // PERHATIKAN: HILANGKAN 's' dari 'Codes' di sini
    public function getAllPromoCode();

    // PERHATIKAN: UBAH 'o' menjadi 'C' kapital di sini
    public function findByCode(string $code);
}
