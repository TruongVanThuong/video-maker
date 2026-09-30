<?php

use App\Http\Controllers\CreatomateWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/creatomate', [CreatomateWebhookController::class, 'handle'])
    ->name('webhooks.creatomate');
