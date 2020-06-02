<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request){
        $valDat = $request->validate([
            'name' => 'required',
            'email' => 'bail|required|email',
            'password' => 'required'
        ]);

        if(User::query()->where('email', '=', $valDat['email'])->get()->count() > 0){
            abort(409, 'Error, user already exists');
        }

        $valDat['password'] = Hash::make($valDat['password']);
        $user = new User($valDat);
        $user->save();

        return $user;
    }
}
