<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $orderCount = 100;
        $userCount = User::count();

        return view('dashboard.index', compact('orderCount', 'userCount'));
    }
}
