<?php

namespace App\Http\Controllers;

use Auth;
use App\EventLog;
use App\Location;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $locations = Location::orderBy('title')->get();
        return view('location.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $location = new Location;
        return view('location.create', compact('location'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->is_default == 1) {
            Location::where('is_default', '=', 1)->update(['is_default' => 0]);
        }
        $location = new Location;
        $location->title = $request->title;
        $location->is_default = $request->is_default;
        $location->save();
        EventLog::log(Auth::user()->id, "Created location {$location->title}");
        return redirect()->action('LocationController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $location = Location::findOrFail($id);
        return view('location.edit', compact('location'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($request->is_default == 1) {
            Location::where('is_default', '=', 1)->update(['is_default' => 0]);
        }
        $location = Location::findOrFail($id);
        $location->title = $request->title;
        $location->is_default = $request->is_default;
        $location->save();
        EventLog::log(Auth::user()->id, "Updated location {$location->title}");
        return redirect()->action('LocationController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        EventLog::log(Auth::user()->id, "Deleted location {$location->title}");
        $location->delete();
        return redirect()->action('LocationController@index');
    }
}
