<?php

namespace App\Repositories;

use App\Models\PromoCode;
use App\Repositories\Contracts\PromoCodeRepositoryInterface;

class PromoCodeRepository implements PromoCodeRepositoryInterface
{
    public function getAllPromoCode() // Ini akan cocok dengan interface baru
    {
        return PromoCode::latest()->get();
    }

    public function findByCode(string $code) // Ini akan cocok dengan interface baru
    {
        return PromoCode::where('code', $code)->first();
    }
}
