<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Feeder Settings
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if (session('status'))
                    <div class="mb-4 rounded-xl bg-green-50 border border-green-200 p-3 text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded-xl bg-red-50 border border-red-200 p-3 text-red-800 break-words">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Info cache + last refresh --}}
                <div class="mb-4 rounded-xl bg-gray-50 border border-gray-200 p-3 text-gray-700">
                    <div class="flex flex-wrap gap-x-6 gap-y-1">
                        <div>
                            <span class="font-medium">Prodi cached:</span> {{ $prodiCount ?? 0 }}
                        </div>
                        <div>
                            <span class="font-medium">Last refresh:</span>
                            @if($lastRun)
                                {{ $lastRun->finished_at?->toDateTimeString() ?? '-' }}
                                ({{ $lastRun->success ? 'OK' : 'FAILED' }})
                            @else
                                -
                            @endif
                        </div>
                    </div>

                    @if($lastRun && !$lastRun->success && $lastRun->message)
                        <div class="mt-2 text-sm text-red-700 break-words">
                            {{ $lastRun->message }}
                        </div>
                    @endif
                </div>

                {{-- Action buttons (Refresh terpisah agar tidak kena validasi form) --}}
                <div class="flex flex-wrap gap-3 mb-5">
                    <form method="POST" action="{{ route('settings.feeder.refresh') }}">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50">
                            Refresh Feeder Data
                        </button>
                    </form>
                </div>

                {{-- Main settings form --}}
                <form method="POST" action="{{ route('settings.feeder.save') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">FEEDER_WS_URL</label>
                        <input
                            type="text"
                            name="ws_url"
                            value="{{ old('ws_url', $setting?->ws_url ?? '') }}"
                            placeholder="http://127.0.0.1:8082/ws/live2.php"
                            class="mt-1 w-full rounded-xl border-gray-300 focus:border-black focus:ring-black"
                            required
                        />
                        @error('ws_url')
                            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <input
                            type="text"
                            name="username"
                            value="{{ old('username', $setting?->username ?? '') }}"
                            class="mt-1 w-full rounded-xl border-gray-300 focus:border-black focus:ring-black"
                        />
                        @error('username')
                            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Password</label>
                        <input
                            type="password"
                            name="password"
                            value="{{ old('password') }}"
                            placeholder="{{ $setting?->password ? '•••••••• (kosongkan jika tidak diganti)' : '' }}"
                            class="mt-1 w-full rounded-xl border-gray-300 focus:border-black focus:ring-black"
                        />
                        @error('password')
                            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Kosongkan jika tidak ingin mengganti password yang sudah tersimpan.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Timeout (detik)</label>
                        <input
                            type="number"
                            name="timeout"
                            value="{{ old('timeout', $setting?->timeout ?? 30) }}"
                            min="5"
                            max="120"
                            class="mt-1 w-full rounded-xl border-gray-300 focus:border-black focus:ring-black"
                            required
                        />
                        @error('timeout')
                            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" name="submit" value="save"
                            class="px-4 py-2 rounded-xl bg-black text-white hover:opacity-90">
                            Save Settings
                        </button>

                        <button type="submit" name="submit" value="test"
                            class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50">
                            Test Connection
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
