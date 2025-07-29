<?php

namespace Database\Factories;

use App\Models\ProductTransaction;
use App\Models\Shoe;
use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductTransactionFactory extends Factory
{
    protected $model = ProductTransaction::class;

    public function definition(): array
    {
        // Ambil sepatu acak
        $shoe = Shoe::inRandomOrder()->first();

        // Ambil ukuran sepatu yang valid untuk sepatu ini
        $sizes = $shoe->sizes()->pluck('size')->toArray();
        $size = count($sizes) ? $this->faker->randomElement($sizes) : $this->faker->randomElement(['38', '39', '40']);

        // Ambil promo code atau null (50% chance)
        $promoCode = $this->faker->boolean(50) ? PromoCode::inRandomOrder()->first() : null;

        // Jumlah pembelian 1-5
        $quantity = $this->faker->numberBetween(1, 5);

        // Hitung sub total dan diskon
        $subTotal = $shoe->price * $quantity;
        $discountAmount = $promoCode ? min($promoCode->discount_amount, $subTotal) : 0;
        $grandTotal = $subTotal - $discountAmount;

        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'booking_trx_id' => strtoupper(Str::random(12)),
            'city' => $this->faker->city(),
            'post_code' => $this->faker->postcode(),
            'proof' => $this->faker->imageUrl(640, 480, 'payment_proofs', true),
            'shoe_size' => $size,
            'address' => $this->faker->address(),
            'quantity' => $quantity,
            'sub_total_amount' => $subTotal,
            'discount_amount' => $discountAmount,
            'grand_total_amount' => $grandTotal,
            'is_paid' => $this->faker->boolean(80), // 80% sudah bayar
            'shoe_id' => $shoe->id,
            'promo_code_id' => $promoCode?->id,
        ];
    }
}
