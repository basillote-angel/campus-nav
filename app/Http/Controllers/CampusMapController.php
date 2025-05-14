<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CampusMapController extends Controller
{
    public function index() {
        return view('campus-map');
    }
}
