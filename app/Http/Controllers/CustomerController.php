<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::paginate(10);  // 10 items per page
        return view('customers.index', compact('customers'));
    }
}
