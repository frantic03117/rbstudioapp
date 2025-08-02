<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class BookingExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $type;

    public function __construct($type = null)
    {
        $this->type = $type;
    }

    public function collection()
    {
        $now = now();

        $items = Booking::where('bookings.id', '>', '0');
        $items->when($this->type == 'upcoming', fn($q) => $q->whereDate('booking_start_date', '>', $now))
            ->when($this->type == 'today', fn($q) => $q->whereDate('booking_start_date', $now->toDateString()))
            ->when($this->type == 'past', fn($q) => $q->whereDate('booking_start_date', '<', $now));
        $items->with('vendor')->with('service:id,name,icon,approval_required')->with('user:id,name,email,mobile')->with('studio:id,name,mobile,address');
        $items->with('rents')->with('transactions')->withSum('transactions', 'amount');
        $items->with('creater:id,name,email');
        $bookings = $items->get();
        return $bookings->map(function ($b, $i) {
            $rentcharge = $b->rents->sum(function ($r) {
                return $r->pivot->charge * $r->pivot->uses_hours;
            });
            $total = ($rentcharge + $b->duration * $b->studio_charge) * 1.18;
            return [
                'Sr No.' => $i + 1,
                'User Name' => $b->user->name ?? 'N/A',
                'Email' => $b->user->email ?? 'N/A',
                'Studio' => $b->studio->name ?? 'N/A',
                'Service' => $b->service->name ?? 'N/A',
                'Start Time' => Carbon::parse($b->booking_start_date)->format('d/m/Y h:i A'),
                'End Time' => Carbon::parse($b->booking_end_date)->format('d/m/Y h:i A'),
                'Duration' => $b->duration . ' hours',
                'Status' => $b->booking_status == 0 ? 'Pending' : ($b->booking_status == 1 ? 'Confirmed' : 'Cancelled'),
                'Total Amount' => $total,
                'Paid Amount' => $b->transactions_sum_amount ?? 0,
                'Remaining Amount' => $total - ($b->transactions_sum_amount ?? 0)
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Sr No.',
            'User Name',
            'Email',
            'Studio',
            'Service',
            'Start Time',
            'End Time',
            'Duration',
            'Status',
            'Total Amount',
            'Paid Amount',
            'Remaining Amount'
        ];
    }
}
