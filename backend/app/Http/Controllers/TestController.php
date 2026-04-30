<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function staticPage()
    {
        return view('test-static');
    }
}
