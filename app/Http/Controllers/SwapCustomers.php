<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SwapCustomers extends Controller
{
    public function swapCustomers()
    {
        if (auth()->user()->role == 'superAdmin' || auth()->user()->role == 'admin') {
            return view('Swap.swapCustomers');
        }else {
            return redirect()->route('login')->with('error', 'You do not have permission to access this page.');
        }
    }
}
