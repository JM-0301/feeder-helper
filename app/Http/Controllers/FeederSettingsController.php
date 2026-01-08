<?php

namespace App\Http\Controllers;

use App\Models\FeederSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\FeederCacheProfilPt;
use App\Models\FeederCacheProdi;
use App\Models\FeederSyncRun;
use App\Services\Feeder\FeederClient;
use App\Models\FeederStatsSnapshot;

class FeederSettingsController extends Controller
{
    public function refreshCache()
    {
       $today = now()->toDateString();
        $mh = $client->fetchMahasiswaCount();

        FeederStatsSnapshot::updateOrCreate(
            ['snapshot_date' => $today],
            [
                'total_mahasiswa' => $mh['count'], // bisa null kalau response belum ada total
                'raw_json' => [
                    'act' => $mh['act'] ?? null,
                    'response' => $mh['raw'] ?? null,
                ],
                'synced_at' => now(),
            ]
        );
       
        $run = FeederSyncRun::create([
            'type' => 'refresh_cache',
            'success' => false,
            'started_at' => now(),
        ]);

        try {
            $client = FeederClient::fromDbOrEnv();

            // 1) Profil PT
            $profil = $client->fetchProfilPt();
            FeederCacheProfilPt::updateOrCreate(
                ['id' => 1],
                ['data_json' => $profil, 'synced_at' => now()]
            );

            // 2) Prodi
            $prodiResp = $client->fetchProdiList();

            $items = $prodiResp['data'] ?? $prodiResp['datas'] ?? $prodiResp['list'] ?? null;
            if (!is_array($items)) {
                // kalau bentuknya bukan list, simpan mentah aja biar kita lihat strukturnya
                $items = [];
            }

            $syncedCount = 0;

            foreach ($items as $row) {
                if (!is_array($row)) continue;

                // Mapping fleksibel (karena key feeder bisa beda)
                $idProdi = $row['id_prodi'] ?? $row['id_prodi_feeder'] ?? $row['id_program_studi'] ?? $row['id'] ?? null;
                if (!$idProdi) continue;

                $kode = $row['kode_program_studi'] ?? $row['kode_prodi'] ?? $row['kode'] ?? null;
                $nama = $row['nama_program_studi'] ?? $row['nama_prodi'] ?? $row['nama'] ?? null;
                $jenjang = $row['nama_jenjang_pendidikan'] ?? $row['jenjang'] ?? $row['id_jenjang_pendidikan'] ?? null;
                $status = $row['status'] ?? $row['status_prodi'] ?? null;

                FeederCacheProdi::updateOrCreate(
                    ['id_prodi_feeder' => (string) $idProdi],
                    [
                        'kode_prodi' => $kode,
                        'nama_prodi' => $nama,
                        'jenjang' => is_scalar($jenjang) ? (string) $jenjang : null,
                        'status' => is_scalar($status) ? (string) $status : null,
                        'data_json' => $row,
                        'synced_at' => now(),
                    ]
                );

                $syncedCount++;
            }

            $run->update([
                'success' => true,
                'message' => 'Refresh cache berhasil.',
                'meta_json' => [
                    'prodi_synced' => $syncedCount,
                ],
                'finished_at' => now(),
            ]);

            return redirect()->route('settings.feeder')->with('status', "Refresh berhasil. Prodi tersinkron: {$syncedCount}");
        } catch (\Throwable $e) {
            $run->update([
                'success' => false,
                'message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            return redirect()->route('settings.feeder')->with('error', 'Refresh gagal: '.$e->getMessage());
        }
    }
    
    public function edit()
    {
        $setting = FeederSetting::query()->latest('id')->first();
        $lastRun = FeederSyncRun::query()->where('type','refresh_cache')->latest('id')->first();
        $prodiCount = FeederCacheProdi::count();

        return view('settings.feeder', compact('setting','lastRun','prodiCount'));
    }

    public function upsert(Request $request)
    {
        $data = $request->validate([
            'ws_url'   => ['required', 'url'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'timeout'  => ['required', 'integer', 'min:5', 'max:120'],
            'submit'   => ['required', 'in:save,test'],
        ]);

        if ($data['submit'] === 'test') {
            return $this->testConnection(
                $data['ws_url'],
                $data['username'] ?? null,
                $data['password'] ?? null,
                (int) $data['timeout']
            )->withInput();
        }

        // SAVE
        $setting = FeederSetting::query()->latest('id')->first() ?? new FeederSetting();

        $setting->ws_url = $data['ws_url'];
        $setting->username = $data['username'] ?? null;
        $setting->timeout = (int) $data['timeout'];

        // password: kalau kosong, jangan overwrite yang lama
        if (!empty($data['password'])) {
            $setting->password = $data['password'];
        }

        $setting->save();

        return redirect()
            ->route('settings.feeder')
            ->with('status', 'Settings berhasil disimpan.');
    }

    private function testConnection(string $wsUrl, ?string $username, ?string $password, int $timeout)
    {
        if (!$username || !$password) {
            return back()->with('error', 'Username & password wajib diisi untuk test koneksi.');
        }

        try {
            // Test paling aman: coba GetToken
            $res = Http::timeout($timeout)
                ->acceptJson()
                ->post($wsUrl, [
                    'act' => 'GetToken',
                    'username' => $username,
                    'password' => $password,
                ]);

            $json = $res->json();

            // Banyak implementasi feeder balas: { error_code, error_desc, data }
            $ok = $res->ok() && is_array($json) && (($json['error_code'] ?? null) === 0) && !empty($json['data']);

            if ($ok) {
                return back()->with('status', 'Test connection berarti: BERHASIL (token diterima).');
            }

            return back()->with('error', 'Test connection gagal. Response: ' . (is_string($res->body()) ? $res->body() : json_encode($json)));
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal akses endpoint. Error: ' . $e->getMessage());
        }
    }

}
