<?php

namespace App\Http\Controllers;

use App\Models\EventLog;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventLogController extends Controller
{
    public function index()
    {
        $cutoff = Carbon::create()->subMonths(3);
        $events = EventLog::where('created_at', '>', $cutoff)->with('user')->orderBy('created_at', 'DESC')->get();

        return view('event.index', compact('events'));
    }
}
