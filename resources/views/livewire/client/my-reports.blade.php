<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Domain\Contracts\ReportRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function getRepository(): ReportRepositoryInterface
    {
        return resolve(ReportRepositoryInterface::class);
    }

    public function download(int $id)
    {
        $report = $this->getRepository()->findById($id);

        // Verificación de seguridad: Asegurarse de que el reporte pertenece al usuario autenticado
        if ($report && $report->user_id === Auth::id() && Storage::disk('private')->exists($report->file_path)) {
            return Storage::disk('private')->download($report->file_path, $report->file_name);
        }

        return abort(404, 'Archivo no encontrado.');
    }

    public function with(): array
    {
        return [
            'reports' => $this->getRepository()->getForUserPaginated(Auth::id(), $this->search),
        ];
    }
}; ?>

<div>
    <div class="p-4 sm:p-6 lg:p-8 bg-white rounded-lg shadow">
        <h1 class="text-2xl font-semibold text-gray-900">Mis Reportes</h1>

        <div class="py-4">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar en mis reportes..."
                class="w-full sm:w-1/3 border-gray-300 rounded-md shadow-sm">
        </div>

        <div class="shadow overflow-x-auto border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre del Archivo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha de Subida</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reports as $report)
                        <tr wire:key="{{ $report->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->file_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="download({{ $report->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 font-semibold">Descargar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">Aún no tienes reportes
                                disponibles.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $reports->links() }}</div>
    </div>
</div>
