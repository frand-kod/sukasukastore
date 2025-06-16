<?php

namespace App\Repositories\Contracts;

interface PromoCodeRepositoryInterface
{
    public function getAllPromoCodes();

    public function findByode (string $code);
}
