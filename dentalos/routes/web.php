<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// Webhook routes (placeholders)
Route::post('/webhooks/stripe', function() {
    // Stripe webhook logic
})->name('webhooks.stripe');

Route::post('/webhooks/twilio', function() {
    // Twilio webhook logic
})->name('webhooks.twilio');
