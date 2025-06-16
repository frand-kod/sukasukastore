<?php

namespace App\Repositories;

use App\Models\PromoCode;
use App\Repositories\Contracts\PromoCodeRepositoryInterface;

class PromoCodeRepository implements PromoCodeRepositoryInterface
{
    /**
     * Retrieves all promo codes, ordered by their creation date (latest first).
     *
     * @return \Illuminate\Database\Eloquent\Collection A collection of PromoCode models.
     */
    public function getAllPromoCode()
    {
        return PromoCode::latest()->get();
    }

    /**
     * Finds a single promo code by its unique code string.
     *
     * @param string $code The promo code string to search for.
     * @return \App\Models\PromoCode|null The PromoCode model instance or null if not found.
     */
    public function findByCode(string $code)
    {
        return PromoCode::where('code', $code)->first();
    }
}
