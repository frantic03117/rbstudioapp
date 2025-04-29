<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VendorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Route::fallback(function(){
//     return response()->json([
//         'success' => 0,
//         'message' => 'Route does not exist'
//     ], 404);
// });
Route::post('enter-mobile', [AuthController::class, 'enter_mobile']);
Route::post('verify-mobile', [AuthController::class, 'verify_otp']);
Route::post('register', [AuthController::class, 'register']);
Route::get('cancel-booking', [ApiController::class, 'cancel_booking']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('terms/{url}', [ApiController::class, 'terms']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('contact_us', [ApiController::class, 'contact_us']);
    Route::get('contact_us', [ApiController::class, 'queries']);
    Route::post('contact_us/delete/{id}', [ApiController::class, 'delete_query']);
    Route::get('bookings', [ApiController::class, 'bookings']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    Route::get('create-order/{id}', [StudioController::class, 'pay_now_razorpay']);
    Route::get('policies', [ApiController::class, 'policies']);
    Route::post('book-studio', [BookingController::class, 'store']);
    Route::post('find_start_slot', [AjaxController::class, 'find_start_slot'])->name('find_start_slot');
    Route::post('find_end_slot', [AjaxController::class, 'find_end_slot'])->name('find_end_slot');
    Route::delete('delete-account',  [ApiController::class, 'delete_account']);
    Route::post('search', [ApiController::class, 'search_bookings']);
    Route::post('coupan', [ApiController::class, 'add_promo_code']);
    Route::post('add-fcm', [ApiController::class, 'update_fcm']);
    Route::get('notifications', [ApiController::class, 'my_notifications']);
    Route::get('gst_list', [ApiController::class, 'gst_list']);
    Route::post('update_profile', [ApiController::class, 'update_profile']);
    Route::post('update_profile_image', [UserController::class, 'select_profile_image']);
    Route::post('clear-notification', [ApiController::class, 'clear_notification']);
});
Route::post('admin', [AdminController::class, 'api_login']);
Route::middleware(['auth:sanctum', 'checkrole:Super,Admin,Employee'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::post('/book-studio', [BookingController::class, 'store']);
    Route::get('bookings/{slug}', [BookingController::class, 'custom_view']);
    Route::get('bookings/show/{id}', [BookingController::class, 'show']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/', [AdminController::class, 'admin_profile']);
    Route::get('/vendors', [VendorController::class, 'index']);
    Route::get('/studios', [StudioController::class, 'index']);
    Route::get('/users', [UserController::class, 'users']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/promo-codes', [PromoCodeController::class, 'index']);
    Route::post('/promo-codes/delete/{id}', [PromoCodeController::class, 'destroy']);
    Route::get('events', [AdminController::class, 'events']);
});

Route::post('get_slots', [AjaxController::class, 'get_slots']);
Route::post('pre_booking_details', [BookingController::class, 'pre_booking_details']);
Route::get('countries', [ApiController::class, 'countries']);
Route::get('rental', [RentController::class, 'index']);
Route::get('services', [ApiController::class, 'services']);
Route::get('states/{id}', [ApiController::class, 'states']);
Route::get('cities/{id}', [ApiController::class, 'cities']);
Route::get('banners', [ApiController::class, 'banners']);
Route::post('studios', [ApiController::class, 'studios']);
Route::get('faqs', [ApiController::class, 'faqs']);
Route::get('states/{id}', [ApiController::class, 'states']);
Route::get('gallery', [GalleryController::class, 'index']);
Route::post('query/resolve', [HomeController::class, 'resolve_queries'])->name('resolve_queries');
