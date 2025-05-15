<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $query = Auth::user()->orders()->with([
            'seller',
            'address',
            'items',
            'lastStatus',
        ]);
    }
}
