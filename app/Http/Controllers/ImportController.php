<?php

namespace App\Http\Controllers;

use App\Imports\GenericHeadingImport;
use App\Imports\Modules\Mahasiswa\Template as MahasiswaTemplate;
use App\Imports\Modules\Mahasiswa\Validator as MahasiswaValidator;
use App\Models\ImportBatch;
use App\Models\ImportRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Imports\Resolvers\ProdiResolver;


class ImportController extends Controller
{
    public function index()
    {
        $batches = ImportBatch::query()->latest('id')->paginate(15);
        return view('imports.index', compact('batches'));
    }

    public function create()
    {
        // sementara modul pertama fixed: mahasiswa
        $module = 'mahasiswa';
        $headings = MahasiswaTemplate::headings();

        return view('imports.create', compact('module','headings'));
    }

    public function downloadTemplate(string $module): StreamedResponse
    {
        if ($module !== 'mahasiswa') abort(404);

        $headings = MahasiswaTemplate::headings();

        // CSV sederhana (paling kompatibel & cepat). Nanti bisa XLSX.
        $filename = "template-{$module}.csv";

        return response()->streamDownload(function () use ($headings) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headings);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'module' => ['required', 'in:mahasiswa'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:51200'],
        ]);

        $file = $data['file'];
        $module = $data['module'];

        $storedPath = $file->store('imports/raw');

        $batch = ImportBatch::create([
            'module' => $module,
            'filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'status' => 'uploaded',
            'created_by' => $request->user()?->id,
        ]);

        // parse + validate + staging
        DB::transaction(function () use ($batch, $storedPath) {
            $import = new GenericHeadingImport();
            $rows = Excel::toArray($import, Storage::path($storedPath));
            $sheet = $rows[0] ?? [];

            $total = 0; $valid = 0; $invalid = 0;

            foreach ($sheet as $i => $row) {
                // WithHeadingRow: $row sudah associative by header
                $total++;
                $rowNumber = $i + 2; // asumsi row 1 header

                // normalisasi key ke string
                $payload = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row);

                // ✅ resolve id_prodi_feeder dari kode_prodi (kalau ada di cache)
                $kodeProdi = (string)($payload['kode_prodi'] ?? '');
                $idProdiFeeder = ProdiResolver::resolveIdByKode($kodeProdi);
                if ($idProdiFeeder) {
                    $payload['id_prodi_feeder'] = $idProdiFeeder;
                }

                $errors = MahasiswaValidator::validate($payload);
                $status = empty($errors) ? 'valid' : 'invalid';

                if ($status === 'valid') $valid++; else $invalid++;

                ImportRow::create([
                    'batch_id' => $batch->id,
                    'row_number' => $rowNumber,
                    'status' => $status,
                    'data_json' => $payload,
                    'error_json' => empty($errors) ? null : $errors,
                    'validated_at' => now(),
                ]);
            }

            $batch->update([
                'status' => 'validated',
                'total_rows' => $total,
                'valid_rows' => $valid,
                'invalid_rows' => $invalid,
            ]);
        });

        return redirect()->route('imports.show', $batch)->with('status', 'Import diproses. Silakan cek hasil validasi.');
    }

    public function show(ImportBatch $batch)
    {
        $rows = $batch->rows()->latest('id')->paginate(25);

        return view('imports.show', compact('batch', 'rows'));
    }
}
