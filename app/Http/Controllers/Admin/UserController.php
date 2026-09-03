<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        return view('admin.users.index', [
            'users' => User::latest()->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'email'=>'required|email|unique:users',
            'role'=>'required'
        ]);

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'role'=>$request->role,
            'password'=>Hash::make('password')
        ]);

        logAktivitas(
            'User',
            'Menambahkan user #'.$user->id.' '.$user->name.' ('.$user->email.') dengan role '.$user->role
        );

        return back()->with('success','User berhasil dibuat');
    }
}
