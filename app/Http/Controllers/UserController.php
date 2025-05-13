<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function showManageUsers() {
        return view('manage-users');
    }

    public function update(Request $request) {

    }
}
