<?php

namespace Webkul\Sales\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Shop\Mail\Order\AbandonedCart;

class SendNotificationToAbandonedCarts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = Carbon::now()->subHours(3);
        $carts = app(CartRepository::class)->whereNotNull('customer_email')->where('created_at', '>=', $date)->get();
        foreach ($carts as $cart) {
            Mail::queue(new AbandonedCart($cart));
        }
    }
}
