<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeederSettingsController;
use App\Models\FeederCacheProfilPt;
use App\Models\FeederCacheProdi;
use App\Models\FeederSyncRun;
use App\Models\FeederStatsSnapshot;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::view('/imports', 'imports.index')->name('imports.index');
    Route::view('/sync-logs', 'sync.logs')->name('sync.logs');
    Route::view('/settings/feeder', 'settings.feeder')->name('settings.feeder');
});

Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/settings/feeder', [FeederSettingsController::class, 'edit'])
        ->name('settings.feeder');

    Route::post('/settings/feeder', [FeederSettingsController::class, 'upsert'])
        ->name('settings.feeder.save');
});

Route::post('/settings/feeder/refresh', [\App\Http\Controllers\FeederSettingsController::class, 'refreshCache'])
    ->name('settings.feeder.refresh');

Route::get('/dashboard', function () {
    $profil = FeederCacheProfilPt::find(1)?->data_json;

    // Nama PT: dibuat fleksibel karena format feeder bisa beda
    $ptName =
        $profil['data']['nama_perguruan_tinggi'] ??
        $profil['data']['nm_pt'] ??
        $profil['nama_perguruan_tinggi'] ??
        $profil['nm_pt'] ??
        null;

    $prodiCount = FeederCacheProdi::count();

    $lastRun = FeederSyncRun::query()
        ->where('type', 'refresh_cache')
        ->latest('id')
        ->first();

    // Distribusi Prodi per jenjang (S1/S2/D3 dst)
    $prodiByJenjang = FeederCacheProdi::query()
        ->selectRaw("COALESCE(NULLIF(TRIM(jenjang), ''), 'Unknown') as jenjang, COUNT(*) as total")
        ->groupBy('jenjang')
        ->orderByDesc('total')
        ->get();

    // Distribusi status prodi (aktif/nonaktif/unknown)
    $prodiByStatus = FeederCacheProdi::query()
        ->selectRaw("COALESCE(NULLIF(TRIM(status), ''), 'Unknown') as status, COUNT(*) as total")
        ->groupBy('status')
        ->orderByDesc('total')
        ->get();

    // Daftar prodi contoh (10 pertama by nama)
    $prodiSample = FeederCacheProdi::query()
        ->orderBy('nama_prodi')
        ->limit(10)
        ->get(['kode_prodi', 'nama_prodi', 'jenjang', 'status', 'synced_at']);

    $latestStats = FeederStatsSnapshot::query()->latest('snapshot_date')->first();
    $totalMahasiswa = $latestStats?->total_mahasiswa;
    $statsSyncedAt = $latestStats?->synced_at;

    return view('dashboard', compact(
    'ptName','prodiCount','lastRun',
    'prodiByJenjang','prodiByStatus','prodiSample',
    'totalMahasiswa','statsSyncedAt'
    ));
})->name('dashboard');


$authRoutes = __DIR__ . '/auth.php';
if (file_exists($authRoutes)) {
    require $authRoutes;
}