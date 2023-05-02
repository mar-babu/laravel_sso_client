<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use \Illuminate\Support\Facades\Http;

class SSOController extends Controller
{
    public function getLogin(Request $request)
    {
        $request->session()->put("state", $state = Str::random(40));
        $query = http_build_query([
            "client_id" => "99118fdc-6765-404c-8dc6-08c37146e121",
            "redirect_url" => "http://127.0.0.1:8080/callback",
            "response_type" => "code",
            "scope" => "view-user",
            "state" => $state,
        ]);
        return redirect("http://127.0.0.1:8000/oauth/authorize?" . $query);

    }
    public function getCallback(Request $request)
    {
        $state = $request->session()->pull("state");

        throw_unless(strlen($state) > 0 && $state === $request->state, InvalidArgumentException::class);

        $response = Http::asForm()->post(
            "http://127.0.0.1:8000/oauth/token/",
            [
                "grant_type" => "authorization_code",
                "client_id" => "99118fdc-6765-404c-8dc6-08c37146e121",
                "client_secret" => "hHQ3hGYpgAQEwZvVIYieG8f2s3s3Bteq8AOQjd14",
                "redirect_url" => "http://127.0.0.1:8080/callback",
                "code" => $request->code

            ]);
        $request->session()->put($response->json());
        return redirect(route('sso.connect'));

    }
    public function connectUser(Request $request)
    {
        $accessToken = $request->session()->get('access_token');
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer'.$accessToken
        ])->get('http://127.0.0.1:8000/api/user');
        return $response->json();

    }

}
