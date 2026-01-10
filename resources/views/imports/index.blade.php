<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Imports</h2>
            <a href="{{ route('imports.create') }}" class="px-4 py-2 rounded-xl bg-black text-white">New Import</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-4 rounded-xl bg-green-50 border border-green-200 p-3 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-gray-500 border-b">
                        <tr>
                            <th class="py-2 pr-4">ID</th>
                            <th class="py-2 pr-4">Module</th>
                            <th class="py-2 pr-4">File</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Valid/Invalid</th>
                            <th class="py-2 pr-4">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($batches as $b)
                            <tr>
                                <td class="py-2 pr-4">
                                    <a class="underline" href="{{ route('imports.show', $b) }}">#{{ $b->id }}</a>
                                </td>
                                <td class="py-2 pr-4">{{ $b->module }}</td>
                                <td class="py-2 pr-4">{{ $b->filename }}</td>
                                <td class="py-2 pr-4">{{ $b->status }}</td>
                                <td class="py-2 pr-4">{{ $b->valid_rows }}/{{ $b->invalid_rows }}</td>
                                <td class="py-2 pr-4">{{ $b->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-3 text-gray-500">Belum ada import.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">{{ $batches->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
