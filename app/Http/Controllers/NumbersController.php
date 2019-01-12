<?php

namespace App\Http\Controllers;

use function App\quick_sort;
use DB;

class NumbersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    public function index()
    {
        set_time_limit(180);

        $numbers = DB::table('numbers')->pluck('number')->toArray();
        $ordered_numbers = quick_sort($numbers);
        
        return response()->json(['numbers' => $ordered_numbers], 200);
    }
}
