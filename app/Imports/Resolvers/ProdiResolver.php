<?php

namespace App\Imports\Resolvers;

use App\Models\FeederCacheProdi;

class ProdiResolver
{
    public static function resolveIdByKode(string $kodeProdi): ?string
    {
        $kodeProdi = trim($kodeProdi);
        if ($kodeProdi === '') return null;

        $prodi = FeederCacheProdi::query()
            ->where('kode_prodi', $kodeProdi)
            ->first(['id_prodi_feeder']);

        return $prodi?->id_prodi_feeder ? (string) $prodi->id_prodi_feeder : null;
    }
}
