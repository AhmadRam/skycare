<?php

namespace Webkul\Myfatoorah\Listeners;

use Webkul\Sales\Repositories\OrderTransactionRepository;

class Transaction
{
    /**
     * Create a new listener instance.
     *
     * @param  \Webkul\Sales\Repositories\OrderTransactionRepository  $orderTransactionRepository
     * @return void
     */
    public function __construct(
        protected OrderTransactionRepository $orderTransactionRepository
    ) {
    }

    /**
     * Save the transaction data for online payment.
     *
     * @param  \Webkul\Sales\Models\Invoice $invoice
     * @return void
     */
    public function saveTransaction($invoice)
    {
        $data = request()->all();

        $this->orderTransactionRepository->create([
            'transaction_id' => $data['paymentId'] ?? 0,
            'status'         => $invoice->state,
            'type'           => $data['payment_type'] ?? "myfatoorah",
            'payment_method' => $invoice->order->payment->method,
            'order_id'       => $invoice->order->id,
            'invoice_id'     => $invoice->id,
            'amount'         => $invoice->grand_total,
            'data'           => json_encode($data),
        ]);
    }
}
