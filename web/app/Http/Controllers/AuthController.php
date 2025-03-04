<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    function logout()
    {
        Auth::logout();
        return redirect("/");
    }

    function google_auth()
    {
        return Socialite::driver("google")->redirect();
    }

    function google_callback()
    {
        // try {
            $google = Socialite::driver("google")->user();
            $user = User::where("email", $google->email)->firstOr(function () use ($google) {
                $u = new User();
                $u->username = $google->name;
                $u->firstname = $google->user["given_name"];
                $u->lastname = $google->user["family_name"] ?? '';
                $u->email = $google->email;
                $u->balance = 0;
                $u->save();
                return $u;
            });
            Auth::login($user);
            return redirect("/");
        // } catch (\Throwable $e) {
        //     return abort(404);
        // }
    }
}
