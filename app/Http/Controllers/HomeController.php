<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        if (app()->bound('current_hotel')) {
            return redirect()->route('hotels.show', app('current_hotel'));
        }

        return view('home');
    }
}
