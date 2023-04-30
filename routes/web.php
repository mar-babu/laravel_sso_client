<?php

use Illuminate\Support\Facades\Route;
use \Illuminate\Http\Request;
use Illuminate\Support\Str;
use \Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function (Request $request) {
    $request->session()->put("state", $state = Str::random(40));
    $query = http_build_query([
        "client_id" => "990e2d78-fc55-4382-9032-5022ba6c1a40",
        "redirect_url" => "http://127.0.0.1:8080/callback",
        "response_type" => "code",
        "scope" => "",
        "state" => $state,
    ]);
    return redirect("http://127.0.0.1:8000/oauth/authorize?" . $query);
});

Route::get('/callback', function (Request $request) {
    $state = $request->session()->pull("state");

    throw_unless(strlen($state) > 0 && $state == $request->state,
    InvalidArgumentException::class);

    $response = Http::asForm()->post(
        "http://127.0.0.1:8000/oauth/token/",
        [
        "grant_type" => "authorization_code",
        "client_id" => "990e2d78-fc55-4382-9032-5022ba6c1a40",
        "client_secret" => "kUYg8ze5Ajg7h6Z1AcC53V6aGrcArnjfZ1vWee6F",
        "redirect_url" => "http://127.0.0.1:8080/callback",
        "code" => $request->code

    ]);
    return $response->json();
});
