<?php

namespace App\Repositories;

use App\Models\ProductTransaction;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\Session;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Creates a new product transaction record in the database.
     *
     * @param array $data The data for the new transaction.
     * @return \App\Models\ProductTransaction The created transaction model instance.
     */
    public function createTransaction(array $data)
    {
        return ProductTransaction::create($data);
    }

    /**
     * Finds a product transaction by its booking transaction ID and phone number.
     *
     * @param mixed $bookingTrxId The booking transaction ID.
     * @param mixed $phoneNumber The phone number.
     * @return \App\Models\ProductTransaction|null The found transaction model instance or null if not found.
     */
    public function findByTrxIdAndPhoneNumber($bookingTrxId, $phoneNumber)
    {
        return ProductTransaction::where('booking_trx_id', $bookingTrxId)
                    ->where('phone_number', $phoneNumber)
                    ->first();
    }

    /**
     * Saves or overwrites order data to the user's session.
     *
     * @param array $data The data to save to the session.
     * @return void
     */
    public function saveToSession(array $data)
    {
        Session::put('orderData', $data);
    }

    /**
     * Retrieves all order data stored in the user's session.
     *
     * @return array The order data from the session, or an empty array if not found.
     */
    public function getOrderDataFromSession()
    {
        return session('orderData', []);
    }

    /**
     * Merges new data with existing order data in the session.
     * If keys exist, they will be overwritten by the new data.
     *
     * @param array $data The data to merge into the session.
     * @return void
     */
    public function updateSessionData(array $data)
    {
        $orderData = session('orderData', []); // Get existing data, or empty array
        $orderData = array_merge($orderData, $data); // Merge new data into existing
        session(['orderData' => $orderData]); // Save the merged data back
    }
}
