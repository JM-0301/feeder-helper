<?php

namespace App\Imports\Modules\Mahasiswa;

use App\Models\FeederCacheProdi;

class Validator
{
    public static function validate(array $row): array
    {
        $errors = [];

        $nim = trim((string)($row['nim'] ?? ''));
        $nama = trim((string)($row['nama'] ?? ''));
        $jk = strtoupper(trim((string)($row['jenis_kelamin'] ?? '')));
        $kodeProdi = trim((string)($row['kode_prodi'] ?? ''));
        $semesterMasuk = trim((string)($row['id_semester_masuk'] ?? ''));

        if ($nim === '') $errors['nim'] = 'NIM wajib diisi';
        if ($nama === '') $errors['nama'] = 'Nama wajib diisi';
        if (!in_array($jk, ['L','P'], true)) $errors['jenis_kelamin'] = 'Jenis kelamin harus L atau P';
        if ($kodeProdi === '') $errors['kode_prodi'] = 'Kode prodi wajib diisi';
        if ($semesterMasuk === '') $errors['id_semester_masuk'] = 'Semester masuk wajib diisi';

        // tanggal lahir optional tapi kalau diisi harus format YYYY-MM-DD
        $tgl = trim((string)($row['tanggal_lahir'] ?? ''));
        if ($tgl !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
            $errors['tanggal_lahir'] = 'Format tanggal_lahir harus YYYY-MM-DD';
        }

        // ✅ Validasi prodi harus ada di cache feeder
        if ($kodeProdi !== '') {
            $exists = FeederCacheProdi::query()
                ->where('kode_prodi', $kodeProdi)
                ->exists();

            if (!$exists) {
                $errors['kode_prodi'] =
                    "Kode prodi '{$kodeProdi}' tidak ditemukan di cache Feeder. " .
                    "Silakan buka Settings → Refresh Feeder Data.";
            }
        }

        return $errors;
    }
}
