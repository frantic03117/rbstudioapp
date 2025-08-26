<?php

namespace App\Observers;

use App\Models\RbNotification;
use App\Models\User;
use App\Traits\RbTrait;
use Illuminate\Support\Facades\Log;

class RbNotificationObserver
{
    use RbTrait;
    /**
     * Handle the RbNotification "created" event.
     *
     * @param  \App\Models\RbNotification  $rbNotification
     * @return void
     */
    public function created(RbNotification $rbNotification)
    {
        $rbNotification->load(['user', 'booking', 'studio']);
        Log::info($rbNotification);
        if ($rbNotification->type !== "Booking") {
            return;
        }

        /**
         * Case 1: Notification for USER (shown_to_user = 1)
         */
        if ($rbNotification->shown_to_user == "1") {
            $user = $rbNotification->user;
            Log::info($user);
            if ($user && $user->fcm_token) {
                Log::info($user->fcm_token);
                $data = [
                    'notification_id' => (string) $rbNotification->id,
                    'type'            => $rbNotification->type ?? 'General',
                    'studio'          => $rbNotification->studio?->name ?? '',
                    'booking_id'      => (string) $rbNotification->booking_id,
                ];

                $this->send_notification(
                    $user->fcm_token,
                    $rbNotification->title,
                    $rbNotification->message,
                    $user->id,
                    $rbNotification->type,
                    $data
                );
            }
        }

        /**
         * Case 2: Notification for ADMIN (shown_to_user = 0)
         */
        if ($rbNotification->shown_to_user == "0") {
            $super = User::where('role', 'Super')->first();

            if ($super && $super->fcm_token) {
                $data = [
                    'notification_id' => (string) $rbNotification->id,
                    'type'            => $rbNotification->type ?? 'General',
                    'studio'          => $rbNotification->studio?->name ?? '',
                    'booking_id'      => (string) $rbNotification->booking_id,
                ];

                $this->send_notification(
                    $super->fcm_token,
                    $rbNotification->title,
                    $rbNotification->message,
                    $super->id,
                    $rbNotification->type,
                    $data
                );
            }
        }
    }

    /**
     * Handle the RbNotification "updated" event.
     *
     * @param  \App\Models\RbNotification  $rbNotification
     * @return void
     */
    public function updated(RbNotification $rbNotification)
    {
        //
    }

    /**
     * Handle the RbNotification "deleted" event.
     *
     * @param  \App\Models\RbNotification  $rbNotification
     * @return void
     */
    public function deleted(RbNotification $rbNotification)
    {
        //
    }

    /**
     * Handle the RbNotification "restored" event.
     *
     * @param  \App\Models\RbNotification  $rbNotification
     * @return void
     */
    public function restored(RbNotification $rbNotification)
    {
        //
    }

    /**
     * Handle the RbNotification "force deleted" event.
     *
     * @param  \App\Models\RbNotification  $rbNotification
     * @return void
     */
    public function forceDeleted(RbNotification $rbNotification)
    {
        //
    }
}
