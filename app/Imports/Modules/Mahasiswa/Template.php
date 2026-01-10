<?php

namespace App\Imports\Modules\Mahasiswa;

class Template
{
    public static function headings(): array
    {
        return [
            'nim',
            'nama',
            'jenis_kelamin',        // L/P
            'tempat_lahir',
            'tanggal_lahir',        // YYYY-MM-DD
            'email',
            'no_hp',
            'kode_prodi',           // kode internal/feeder
            'id_semester_masuk',    // contoh 20231
            'status_awal',          // Baru/Pindahan dll (opsional)
        ];
    }
}
