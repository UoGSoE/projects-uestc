<?php

namespace App\Http\Controllers;

use App\EventLog;
use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EventLogController extends Controller
{

    public function index()
    {
        $cutoff = Carbon::create()->subMonths(3);
        $events = EventLog::where('created_at', '>', $cutoff)->orderBy('created_at', 'DESC')->get();
        return view('event.index', compact('events'));
    }
}
