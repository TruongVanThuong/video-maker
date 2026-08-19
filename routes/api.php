<?php

declare(strict_types=1);

use App\Http\Controllers\ProjectRenderController;
use App\Http\Controllers\VideoWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/video-rendered', [VideoWebhookController::class, 'handle']);
Route::post('/projects/{project}/render', [ProjectRenderController::class, 'render']);

