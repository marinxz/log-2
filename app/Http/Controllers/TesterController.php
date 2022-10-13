<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
 
class TesterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tester');
    }

    public function store(Request $request)
    {
        $message = 'Logging';
        Log::info("in store");
        Log::info('Adding variable', ['app_record_id' => 123123123123]);
        return redirect('/tester')->with('message', $message);
    }

}