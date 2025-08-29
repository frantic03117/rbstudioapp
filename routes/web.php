<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BlockSlotController;
use App\Http\Controllers\ExtraBookingAmountController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;
use Kreait\Firebase\Factory;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test-fcm', function () {
    $factory = (new Factory)->withServiceAccount(storage_path('app/firebase/serviceAccount.json'));
    $messaging = $factory->createMessaging();
    return 'Firebase connection OK';
});
Route::get('/login', [AdminController::class, 'index'])->name('login');
Route::post('/admin', [AdminController::class, 'login'])->name('login.store');
Route::get('/admin', [AdminController::class, 'login'])->name('login.index');
Route::any('logout', [AdminController::class, 'logout'])->name('logout');
Route::any('check-payment', [StudioController::class, 'getPaymentStatusAfterPending']);

Route::get('/', [HomeController::class, 'home'])->name('home');
Route::get('/policy/{url}', [HomeController::class, 'terms'])->name('terms');
Route::get('admin/generate-bill/{id}', [BookingController::class, 'generate_bill'])->name('generate_bill');
Route::get('download_bill/{id}', [BookingController::class, 'download_bill'])->name('download_bill');
Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Route::get('/das', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/banners', [HomeController::class, 'banners'])->name('banners');
    Route::get('/banners_delete/{id}', [HomeController::class, 'banners_delete'])->name('banners_delete');
    Route::post('/banners', [HomeController::class, 'save_banners'])->name('save_banners');
    Route::resource('roles', RoleController::class);
    Route::resource('faq', FaqController::class);
    Route::resource('rents', RentController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('studio', StudioController::class);
    Route::resource('vendor', VendorController::class);
    Route::resource('setting', SettingController::class);
    Route::resource('blocked-slot', BlockSlotController::class);
    Route::get('add-resource/{id}', [StudioController::class, 'add_resource'])->name('add_resource');
    Route::get('delete_s_service/{id}', [StudioController::class, 'delete_s_service'])->name('delete_s_service');
    Route::post('add-resource/{id}', [StudioController::class, 'save_resource'])->name('studio.add_resource');
    Route::post('add_studio_service', [StudioController::class, 'add_studio_service'])->name('studio.add_studio_service');
    Route::delete('delete-resource', [StudioController::class, 'delete_studio_resource'])->name('studio.delete__studio_resource');
    Route::put('update-resource', [StudioController::class, 'update_studio_resource_charge'])->name('studio.update_studio_resource_charge');
    Route::get('booking/export', [BookingController::class, 'export'])->name('booking.export');
    Route::resource('employee', UserController::class);
    Route::resource('gallery', GalleryController::class);
    Route::resource('booking', BookingController::class);
    Route::resource('extra-amount', ExtraBookingAmountController::class);
    Route::get('bookings/{slug}', [BookingController::class, 'custom_view'])->name('bookingsview');
    Route::get('bookings/make-confirm/{id}', [BookingController::class, 'confirm_booking'])->name('confirm_booking');
    Route::get('bookings/re-book/{id}', [BookingController::class, 'rebook'])->name('rebook');
    Route::get('query', [HomeController::class, 'queries'])->name('queries');
    Route::post('studio/handle-pp/{id}', [StudioController::class, 'handlePartialPayment'])->name('handlePartialPayment');
    Route::post('update-gst-details', [BookingController::class, 'update_gst_details'])->name('update_gst_details');
    Route::get('add-buffer-slot/{id}', [BlockSlotController::class, 'add_buffer_time'])->name('add_buffer_time');


    Route::get('calendar', [HomeController::class, 'index'])->name('calendar');
    // Route::get('generate-bill/{id}', [BookingController::class, 'generate_bill'])->name('generate_bill');
    Route::post('generate-bill/{id}', [BookingController::class, 'save_bill'])->name('save_bill');
    // Route::get('download_bill/{id}', [BookingController::class, 'download_bill'])->name('download_bill');
    Route::post('booking_item-add', [BookingController::class, 'booking_item_add'])->name('booking_item.add');
    Route::get('booking_item-delete/{id}', [BookingController::class, 'booking_item_delete'])->name('booking_item.destroy');
    Route::get('approve_booking/{id}', [BookingController::class, 'approve_booking'])->name('approve_booking');
    Route::post('booking-discount', [BookingController::class, 'discount'])->name('booking.discount');
    Route::resource('transactions', TransactionController::class);
    Route::resource('policy', PolicyController::class);
    Route::resource('promo', PromoCodeController::class);
    Route::get('/notifications', [AdminController::class, 'all_notification'])->name('notification');
    Route::delete('/notifications/delete/{id}', [AdminController::class, 'delete_notification'])->name('delete_notification');
    Route::post('/mark-read', [AdminController::class, 'mark_read'])->name('mark-read');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::get('/users', [UserController::class, 'users'])->name('users');
    Route::post('/users', [UserController::class, 'store_user'])->name('store_user');
    Route::get('/edit_user/{id}', [UserController::class, 'edit_user'])->name('edit_user');
    Route::post('/edit_user/{id}', [UserController::class, 'update_edit_user'])->name('edit_user.update');
    Route::post('/update_admin_profile', [AdminController::class, 'update_admin_profile'])->name('update_admin_profile');
    Route::post('/update_rental_item', [BookingController::class, 'update_rental_item_in_booking'])->name('update_rental_item_in_booking');
    Route::post('/remove_rental_item_from_booking', [BookingController::class, 'remove_rental_item_from_booking'])->name('remove_rental_item_from_booking');
});
Route::prefix('ajax')->middleware(['auth'])->group(function () {
    Route::post('/get-state', [AjaxController::class, 'states'])->name('ajax_states');
    Route::post('/get-city', [AjaxController::class, 'cities'])->name('ajax_cities');
    Route::post('get-studios', [AjaxController::class, 'getStudios'])->name('ajax_studios');
    Route::post('get-slots', [AjaxController::class, 'get_slots'])->name('ajax_slots');
    Route::post('get-user', [AjaxController::class, 'get_user'])->name('ajax_user');
    Route::post('get_images', [AjaxController::class, 'get_images'])->name('ajax_studio_images');
    Route::post('add_image', [AjaxController::class, 'add_image'])->name('ajax_add_studio_images');
    Route::post('delete-images', [AjaxController::class, 'delete_images'])->name('ajax_studio_image_delete');
    Route::post('find_start_slot', [AjaxController::class, 'find_start_slot'])->name('find_start_slot');
    Route::post('find_end_slot', [AjaxController::class, 'find_end_slot'])->name('find_end_slot');
    Route::post('get_services', [AjaxController::class, 'get_services'])->name('ajax_services');
    Route::post('set_permissiable', [AjaxController::class, 'set_permissiable'])->name('set_permissiable');
    Route::post('get_rest_services', [AjaxController::class, 'get_rest_services'])->name('get_rest_services');
    Route::post('update_s_service', [StudioController::class, 'update_s_service'])->name('studio.update_s_service');
    Route::post('rent-items', [AjaxController::class, 'get_rent_items'])->name('ajax_rents');
    Route::get('web-notification', [AjaxController::class, 'web_notification'])->name('web_notification');
});
Route::get('ajax/delete-bookings-cron', [BookingController::class, 'cron_destroy_booking'])->name('cron_destroy_booking');
Route::get('ajax/events', [AdminController::class, 'events'])->name('events');
Route::any('ajax/paymentCallbackRazorpay', [StudioController::class, 'paymentCallbackRazorpay'])->name('paymentCallbackRazorpay');
// Route::post('add-payment-online/{id}', [StudioController::class, 'pay_now'])->name('pay_now'); cca venue payment
Route::post('add-payment-online/{id}', [StudioController::class, 'pay_now_razorpay'])->name('pay_now');
Route::post('pay_now/{id}', [StudioController::class, 'pay_now']);
Route::post('pay_now_razorpay/{id}', [StudioController::class, 'pay_now_razorpay'])->name('pay_now_razorpay');
Route::any('pay_response', [StudioController::class, 'pay_response'])->name('pay_response');
Route::any('pay_cancel', [StudioController::class, 'pay_cancel'])->name('pay_cancel');
Route::any('success_page/{id}', [TransactionController::class, 'success_page'])->name('success_page');
Route::any('payment-response/{type}/{id}', [TransactionController::class, 'success_page_order_id'])->name('success_page_response');
// Route::get('add-payment-online/{id}', [StudioController::class, 'add_payment_online'])->name('pay-online');
Route::get('add-payment-online/{id}', [StudioController::class, 'pay_now_razorpay'])->name('pay-online');
Route::get('check-status/{id}', [StudioController::class, 'checkOrderStatus'])->name('checkOrderStatus');