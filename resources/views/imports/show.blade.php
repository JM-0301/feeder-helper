<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Import #{{ $batch->id }} ({{ $batch->module }})
            </h2>
            <a href="{{ route('imports.index') }}" class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('status'))
                <div class="rounded-xl bg-green-50 border border-green-200 p-3 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="text-sm text-gray-500">File</div>
                    <div class="mt-1 font-semibold break-words">{{ $batch->filename }}</div>
                </div>
                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="text-sm text-gray-500">Total Rows</div>
                    <div class="mt-1 text-2xl font-bold">{{ $batch->total_rows }}</div>
                </div>
                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="text-sm text-gray-500">Valid</div>
                    <div class="mt-1 text-2xl font-bold">{{ $batch->valid_rows }}</div>
                </div>
                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="text-sm text-gray-500">Invalid</div>
                    <div class="mt-1 text-2xl font-bold">{{ $batch->invalid_rows }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500 border-b">
                        <tr>
                            <th class="py-2 pr-4">Row</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">NIM</th>
                            <th class="py-2 pr-4">Nama</th>
                            <th class="py-2 pr-4">Prodi</th>
                            <th class="py-2 pr-4">Errors</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($rows as $r)
                            @php
                                $d = $r->data_json ?? [];
                                $e = $r->error_json ?? [];
                            @endphp
                            <tr>
                                <td class="py-2 pr-4">{{ $r->row_number }}</td>
                                <td class="py-2 pr-4">
                                    <span class="px-2 py-1 rounded-lg text-xs {{ $r->status === 'valid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $r->status }}
                                    </span>
                                </td>
                                <td class="py-2 pr-4">{{ $d['nim'] ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ $d['nama'] ?? '-' }}</td>
                                <td class="py-2 pr-4">{{ $d['kode_prodi'] ?? '-' }}</td>
                                <td class="py-2 pr-4">
                                    @if(!empty($e))
                                        <ul class="list-disc pl-4 text-red-700">
                                            @foreach($e as $k => $msg)
                                                <li><span class="font-medium">{{ $k }}:</span> {{ $msg }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-3 text-gray-500">Tidak ada row.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">{{ $rows->links() }}</div>
            </div>

        </div>
    </div>
</x-app-layout>
