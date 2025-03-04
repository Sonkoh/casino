<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get("/", function () {
    if (Auth::check()) {
        return view("home", ["title" => "Inicio"]);
    }
    return view("auth", ["title" => "Log In"]);
});

Route::get("/auth", [AuthController::class, "google_auth"]);
Route::get("/auth/callback", [AuthController::class, "google_callback"]);
Route::get("/auth/logout", [AuthController::class, "logout"]);

Route::middleware(['auth'])->group(function () {
    // ...
});
