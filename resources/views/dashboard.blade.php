<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>

            <a href="{{ route('settings.feeder') }}"
               class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50">
                Settings Feeder
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Summary cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">

                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="text-sm text-gray-500">Jumlah Prodi (cached)</div>
                    <div class="mt-1 text-3xl font-bold">{{ $prodiCount ?? 0 }}</div>
                </div>

                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="text-sm text-gray-500">Jumlah Mahasiswa (snapshot)</div>
                    <div class="mt-1 text-3xl font-bold">{{ $totalMahasiswa ?? '-' }}</div>
                    <div class="mt-1 text-xs text-gray-500">
                        {{ $statsSyncedAt?->format('Y-m-d H:i') ?? '-' }}
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="text-sm text-gray-500">Perguruan Tinggi</div>
                    <div class="mt-1 text-lg font-semibold">
                        {{ $ptName ?? 'Belum tersinkron (klik Settings → Refresh)' }}
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="text-sm text-gray-500">Last Refresh</div>
                    <div class="mt-1 text-lg font-semibold">
                        {{ $lastRun?->finished_at?->format('Y-m-d H:i') ?? '-' }}
                    </div>

                    @if($lastRun)
                        <div class="mt-1 text-sm {{ $lastRun->success ? 'text-green-700' : 'text-red-700' }}">
                            {{ $lastRun->success ? 'OK' : 'FAILED' }}
                        </div>
                        @if(!$lastRun->success && $lastRun->message)
                            <div class="mt-2 text-xs text-red-700 break-words">
                                {{ $lastRun->message }}
                            </div>
                        @endif
                    @else
                        <div class="mt-1 text-sm text-gray-500">Belum pernah refresh</div>
                    @endif
                </div>
            </div>

            {{-- Distributions --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">Distribusi Prodi per Jenjang</h3>
                        <span class="text-xs text-gray-500">cache</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse($prodiByJenjang as $row)
                            @php
                                $label = $row->jenjang;
                                $total = (int) $row->total;
                                $pct = ($prodiCount ?? 0) > 0 ? round(($total / $prodiCount) * 100) : 0;
                            @endphp

                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <div class="text-gray-700 font-medium">{{ $label }}</div>
                                    <div class="text-gray-600">{{ $total }} ({{ $pct }}%)</div>
                                </div>
                                <div class="mt-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-2 bg-black" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">
                                Belum ada data. Silakan Refresh Feeder Data di Settings.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">Distribusi Prodi per Status</h3>
                        <span class="text-xs text-gray-500">cache</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse($prodiByStatus as $row)
                            @php
                                $label = $row->status;
                                $total = (int) $row->total;
                                $pct = ($prodiCount ?? 0) > 0 ? round(($total / $prodiCount) * 100) : 0;
                            @endphp

                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <div class="text-gray-700 font-medium">{{ $label }}</div>
                                    <div class="text-gray-600">{{ $total }} ({{ $pct }}%)</div>
                                </div>
                                <div class="mt-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-2 bg-black" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">
                                Belum ada data. Silakan Refresh Feeder Data di Settings.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Sample prodi table --}}
            <div class="bg-white rounded-2xl shadow p-5">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Contoh Daftar Prodi (10)</h3>
                    <span class="text-xs text-gray-500">
                        Terakhir sync: {{ optional($prodiSample->first()?->synced_at)->format('Y-m-d H:i') ?? '-' }}
                    </span>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500 border-b">
                            <tr>
                                <th class="py-2 pr-4">Kode</th>
                                <th class="py-2 pr-4">Nama Prodi</th>
                                <th class="py-2 pr-4">Jenjang</th>
                                <th class="py-2 pr-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($prodiSample as $p)
                                <tr class="text-gray-700">
                                    <td class="py-2 pr-4 whitespace-nowrap">{{ $p->kode_prodi ?? '-' }}</td>
                                    <td class="py-2 pr-4">{{ $p->nama_prodi ?? '-' }}</td>
                                    <td class="py-2 pr-4 whitespace-nowrap">{{ $p->jenjang ?? '-' }}</td>
                                    <td class="py-2 pr-4 whitespace-nowrap">{{ $p->status ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-3 text-gray-500">
                                        Belum ada data prodi. Silakan Refresh Feeder Data di Settings.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
