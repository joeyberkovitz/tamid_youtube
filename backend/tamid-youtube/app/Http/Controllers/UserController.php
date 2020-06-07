<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Registers a new user
    public function register(Request $request){
        // Validation of inputs
        $valDat = $request->validate([
            'name' => 'required',
            'email' => 'bail|required|email',
            'password' => 'required'
        ]);

        // If user exists, fail with 409 error
        if(User::query()->where('email', '=', $valDat['email'])->get()->count() > 0){
            abort(409, 'Error, user already exists');
        }

        // Hash password using configured Hash provider (config/hashing.php)
        $valDat['password'] = Hash::make($valDat['password']);
        // Insert and save new user
        $user = new User($valDat);
        $user->save();

        return $user;
    }
}
