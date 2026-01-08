<?php

namespace App\Services\Feeder;

use App\Models\FeederSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FeederClient
{
    public function __construct(private readonly FeederSetting $setting) {}

    public static function fromDbOrEnv(): self
    {
        $s = FeederSetting::query()->latest('id')->first();

        // fallback kalau DB belum ada (optional)
        if (!$s) {
            $s = new FeederSetting([
                'ws_url' => config('feeder.ws_url'),
                'username' => config('feeder.username'),
                'password' => config('feeder.password'),
                'timeout' => config('feeder.timeout', 30),
            ]);
        }

        return new self($s);
    }

    public function getToken(): string
    {
        $ws = $this->setting->ws_url;
        $u  = (string) ($this->setting->username ?? '');
        $p  = (string) ($this->setting->password ?? '');
        $timeout = (int) ($this->setting->timeout ?? 30);

        if (!$ws || !$u || !$p) {
            throw new \RuntimeException('Feeder settings belum lengkap (URL/username/password).');
        }

        $cacheKey = 'feeder_token:' . sha1($ws.'|'.$u);
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($ws, $u, $p, $timeout) {
            $res = Http::timeout($timeout)->acceptJson()->post($ws, [
                'act' => 'GetToken',
                'username' => $u,
                'password' => $p,
            ]);

            $json = $res->json();

            // Umumnya feeder: error_code 0 + data berisi token
            $token = $json['data'] ?? null;

            if (!$res->ok() || empty($token) || (($json['error_code'] ?? 0) !== 0)) {
                throw new \RuntimeException('GetToken gagal: ' . ($res->body() ?: json_encode($json)));
            }

            return (string) $token;
        });
    }

    public function call(string $act, array $payload = []): array
    {
        $ws = $this->setting->ws_url;
        $timeout = (int) ($this->setting->timeout ?? 30);

        $token = $this->getToken();

        $res = Http::timeout($timeout)->acceptJson()->post($ws, array_merge([
            'act' => $act,
            'token' => $token,
        ], $payload));

        $json = $res->json();
        if (!is_array($json)) {
            throw new \RuntimeException("Response bukan JSON untuk act={$act}: " . $res->body());
        }

        if (!$res->ok() || (($json['error_code'] ?? 0) !== 0)) {
            throw new \RuntimeException("Act {$act} gagal: " . ($json['error_desc'] ?? $res->body()));
        }

        return $json;
    }

    /**
     * NOTE: Nama ACT bisa beda antar versi/konfigurasi feeder.
     * Di sini kita coba beberapa kandidat yang umum.
     */
    public function fetchProfilPt(): array
    {
        foreach (['GetProfilPT', 'GetProfilPt', 'GetProfilPerguruanTinggi'] as $act) {
            try {
                return $this->call($act);
            } catch (\Throwable $e) {
                // coba act berikutnya
            }
        }
        throw new \RuntimeException('Tidak menemukan ACT untuk Profil PT. Sesuaikan di fetchProfilPt().');
    }

    public function fetchProdiList(): array
    {
        foreach (['GetProdi', 'GetListProdi', 'GetListProgramStudi', 'GetProdiPT'] as $act) {
            try {
                return $this->call($act);
            } catch (\Throwable $e) {
                // coba act berikutnya
            }
        }
        throw new \RuntimeException('Tidak menemukan ACT untuk daftar Prodi. Sesuaikan di fetchProdiList().');
    }
    

    public function fetchMahasiswaCount(): array
    {
        // Kita coba beberapa ACT yang umum dipakai
        $acts = [
            'GetListMahasiswa',
            'GetMahasiswa',
            'GetListMahasiswaPT',
            'GetCountMahasiswa',
        ];

        foreach ($acts as $act) {
            try {
                // coba request ringan (limit 1) agar kita dapat metadata total
                $resp = $this->call($act, [
                    'offset' => 0,
                    'limit' => 1,
                ]);

                $count = $this->extractTotalCount($resp);
                if ($count !== null) {
                    return ['count' => $count, 'raw' => $resp, 'act' => $act];
                }

                // kalau tidak ada total, tetap simpan raw buat kita lihat formatnya
                return ['count' => null, 'raw' => $resp, 'act' => $act];
            } catch (\Throwable $e) {
                // coba act berikutnya
            }
        }

        throw new \RuntimeException('Tidak menemukan ACT untuk menghitung mahasiswa. Perlu sesuaikan fetchMahasiswaCount().');
    }

    private function extractTotalCount(array $resp): ?int
    {
        // beberapa format total yang sering muncul
        $candidates = [
            $resp['totaldata'] ?? null,
            $resp['total'] ?? null,
            $resp['jumlah_data'] ?? null,
            $resp['jumlah'] ?? null,
            $resp['data']['totaldata'] ?? null,
            $resp['data']['total'] ?? null,
            $resp['data']['jumlah_data'] ?? null,
            $resp['data']['jumlah'] ?? null,
        ];

        foreach ($candidates as $v) {
            if (is_numeric($v)) return (int) $v;
        }

        // kadang Feeder taruh total di meta
        if (isset($resp['meta']) && is_array($resp['meta'])) {
            foreach (['totaldata','total','jumlah_data','jumlah'] as $k) {
                if (isset($resp['meta'][$k]) && is_numeric($resp['meta'][$k])) {
                    return (int) $resp['meta'][$k];
                }
            }
        }

        return null;
    }
}
