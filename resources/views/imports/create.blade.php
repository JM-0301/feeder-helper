<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Import</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">

                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Module</div>
                        <div class="text-lg font-semibold">{{ strtoupper($module) }}</div>
                    </div>

                    <a href="{{ route('imports.template', $module) }}"
                       class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50">
                        Download Template
                    </a>
                </div>

                <div class="rounded-xl bg-gray-50 border p-4">
                    <div class="text-sm font-medium text-gray-700 mb-2">Kolom template:</div>
                    <div class="text-sm text-gray-600 break-words">
                        {{ implode(', ', $headings) }}
                    </div>
                </div>

                <div class="text-sm text-gray-600">
                    <span class="font-medium">Catatan:</span>
                    Pastikan Anda sudah melakukan <span class="font-medium">Settings → Refresh Feeder Data</span>
                    agar validasi <span class="font-medium">kode_prodi</span> bisa dicek ke cache.
                </div>


                <form method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="module" value="mahasiswa">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">File (XLSX/CSV)</label>
                        <input type="file" name="file" class="mt-1 block w-full" required>
                        @error('file')
                            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-black text-white">
                        Upload & Validate
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
