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

    public function paymentConfirm(array $validated) // <-- Nama fungsi tidak sesuai dengan isinya!
    {

        $orderData = $this->orderRepository->getOrderDataFromSession();

        $productTransactionId = null; // Inisialisasi variabel untuk ID transaksi baru

        try {
            // DB::transaction untuk memastikan semua operasi (DB dan file) bersifat atomik
            DB::transaction(function () use ($validated, &$productTransactionId, $orderData) { // <-- MASALAH UTAMA: $validated dan $orderData tidak terdefinisi di sini atau di scope luar!

                // Logic untuk upload bukti pembayaran (proof)
                if (isset($validated['proof'])) {
                    // Asumsi $validated['proof'] adalah instance UploadedFile
                    $proofPath = $validated['proof']->store('proofs', 'public'); // Simpan file ke storage
                    $validated['proof'] = $proofPath; // Ganti objek file dengan path-nya
                }

                // Menggabungkan data pesanan dari sesi ($orderData) ke array $validated
                // Ini terlihat seperti persiapan data untuk disimpan sebagai ProductTransaction
                $validated['name'] = $orderData['name'];
                $validated['email'] = $orderData['email'];
                $validated['phone'] = $orderData['phone'];
                $validated['address'] = $orderData['address'];
                $validated['post_code'] = $orderData['post_code'];
                $validated['city'] = $orderData['city'];
                $validated['quantity'] = $orderData['quantity'];
                $validated['sub_total_amount'] = $orderData['sub_total_amount'];
                $validated['grand_total_amount'] = $orderData['grand_total_amount'];
                $validated['discount_amount'] = $orderData['total_discount_amount']; // Perhatikan nama kunci 'total_discount_amount' vs 'discount_amount'
                $validated['promo_code_id'] = $orderData['promo_code_id'];
                $validated['shoe_id'] = $orderData['shoe_id'];
                $validated['shoe_size'] = $orderData['shoe_size'];
                $validated['size_id'] = $orderData['size_id'];

                $validated['is_paid'] = false; // Menandai transaksi sebagai belum lunas
                $validated['booking_trx_id'] = ProductTransaction::generateUniqueTrxId(); // Generate ID transaksi unik

                // Menyimpan transaksi baru ke database melalui OrderRepository
                $newTransaction = $this->orderRepository->createTransaction($validated);

                // Mengambil ID transaksi yang baru dibuat dan menyimpannya ke variabel luar
                $productTransactionId = $newTransaction->id;

            }); // End of DB::transaction closure

        } catch (\Exception $e) {
            // Menangani error jika ada masalah selama proses transaksi
            Log::error('Error in payment confirmation: ' . $e->getMessage()); // Log error
            session()->flash('error', $e->getMessage()); // Tampilkan pesan error ke pengguna
            return null; // Kembalikan null jika terjadi error
        }

        // Kembalikan ID transaksi jika proses sukses
        return $productTransactionId;
    }

}
