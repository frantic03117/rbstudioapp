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
use App\Http\Controllers\BlockSlotController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ExtraBookingAmountController;

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
Route::get('payment-pending-notification', [ApiController::class, 'payment_notification']);
Route::post('webhook-handler', [StudioController::class, 'paymentCallbackRazorpayWebHook']);
Route::post('find_start_slot', [AjaxController::class, 'find_start_slot'])->name('find_start_slot');
Route::post('find_end_slot', [AjaxController::class, 'find_end_slot'])->name('find_end_slot');
Route::get('find_gst_list/{id}', [ApiController::class, 'find_gst_list']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('terms/{url}', [ApiController::class, 'terms']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('delete-gst-details/{id}', [BookingController::class, 'delete_gst_details']);
    Route::post('contact_us', [ApiController::class, 'contact_us']);
    Route::get('contact_us', [ApiController::class, 'queries']);
    Route::post('contact_us/delete/{id}', [ApiController::class, 'delete_query']);
    Route::get('bookings', [ApiController::class, 'bookings']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    Route::get('create-order/{id}', [StudioController::class, 'pay_now_razorpay']);
    Route::get('policies', [ApiController::class, 'policies']);
    Route::post('book-studio', [BookingController::class, 'store']);
    Route::post('delete-account',  [ApiController::class, 'delete_account']);
    Route::post('search', [ApiController::class, 'search_bookings']);
    Route::post('coupan', [ApiController::class, 'add_promo_code']);
    Route::post('add-fcm', [ApiController::class, 'update_fcm']);
    Route::get('notifications', [ApiController::class, 'my_notifications']);
    Route::get('gst_list', [ApiController::class, 'gst_list']);
    Route::post('artist-update/{id}', [BookingController::class, 'updateArtist'])->name('updateArtist');
    Route::post('update_profile', [ApiController::class, 'update_profile']);
    Route::post('update_profile_image', [UserController::class, 'select_profile_image']);
    Route::post('clear-notification', [ApiController::class, 'clear_notification']);
    Route::post('/mark-read', [AdminController::class, 'mark_read'])->name('mark-read');
    Route::get('/is-all-read', [ApiController::class, 'is_all_notification_read'])->name('is_all_notification_read');
    Route::post('/mark-all-read', [ApiController::class, 'mark_all_read']);
    Route::get('/find-rental-items/{id}', [RentController::class, 'findRentalItemsApi']);
    Route::post('add-rental-item', [BookingController::class, 'booking_item_add']);
    Route::post('remove-rental-item-from-booking', [BookingController::class, 'remove_rental_item_from_booking']);
});
Route::post('admin', [AdminController::class, 'api_login']);
Route::get('/transactions-all', [TransactionController::class, 'index'])->name('api_transactions');
Route::middleware(['auth:sanctum', 'checkrole:Super,Admin,Employee'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/slots', [ApiController::class, 'all_slots']);
    Route::post('/book-studio', [BookingController::class, 'store']);
    Route::get('bookings/{slug}', [BookingController::class, 'custom_view']);
    Route::get('bookings/show/{id}', [BookingController::class, 'show']);
    Route::get('bookings/details/{id}', [BookingController::class, 'show_new']);
    Route::post('bookings/update/{id}', [BookingController::class, 'update']);
    Route::get('transactions', [TransactionController::class, 'index']);
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::post('transactions/update/{id}', [TransactionController::class, 'update']);
    Route::post('transactions/delete/{id}', [TransactionController::class, 'destroy']);
    Route::get('/', [AdminController::class, 'admin_profile']);
    Route::get('vendors', [VendorController::class, 'index']);
    Route::get('studios', [StudioController::class, 'index']);
    Route::get('approve_booking/{id}', [BookingController::class, 'approve_booking']);
    Route::get('bookings/make-confirm/{id}', [BookingController::class, 'confirm_booking']);
    Route::get('users', [UserController::class, 'users']);
    Route::post('users', [UserController::class, 'store_user']);
    Route::post('users/update/{id}', [UserController::class, 'update_edit_user']);
    Route::get('services', [ServiceController::class, 'index']);
    Route::get('promo-codes', [PromoCodeController::class, 'index']);
    Route::post('promo-codes', [PromoCodeController::class, 'store']);
    Route::post('promo-codes/delete/{id}', [PromoCodeController::class, 'destroy']);
    Route::get('events', [AdminController::class, 'events']);
    Route::get('notifications', [AdminController::class, 'all_notification']);
    Route::post('gallery', [GalleryController::class, 'store']);
    Route::get('gallery', [GalleryController::class, 'index']);
    Route::get('queries', [HomeController::class, 'queries']);
    Route::post('resolve_queries', [HomeController::class, 'resolve_queries']);
    Route::post('bookings/cancel/{id}', [BookingController::class, 'destroy']);
    Route::post('booking_item-add', [BookingController::class, 'booking_item_add']);
    Route::post('extra-amount/store', [ExtraBookingAmountController::class, 'store']);
    Route::post('update-gst-details', [BookingController::class, 'update_gst_details']);
    Route::post('update-tds/{id}', [BookingController::class, 'tds_control']);
    Route::post('update_profile_image', [UserController::class, 'select_profile_image']);
    Route::post('update_rental_item', [BookingController::class, 'update_rental_item_in_booking']);
    Route::post('remove_rental_item_from_booking', [BookingController::class, 'remove_rental_item_from_booking']);
    Route::post('booking-discount', [BookingController::class, 'discount']);
    Route::get('blocked-slot', [BlockSlotController::class, 'index']);
    Route::post('blocked-slot', [BlockSlotController::class, 'store']);
    Route::post('/blocked-slot/destroy-multiple', [BlockSlotController::class, 'destroyMultiple']);
    Route::post('/blocked-slot/destroy/{id}', [BlockSlotController::class, 'destroy'])->name('remove_buffer_time');
});
Route::get('/admin/add-buffer-slot/{id}', [BlockSlotController::class, 'add_buffer_time'])->name('add_buffer_time');
Route::get('/fault', [BlockSlotController::class, 'bookingfindwithoutsots']);

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
