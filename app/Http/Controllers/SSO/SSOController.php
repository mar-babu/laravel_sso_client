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
        session()->put("state", $state = Str::random(40));
        $query = http_build_query([
            "client_id" => "9913ab72-debb-41bd-b3fd-37d8016d00b5",
            "redirect_uri" => "http://127.0.0.1:8080/auth/callback",
            "response_type" => "code",
            "scope" => "view-user",
            "state" => $state,
        ]);
        return redirect("http://127.0.0.1:8000/oauth/authorize?" . $query);

    }
    public function getCallback(Request $request)
    {
        $state = session()->pull("state");

        throw_unless(strlen($state) > 0 && $state === $request->state, InvalidArgumentException::class);

        $response = Http::asForm()->post(
            "http://127.0.0.1:8000/oauth/token",
            [
                "grant_type" => "authorization_code",
                "client_id" => "9913ab72-debb-41bd-b3fd-37d8016d00b5",
                "client_secret" => "4ks5wV6X5W2FXIW0ZBFopkjLQsWkkQHE7fCGYCSi",
                "redirect_uri" => "http://127.0.0.1:8080/auth/callback",
                "code" => $request->code

            ]);
        session()->put($response->json());
        return redirect(route('sso.connect'));

    }
    public function connectUser(Request $request)
    {
        $accessToken = session()->get('access_token');
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$accessToken
        ])->get('http://127.0.0.1:8000/api/user');
        return $response->json();

    }

}
