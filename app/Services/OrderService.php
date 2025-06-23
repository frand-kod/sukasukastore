<?php

namespace App\Services;

use App\Models\ProductTransaction;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\PromoCodeRepositoryInterface;
use App\Repositories\Contracts\ShoeRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $categoryRepository;
    protected $promoCodeRepository;
    protected $orderRepository;
    protected $shoeRepository;

    public function __construct(
        PromoCodeRepositoryInterface $promoCodeRepository,
        CategoryRepositoryInterface $categoryRepository,
        OrderRepositoryInterface $orderRepository,
        ShoeRepositoryInterface $shoeRepository)
    {
        $this->categoryRepository = $categoryRepository;
        $this->promoCodeRepository = $promoCodeRepository;
        $this->orderRepository = $orderRepository;
        $this->shoeRepository = $shoeRepository;
    }

    public function beginOrder(array $data)
    {
        $orderData = [
            'shoe_size' => $data['shoe_size'],
            'size_id' => $data['size_id'],
            'shoe_id' => $data['shoe_id'],
        ];

        $this->orderRepository->saveToSession($orderData);
    }

    public function getMyOrderDetails(array $validated)
    {
        return $this->orderRepository->findByTrxIdAndPhoneNumber($validated['booking_trx_id'], $validated['phone']);
    }

    public function getOrderDetails()
    {
        // 1. Ambil data pesanan dari sesi
        $orderData = $this->orderRepository->getOrderDataFromSession();

        // 2. Ambil detail sepatu berdasarkan shoe_id dari data sesi
        $shoe = $this->shoeRepository->find($orderData['shoe_id']);

        // 3. Tentukan kuantitas (default 1 jika tidak ada di sesi)
        $quantity = isset($orderData['quantity']) ? $orderData['quantity'] : 1;

        // 4. Hitung sub total
        $subTotalAmount = $shoe->price * $quantity;

        // 5. Tentukan tarif pajak (hardcoded)
        $taxRate = 0.11;

        // 6. Hitung total pajak
        $totalTax = $subTotalAmount * $taxRate;

        // 7. Hitung grand total
        $grandTotalAmount = $subTotalAmount + $totalTax;

        // 8. Tambahkan detail perhitungan ke array orderData
        $orderData['sub_total_amount'] = $subTotalAmount;
        $orderData['total_tax'] = $totalTax;
        $orderData['grand_total_amount'] = $grandTotalAmount;

        session()->put('orderData', $orderData);

        // 9. Kembalikan data pesanan dan objek sepatu
        return compact('orderData', 'shoe');
    }

    public function applyPromoCode(string $code, int $subTotalAmount)
    {
        // 1. Cari kode promo berdasarkan string kode yang diberikan
        $promo = $this->promoCodeRepository->findByCode($code);

        // 2. Jika kode promo ditemukan
        if ($promo) {
            // 2a. Ambil jumlah diskon dari objek promo
            $discount = $promo->discount_amount; // Asumsi ini adalah diskon nilai tetap

            // 2b. Hitung grand total setelah diskon
            $grandTotalAmount = $subTotalAmount - $discount;

            // 2c. Ambil ID kode promo
            $promoCodeId = $promo->id;

            // 2d. Kembalikan data diskon, grand total baru, dan ID promo
            return [
                'discount' => $discount,
                'grandTotalAmount' => $grandTotalAmount,
                'promoCodeId' => $promoCodeId
            ];
        }

        // 3. Jika kode promo tidak ditemukan
        return ['error' => 'Kode promo tidak tersedia!'];
    }

    public function saveBookingTransaction(array $data)
    {
        $this->orderRepository->saveToSession($data);
    }

    public function updateCustomerData(array $data)
    {
        $this->orderRepository->updateSessionData($data);
    }

   // Lokasi: app/Services/OrderService.php
// Lokasi: app/Services/OrderService.php

public function paymentConfirm(array $validated)
{
    // Langkah 1: Mengambil data dari session
    // Pastikan OrderRepository->getOrderDataFromSession() mengambil dari session('orderData')
    $orderData = $this->orderRepository->getOrderDataFromSession();
    $productTransactionId = null;

    // Langkah 2: Memastikan data session ada sebelum melanjutkan
    if (!$orderData) {
        throw new \Exception("Sesi pesanan tidak ditemukan. Mohon ulangi proses dari awal.");
    }

    // Variabel ini akan kita isi nanti, untuk debugging jika terjadi error
    $dataToSave = [];

    try {
        DB::transaction(function () use ($validated, &$productTransactionId, $orderData, &$dataToSave) {

            // Langkah 3: Menyiapkan data yang akan disimpan, dimulai dari data form
            $dataToSave = $validated;

            if (isset($dataToSave['proof'])) {
                $dataToSave['proof'] = $dataToSave['proof']->store('proofs', 'public');
            }

            // Langkah 4: Menggabungkan data dari session ke array $dataToSave
            // STRUKTUR INI DISESUAIKAN DENGAN SCREENSHOT DD() TERAKHIR ANDA

            // Data Customer (tanpa sub-array 'customer')
            $dataToSave['name'] = $orderData['name'];
            $dataToSave['email'] = $orderData['email'];
            $dataToSave['phone'] = $orderData['phone'];
            $dataToSave['address'] = $orderData['address'];
            $dataToSave['post_code'] = $orderData['post_code'];
            $dataToSave['city'] = $orderData['city'];

            // Data Pesanan
            $dataToSave['quantity'] = $orderData['quantity'];
            $dataToSave['sub_total_amount'] = $orderData['sub_total_amount'];
            $dataToSave['grand_total_amount'] = $orderData['grand_total_amount'];

            // Menggunakan ?? untuk keamanan jika key tidak ada
            $dataToSave['discount_amount'] = $orderData['total_discount_amount'] ?? $orderData['discount'] ?? 0;
            $dataToSave['promo_code_id'] = $orderData['promo_code_id'] ?? null;

            // Data Sepatu
            $dataToSave['shoe_id'] = $orderData['shoe_id'];
            $dataToSave['shoe_size'] = $orderData['shoe_size'];
            $dataToSave['size_id'] = $orderData['size_id'];

            // Data Tambahan
            $dataToSave['is_paid'] = false;
            $dataToSave['booking_trx_id'] = ProductTransaction::generateUniqueTrxId();

            // Langkah 5: Menyimpan ke database
            $newTransaction = $this->orderRepository->createTransaction($dataToSave);
            $productTransactionId = $newTransaction->id;

            // Langkah 6: Menghapus session setelah semua berhasil
            $this ->orderRepository->clearSession();
        });

        // Langkah 7: Mengembalikan ID transaksi jika sukses
        return $productTransactionId;

    } catch (\Exception $e) {
        // PENTING: JIKA MASIH GAGAL, KODE INI AKAN DIJALANKAN
        // Ini akan menghentikan redirect dan menunjukkan apa yang sebenarnya salah.

        // Baris di bawah ini tidak akan dijalankan jika dd() aktif, tapi ini untuk produksi nanti
        // Log::error('Payment confirmation failed: ' . $e->getMessage());
        return null;
    }
}
}
