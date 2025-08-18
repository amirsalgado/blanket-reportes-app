<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Domain\Contracts\ReportRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public bool $showModal = false;
    public $file;
    public ?int $selectedClientId = null;
    
    // Propiedades para los filtros de fecha
    public ?string $startDate = null;
    public ?string $endDate = null;

    // Este método se ejecuta cuando se actualiza una propiedad con `wire:model.live`
    public function updating($property): void
    {
        if (in_array($property, ['search', 'startDate', 'endDate'])) {
            $this->resetPage();
        }
    }

    public function getRepository(): ReportRepositoryInterface
    {
        return resolve(ReportRepositoryInterface::class);
    }
    
    // Acción para limpiar todos los filtros
    public function clearFilters(): void
    {
        $this->reset('startDate', 'endDate', 'search');
        $this->resetPage();
    }

    public function save(): void
    {
        $data = $this->validate([
            'selectedClientId' => ['required', 'exists:users,id'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);
        
        $originalName = $data['file']->getClientOriginalName();
        $path = $data['file']->store('reports', 'private');

        $this->getRepository()->create([
            'user_id' => $data['selectedClientId'],
            'file_name' => $originalName,
            'file_path' => $path,
        ]);

        $this->showModal = false;
        $this->reset('file', 'selectedClientId');
    }

    public function delete(int $id): void
    {
        $this->getRepository()->delete($id);
    }

    public function download(int $id)
    {
        $report = $this->getRepository()->findById($id);
        if ($report && Storage::disk('private')->exists($report->file_path)) {
            return Storage::disk('private')->download($report->file_path, $report->file_name);
        }
        return abort(404, 'Archivo no encontrado.');
    }

    public function with(): array
    {
        // Pasamos los filtros al método del repositorio
        return [
            'reports' => $this->getRepository()->getPaginated($this->search, 10, $this->startDate, $this->endDate),
            'clients' => User::where('role', 'cliente')->orderBy('name')->get(),
        ];
    }
}; ?>

<div>
    <div class="p-4 sm:p-6 lg:p-8 bg-white rounded-lg shadow">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-900">Gestión de Reportes</h1>
            <button wire:click="$set('showModal', true)" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md">
                Subir Nuevo Reporte
            </button>
        </div>

        <!-- Filtros -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6 items-center">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por archivo o cliente..." class="col-span-1 md:col-span-2 border-gray-300 rounded-md shadow-sm">
            <input wire:model.live="startDate" type="date" class="border-gray-300 rounded-md shadow-sm" title="Fecha de inicio">
            <input wire:model.live="endDate" type="date" class="border-gray-300 rounded-md shadow-sm" title="Fecha de fin">
        </div>
        
        <!-- Tabla de Reportes -->
        <div class="mt-4 shadow overflow-x-auto border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre del Archivo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente Asociado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha de Subida</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reports as $report)
                        <tr wire:key="{{ $report->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->file_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="download({{ $report->id }})" class="text-green-600 hover:text-green-900">Descargar</button>
                                <button wire:click="delete({{ $report->id }})" wire:confirm="¿Estás seguro?" class="text-red-600 hover:text-red-900 ml-4">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No se encontraron reportes con los filtros aplicados.
                            <button wire:click="clearFilters" class="text-indigo-600 hover:underline ml-2 font-semibold">Limpiar filtros</button>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $reports->links() }}</div>
    </div>
    
    {{-- El modal para subir reportes se mantiene igual --}}
    @if($showModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen">
                <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900">Subir Nuevo Reporte</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="client" class="block text-sm font-medium text-gray-700">Asignar a Cliente</label>
                                    <select wire:model="selectedClientId" id="client" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Seleccione un cliente...</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedClientId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="file" class="block text-sm font-medium text-gray-700">Archivo PDF</label>
                                    <input type="file" wire:model="file" id="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <div wire:loading wire:target="file" class="text-sm text-gray-500 mt-1">Cargando...</div>
                                    @error('file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Guardar Reporte</button>
                            <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>