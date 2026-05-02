<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KitController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminKitController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminDelivererController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminZoneController;
use App\Http\Controllers\Admin\AdminPromoController;
use App\Http\Controllers\Admin\AdminOfferController;
use App\Http\Controllers\Admin\AdminReviewController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 🔓 ROUTES PUBLIQUES
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

// 🌐 AUTH SOCIALE
Route::get('/auth/{provider}/redirect', [\App\Http\Controllers\SocialAuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [\App\Http\Controllers\SocialAuthController::class, 'handleProviderCallback']);

Route::get('/kits', [KitController::class, 'index']);
Route::get('/kits/{slug}', [KitController::class, 'show']);
Route::get('/categories', [KitController::class, 'categories']);

// Avis publics
Route::get('/reviews', [ReviewController::class, 'index']);

// 🎁 Offres & Abonnements (PUBLIC)
Route::get('/offers', [OfferController::class, 'index']);
Route::get('/offers/{slug}', [OfferController::class, 'show']);
Route::get('/offers/{slug}/subscriptions', [OfferController::class, 'subscriptions']);

// Webhook paiement (doit être public, sans CSRF/Auth)
// Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
Route::post('/webhook/leekpay', [PaymentController::class, 'webhook']);

// 🤖 Assistant IA (PUBLIC)
Route::post('/ai/chat', [AiAssistantController::class, 'chat']);


// 🔐 ROUTES PROTÉGÉES (Connecté)
Route::middleware('auth:sanctum')->group(function () {

    // Authentification & Utilisateur
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);

    // Favoris
    Route::post('/kits/{id}/favorite', [KitController::class, 'toggleFavorite']);

    // Adresses
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
    Route::get('/zones', function() {
        return response()->json(\App\Models\DeliveryZone::all());
    });

    // Commandes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);

    // Paiement
    Route::post('/payments/initiate/{orderId}', [PaymentController::class, 'initiate']);

    // Avis & Notifications
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Abonnements
    Route::get('/subscriptions', [\App\Http\Controllers\SubscriptionController::class, 'index']);
    Route::post('/subscriptions/pay', [OfferController::class, 'pay']);
    Route::post('/subscriptions', [\App\Http\Controllers\SubscriptionController::class, 'store']);
    Route::put('/subscriptions/{id}', [\App\Http\Controllers\SubscriptionController::class, 'update']);
    Route::put('/subscriptions/{id}/pause', [\App\Http\Controllers\SubscriptionController::class, 'pause']);
    Route::delete('/subscriptions/{id}', [\App\Http\Controllers\SubscriptionController::class, 'destroy']);

    // 🤖 Assistant IA (HISTORY - AUTH ONLY)
    Route::prefix('ai')->group(function () {
        Route::get('/conversations',         [AiAssistantController::class, 'conversations']);
        Route::get('/conversations/{id}',    [AiAssistantController::class, 'conversation']);
        Route::delete('/conversations/{id}', [AiAssistantController::class, 'deleteConversation']);
    });

    // 🛵 ESPACE LIVREUR
    Route::middleware('is.livreur')->prefix('livreur')->group(function () {
        Route::get('/deliveries', [DeliveryController::class, 'index']);
        Route::patch('/deliveries/{id}/take', [DeliveryController::class, 'take']);
        Route::post('/deliveries/{id}/location', [DeliveryController::class, 'updateLocation']);
        Route::patch('/deliveries/{id}/complete', [DeliveryController::class, 'complete']);
    });


    // 🛡️ ESPACE ADMIN
    Route::middleware('is.admin')->prefix('admin')->group(function () {

        // Dashboard
        Route::get('/stats', [AdminDashboardController::class, 'stats']);

        // Kits repas
        Route::get('/kits',              [AdminKitController::class, 'index']);
        Route::post('/kits',             [AdminKitController::class, 'store']);
        Route::get('/kits/{id}',         [AdminKitController::class, 'show']);
        Route::put('/kits/{id}',         [AdminKitController::class, 'update']);
        Route::delete('/kits/{id}',      [AdminKitController::class, 'destroy']);
        Route::put('/kits/{id}/toggle',  [AdminKitController::class, 'toggle']);
        Route::post('/kits/upload-image',[AdminKitController::class, 'uploadImage']);

        // Commandes
        Route::get('/orders',                       [AdminOrderController::class, 'index']);
        Route::get('/orders/{id}',                  [AdminOrderController::class, 'show']);
        Route::put('/orders/{id}/status',           [AdminOrderController::class, 'updateStatus']);
        Route::put('/orders/{id}/assign-deliverer', [AdminOrderController::class, 'assignDeliverer']);
        Route::get('/orders/export',                [AdminOrderController::class, 'export']);

        // Livreurs
        Route::get('/livreurs',              [AdminDelivererController::class, 'index']);
        Route::post('/livreurs',             [AdminDelivererController::class, 'store']);
        Route::get('/livreurs/{id}',         [AdminDelivererController::class, 'show']);
        Route::put('/livreurs/{id}',         [AdminDelivererController::class, 'update']);
        Route::put('/livreurs/{id}/toggle',  [AdminDelivererController::class, 'toggle']);

        // Clients
        Route::get('/clients',             [AdminUserController::class, 'index']);
        Route::get('/clients/{id}',        [AdminUserController::class, 'show']);
        Route::put('/clients/{id}/toggle', [AdminUserController::class, 'toggle']);

        // Paiements
        Route::get('/paiements',        [AdminPaymentController::class, 'index']);
        Route::get('/paiements/export', [AdminPaymentController::class, 'export']);

        // Zones
        Route::get('/zones',        [AdminZoneController::class, 'index']);
        Route::post('/zones',       [AdminZoneController::class, 'store']);
        Route::put('/zones/{id}',   [AdminZoneController::class, 'update']);
        Route::delete('/zones/{id}',[AdminZoneController::class, 'destroy']);

        // Codes promo
        Route::get('/promos',             [AdminPromoController::class, 'index']);
        Route::post('/promos',            [AdminPromoController::class, 'store']);
        Route::put('/promos/{id}',        [AdminPromoController::class, 'update']);
        Route::delete('/promos/{id}',     [AdminPromoController::class, 'destroy']);
        Route::put('/promos/{id}/toggle', [AdminPromoController::class, 'toggle']);

        // Avis
        Route::get('/avis',              [AdminReviewController::class, 'index']);
        Route::put('/avis/{id}/approve', [AdminReviewController::class, 'approve']);
        Route::put('/avis/{id}/reject',  [AdminReviewController::class, 'reject']);
        Route::delete('/avis/{id}',      [AdminReviewController::class, 'destroy']);

        // Catégories
        Route::get('/categories',             [\App\Http\Controllers\Admin\AdminCategoryController::class, 'index']);
        Route::post('/categories',            [\App\Http\Controllers\Admin\AdminCategoryController::class, 'store']);
        Route::put('/categories/{id}',        [\App\Http\Controllers\Admin\AdminCategoryController::class, 'update']);
        Route::delete('/categories/{id}',     [\App\Http\Controllers\Admin\AdminCategoryController::class, 'destroy']);
        // Offres & Abonnements
        Route::get('/offers',                 [AdminOfferController::class, 'index']);
        Route::post('/offers',                [AdminOfferController::class, 'store']);
        Route::get('/offers/{id}',            [AdminOfferController::class, 'show']);
        Route::put('/offers/{id}',            [AdminOfferController::class, 'update']);
        Route::delete('/offers/{id}',         [AdminOfferController::class, 'destroy']);
        Route::put('/offers/{id}/toggle',     [AdminOfferController::class, 'toggle']);

        // Gestion des Administrateurs
        Route::get('/admins',              [\App\Http\Controllers\Admin\AdminManagementController::class, 'index']);
        Route::post('/admins',             [\App\Http\Controllers\Admin\AdminManagementController::class, 'store']);
        Route::put('/admins/{id}',         [\App\Http\Controllers\Admin\AdminManagementController::class, 'update']);
        Route::delete('/admins/{id}',      [\App\Http\Controllers\Admin\AdminManagementController::class, 'destroy']);
        Route::put('/admins/{id}/toggle',  [\App\Http\Controllers\Admin\AdminManagementController::class, 'toggleStatus']);

        // Formules d'abonnement (sub-resources)
        Route::post('/offers/{offerId}/subscriptions',      [AdminOfferController::class, 'storeSubscription']);
        Route::put('/subscriptions/{subId}',                [AdminOfferController::class, 'updateSubscription']);
        Route::delete('/subscriptions/{subId}',             [AdminOfferController::class, 'destroySubscription']);
    });

});
