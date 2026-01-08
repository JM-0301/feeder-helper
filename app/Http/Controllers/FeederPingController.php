<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FeederPingController extends Controller
{
    public function __invoke(Request $request)
    {
        $url = config('feeder.ws_url');

        if (!$url) {
            return response()->json([
                'ok' => false,
                'message' => 'FEEDER_WS_URL belum di-set di .env',
            ], 400);
        }

        // Ping sederhana: cek endpoint bisa diakses (bukan GetToken dulu)
        try {
            $res = Http::timeout(config('feeder.timeout'))
                ->acceptJson()
                ->post($url, ['act' => 'GetToken', 'username' => config('feeder.username'), 'password' => config('feeder.password')]);

            return response()->json([
                'ok' => $res->ok(),
                'http_status' => $res->status(),
                'response' => $res->json() ?? $res->body(),
            ], $res->ok() ? 200 : 502);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal akses Feeder WS',
                'error' => $e->getMessage(),
            ], 502);
        }
    }
}
