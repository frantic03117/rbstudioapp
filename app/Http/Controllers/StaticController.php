<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\RbNotification;
use App\Models\Vendor;
use App\Models\Studio\Service;
use App\Models\Studio\Studio;
use Illuminate\Support\Facades\DB;
use App\Mail\CustomMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class StaticController extends Controller
{
    public static function studio_css(){
        $items = Studio::select(['name','color'])->get();
        return $items;
    }
}