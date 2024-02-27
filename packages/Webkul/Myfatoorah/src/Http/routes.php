<?php

use Illuminate\Support\Facades\Route;
use Webkul\Myfatoorah\Http\Controllers\StandardController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('myfatoorah/standard')->group(function () {
        Route::get('/redirect', [StandardController::class, 'redirect'])->name('myfatoorah.standard.redirect');

        Route::get('/callback', [StandardController::class, 'callback'])->name('myfatoorah.standard.callback');

        Route::get('/success', [StandardController::class, 'success'])->name('myfatoorah.standard.success');

        Route::get('/cancel', [StandardController::class, 'cancel'])->name('myfatoorah.standard.cancel');
    });
});
