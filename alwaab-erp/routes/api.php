<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Website\WebsiteQuoteController;
use App\Http\Controllers\Api\V1\CRM\ContactController;
use App\Http\Controllers\Api\V1\CRM\LeadController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FieldVisit\VisitController;
use App\Http\Controllers\Api\V1\Notification\NotificationController;
use App\Http\Controllers\Api\V1\Inventory\InventoryController;
use App\Http\Controllers\Api\V1\Inventory\ProductController;
use App\Http\Controllers\Api\V1\Projects\ProjectController;
use Illuminate\Support\Facades\Route;

Route::post('website/quote-requests', [WebsiteQuoteController::class, 'store'])
    ->middleware(['website.key', 'throttle:10,1']);

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('dashboard/kpis', [DashboardController::class, 'kpis'])
        ->middleware('permission:dashboard.view');

    Route::get('contacts', [ContactController::class, 'index'])->middleware('permission:contacts.view');
    Route::post('contacts', [ContactController::class, 'store'])->middleware('permission:contacts.create');
    Route::get('contacts/{contact}', [ContactController::class, 'show'])->middleware('permission:contacts.view');
    Route::put('contacts/{contact}', [ContactController::class, 'update'])->middleware('permission:contacts.update');
    Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->middleware('permission:contacts.delete');

    Route::get('leads', [LeadController::class, 'index'])->middleware('permission:leads.view');
    Route::post('leads', [LeadController::class, 'store'])->middleware('permission:leads.create');
    Route::get('leads/{lead}', [LeadController::class, 'show'])->middleware('permission:leads.view');
    Route::post('leads/{lead}/stage', [LeadController::class, 'updateStage'])->middleware('permission:leads.update');

    Route::get('projects', [ProjectController::class, 'index'])->middleware('permission:projects.view');
    Route::post('projects', [ProjectController::class, 'store'])->middleware('permission:projects.create');
    Route::get('projects/map', [ProjectController::class, 'map'])->middleware('permission:projects.view');
    Route::get('projects/{project}', [ProjectController::class, 'show'])->middleware('permission:projects.view');
    Route::put('projects/{project}/status', [ProjectController::class, 'updateStatus'])->middleware('permission:projects.update');

    Route::get('products', [ProductController::class, 'index'])->middleware('permission:products.view');
    Route::get('products/{product}', [ProductController::class, 'show'])->middleware('permission:products.view');
    Route::get('products/{product}/specs', [ProductController::class, 'specs'])->middleware('permission:products.view');

    Route::get('inventory/stock', [InventoryController::class, 'stock'])->middleware('permission:inventory.view');
    Route::post('inventory/movements', [InventoryController::class, 'storeMovement'])->middleware('permission:inventory.move');

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/poll', [NotificationController::class, 'poll']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead']);

    Route::get('visits/active', [VisitController::class, 'active'])->middleware('permission:visits.create');
    Route::get('visits/live-gps', [VisitController::class, 'liveGps'])->middleware('permission:visits.manage');
    Route::get('visits/history', [VisitController::class, 'history'])->middleware('permission:visits.view');
    Route::get('visits/map', [VisitController::class, 'map'])->middleware('permission:visits.view');
    Route::get('visits/{visit}', [VisitController::class, 'show'])->middleware('permission:visits.view');
    Route::post('visits', [VisitController::class, 'store'])->middleware('permission:visits.create');
    Route::put('visits/{visit}/location', [VisitController::class, 'updateLocation'])->middleware('permission:visits.create');
    Route::post('visits/{visit}/photos', [VisitController::class, 'uploadPhoto'])->middleware('permission:visits.create');
    Route::put('visits/{visit}/complete', [VisitController::class, 'complete'])->middleware('permission:visits.create');
});
