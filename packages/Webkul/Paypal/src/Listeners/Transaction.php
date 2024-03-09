<?php

namespace Webkul\Paypal\Listeners;

use Webkul\Paypal\Payment\SmartButton;
use Webkul\Sales\Repositories\OrderTransactionRepository;

class Transaction
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected SmartButton $smartButton,
        protected OrderTransactionRepository $orderTransactionRepository
    ) {
    }

    /**
     * Save the transaction data for online payment.
     *
     * @param  \Webkul\Sales\Models\Invoice  $invoice
     * @return void
     */
    public function saveTransaction($invoice)
    {
        $data = request()->all();
        if ($invoice->order->payment->method == 'paypal_smart_button') {
            if (isset($data['orderData']['orderID'])) {
                $transactionDetails = $this->smartButton->getOrder($data['orderData']['orderID']);

                $transactionDetails = json_decode(json_encode($transactionDetails), true);

                if ($transactionDetails['statusCode'] == 200) {
                    $this->orderTransactionRepository->create([
                        'transaction_id' => $transactionDetails['result']['id'],
                        'status'         => $transactionDetails['result']['status'],
                        'type'           => $transactionDetails['result']['intent'],
                        'amount'         => $transactionDetails['result']['purchase_units'][0]['amount']['value'],
                        'payment_method' => $invoice->order->payment->method,
                        'order_id'       => $invoice->order->id,
                        'invoice_id'     => $invoice->id,
                        'data'           => json_encode(
                            array_merge(
                                $transactionDetails['result']['purchase_units'],
                                $transactionDetails['result']['payer']
                            )
                        ),
                    ]);
                }
            }
        } elseif ($invoice->order->payment->method == 'paypal_standard') {
            $this->orderTransactionRepository->create([
                'transaction_id' => $data['razorpay_payment_id'],
                'status'         => $data['payment']['status'],
                'type'           => $data['payment']['method'],
                'payment_method' => $invoice->order->payment->method,
                'order_id'       => $invoice->order->id,
                'invoice_id'     => $invoice->id,
                'data'           => json_encode($data),
            ]);
        }
    }
}
