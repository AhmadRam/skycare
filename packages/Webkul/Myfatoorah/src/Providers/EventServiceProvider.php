<?php

namespace Webkul\Myfatoorah\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Webkul\Theme\ViewRenderEventManager;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen('sales.invoice.save.after', 'Webkul\Myfatoorah\Listeners\Transaction@saveTransaction');
    }
}