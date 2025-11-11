<?php

namespace App\Models;

use App\Models\Studio\Studio;
use App\Models\Studio\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'studio_id',
        'vendor_id',
        'service_id',
        'bill_no',
        'booking_start_date',
        'booking_end_date',
        'start_at',
        'end_at',
        'duration',
        'payment_status',
        'discount',
        'discount_type',
        'refunded',
        'booking_status',
        'studio_charge',
        'partial_percent',
        'promo_id',
        'promo_code',
        'promo_discount_calculated',
        'approved_at',
        'created_by',
        'created_at',
        'tds_allowed'
    ];

    protected $appends = [
        'studio_charge_sum',
        'rent_charges',
        'extra_charge',
        'discount_total',
        'net_total',
        'gst_sum',
        'total_amount',
        'paid_sum',
        'balance',
        'cancel_time',
        'tds_sum'

    ];

    protected $extra_charge_per_hour = 200;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class)
            ->join("users", "users.vendor_id", "=", "vendors.id")
            ->where("users.role", "Admin")
            ->select('vendors.*', 'users.email', 'users.mobile');
    }

    public function studio()
    {
        return $this->belongsTo(Studio::class)->with('images');
    }

    public function rents()
    {
        return $this->belongsToMany(Rent::class, BookingItem::class, 'booking_id', 'item_id')
            ->withPivot('charge', 'uses_hours');
    }

    public function creater()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function gst()
    {
        return $this->hasOne(BookingGst::class, 'id', 'gst_id')
            ->with('state')
            ->with('city');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'booking_id', 'id')
            ->where('status', 'Success');
    }

    public function extra_added()
    {
        return $this->hasMany(ExtraBookingAmount::class, 'booking_id', 'id');
    }
    public function buffer_slot()
    {
        return $this->hasMany(BlockedSlot::class, 'booking_id', 'id')->where('reason', 'buffer')->orderBy('id', 'DESC');
    }
    /*
    |--------------------------------------------------------------------------
    | Computed Attributes
    |--------------------------------------------------------------------------
    */

    // ✅ Base studio charge
    public function getStudioChargeSumAttribute()
    {
        return $this->duration * $this->studio_charge;
    }

    // ✅ Rent charges
    public function getRentChargesAttribute()
    {
        return $this->rents->sum(fn($r) => $r->pivot->charge * $r->pivot->uses_hours);
    }

    // ✅ Extra charge (night hours)
    public function getExtraChargeAttribute()
    {
        $extra_hours = 0;

        $start_time = strtotime($this->booking_start_date);
        $end_time   = strtotime($this->booking_end_date);

        $night_start = strtotime(date('Y-m-d', $start_time) . ' 23:00:00');
        $morning_end = strtotime(date('Y-m-d', $start_time) . ' 08:00:00');

        if ($start_time >= $night_start) {
            $morning_end += 86400; // Next day's 8 AM
        }

        while ($start_time < $end_time) {
            if ($start_time >= $night_start || $start_time < $morning_end) {
                $extra_hours++;
            }
            $start_time = strtotime('+1 hour', $start_time);
        }

        return ($extra_hours > 0) ? $extra_hours * $this->extra_charge_per_hour : 0;
    }

    // ✅ Subtotal
    public function getSubtotalAttribute()
    {
        $extra_added_sum = $this->extra_added()->sum('amount'); // using relation
        return $this->studio_charge_sum + $this->extra_charge + $extra_added_sum + $this->rent_charges;
    }

    // ✅ Discount total
    public function getDiscountTotalAttribute()
    {
        $discount_amount = 0;

        if ($this->discount_type === 'Fixed') {
            $discount_amount = $this->discount;
        } elseif ($this->discount_type === 'Percent') {
            $discount_amount = ($this->subtotal * $this->discount) / 100;
        }

        if ($discount_amount > $this->subtotal) {
            $discount_amount = $this->subtotal;
        }

        return $discount_amount + floatval($this->promo_discount_calculated);
    }

    // ✅ Net total
    public function getNetTotalAttribute()
    {
        return $this->subtotal - $this->discount_total;
    }
    public function getTdsSumAttribute()
    {
        return $this->tds_allowed == "1" ?  $this->net_total * 0.10 : 0;
    }

    // ✅ GST (default 18%)
    public function getGstSumAttribute()
    {
        return $this->net_total * 0.18;
    }

    // ✅ Final total with GST
    public function getTotalAmountAttribute()
    {
        return round($this->net_total * 1.18, 2);
    }
    public function getPaidSumAttribute()
    {
        return $this->transactions()->where('type', 'Credit')->sum('amount');
    }
    public function getBalanceAttribute()
    {
        return round($this->total_amount - $this->paid_sum, 2);
    }
    public function getCancelTimeAttribute($value)
    {
        $endtime = Setting::where('id', '2')->select('col_val')->first();
        $minutes = $endtime->col_val;
        return Carbon::parse($this->created_at)
            ->addMinutes($minutes)
            ->format('Y-m-d H:i:s');
    }
    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)
            ->timezone('Asia/Kolkata')
            ->toDateTimeString();
    }
    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)
            ->timezone('Asia/Kolkata')
            ->toDateTimeString();
    }
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($booking) {
            // Only assign bill_no the first time booking_status becomes 1
            if ($booking->booking_status == '1' && empty($booking->bill_no)) {
                $booking->bill_no = self::generateBillNo();
            }
        });
    }

    /**
     * Generate bill number based on financial year
     *
     * Format: YY-YY/{running_number}
     * Example: 24-25/1, 24-25/2, 25-26/1
     */
    public static function generateBillNo()
    {
        // Determine current financial year (April → March)
        $year = now()->month >= 4
            ? now()->year
            : now()->year - 1;

        $nextYear = $year + 1;

        // Example: "24-25"
        $prefix = substr($year, -2) . '-' . substr($nextYear, -2);

        // Count existing bills with this prefix
        $count = self::where('bill_no', 'like', "$prefix/%")->count();

        // Bill number = prefix + next number
        return $prefix . '/' . ($count + 1);
    }
}