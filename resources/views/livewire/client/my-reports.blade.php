<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    
    // --- NUEVAS PROPIEDADES PARA LA VISTA PREVIA ---
    public bool $showPreviewModal = false;
    public ?string $previewUrl = null;

    public function showPreview(int $reportId): void
    {
        $this->previewUrl = route('client.reports.preview', $reportId);
        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewUrl = null;
    }

    public function with(): array
    {
        $user = Auth::user();
        return [
            'reports' => Report::where('user_id', $user->id)
                ->when($this->search, function ($query) {
                    $query->where('file_name', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10),
        ];
    }
}; ?>

<div>
    <div class="p-4 sm:p-6 lg:p-8 bg-white rounded-lg shadow">
        <h1 class="text-2xl font-semibold text-gray-900 mb-4">Mis Reportes</h1>
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar en mis reportes..." class="w-full sm:w-1/3 mb-4 border-gray-300 rounded-md shadow-sm">

        <div class="shadow overflow-x-auto border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre del Archivo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mes del Reporte</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servicio</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reports as $report)
                        <tr wire:key="{{ $report->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->file_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($report->month)->format('F Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->service }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                {{-- --- BOTÓN AÑADIDO --- --}}
                                <button wire:click="showPreview({{ $report->id }})" class="text-indigo-600 hover:text-indigo-900">Ver</button>
                                <a href="{{ route('client.reports.download', $report) }}" class="text-green-600 hover:text-green-900">Descargar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">No tienes reportes disponibles.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $reports->links() }}</div>
    </div>

    {{-- --- NUEVO MODAL PARA LA VISTA PREVIA --- --}}
    @if ($showPreviewModal)
        <div class="fixed z-20 inset-0 overflow-y-auto" x-data @keydown.escape.window="$wire.closePreview()">
            <div class="flex items-center justify-center min-h-screen">
                <div wire:click="closePreview()" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-4xl" style="height: 90vh;">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Vista Previa del Reporte</h3>
                        <button wire:click="closePreview()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                    </div>
                    <div class="p-4 h-full">
                        <iframe src="{{ $previewUrl }}" frameborder="0" class="w-full" style="height: calc(100% - 4rem);"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>