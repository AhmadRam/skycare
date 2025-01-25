<?php

namespace Webkul\Shop\Http\Middleware;

use Closure;
use Stevebauman\Location\Facades\Location;
use Webkul\Core\Repositories\CurrencyRepository;
use Webkul\Shop\Jobs\SendFacebookEventJob;

class Currency
{
    /**
     * Create a middleware instance.
     *
     * @return void
     */
    public function __construct(protected CurrencyRepository $currencyRepository) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($currencyCode = request()->get('currency')) {
            if ($this->currencyRepository->findOneByField('code', $currencyCode)) {
                core()->setCurrentCurrency($currencyCode);

                session()->put('currency', $currencyCode);
            }
        } else {
            if ($currencyCode = session()->get('currency')) {
                core()->setCurrentCurrency($currencyCode);
            } else {

                $currencyMap = [
                    'SA' => 'SAR', // Saudi Arabia
                    'QA' => 'QAR', // Qatar
                    'AE' => 'AED', // UAE
                    'BH' => 'BHD', // Bahrain
                    'OM' => 'OMR', // Oman
                    'KW' => 'KWD', // Kuwait
                ];

                $userLocation = Location::get();
                if ($userLocation && isset($currencyMap[$userLocation->countryCode])) {
                    $currency = $currencyMap[$userLocation->countryCode];
                    core()->setCurrentCurrency($currency);
                } else {
                    core()->setCurrentCurrency(core()->getChannelBaseCurrencyCode());
                }
            }
        }

        unset($request['currency']);
        dispatch(new SendFacebookEventJob('PageView', auth()->user(), null));

        return $next($request);
    }
}
