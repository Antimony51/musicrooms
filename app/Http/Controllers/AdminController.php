<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\User;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showUserList()
    {
        $users = User::get();

        return view('admin.userlist', compact('users'));
    }
}
