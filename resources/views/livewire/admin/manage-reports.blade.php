<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Domain\Contracts\ReportRepositoryInterface;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use App\Domain\Enums\ServiceType;
use Illuminate\Support\Collection; // Importar la clase Collection
use Illuminate\Validation\Rule;

new class extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public ?string $startDate = null;
    public ?string $endDate = null;

    public bool $showModal = false;
    public ?int $reportId = null;

    // Campos del formulario
    public $file;
    public ?int $user_id = null;
    public string $month = '';
    public string $service = '';

    // Se declara la propiedad para que acepte una Collection.
    public Collection $clients;
    
    // El método mount se usa para inicializar propiedades.
    public function mount(UserRepositoryInterface $userRepository): void
    {
        $this->clients = $userRepository->getClientsForSelect();
        $this->month = now()->format('Y-m'); // Pre-seleccionar el mes actual
    }

    protected function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB Max
            'user_id' => ['required', 'exists:users,id'],
            'month' => ['required', 'date_format:Y-m'],
            'service' => ['required', Rule::in(array_column(ServiceType::cases(), 'value'))],
        ];
    }

    public function openModal(): void
    {
        $this->resetValidation();
        $this->reset('file', 'user_id', 'service');
        $this->month = now()->format('Y-m');
        $this->showModal = true;
    }

    public function save(ReportRepositoryInterface $reportRepository): void
    {
        $validated = $this->validate();

        $fileName = $this->file->getClientOriginalName();
        $filePath = $this->file->storeAs('reports', $fileName, 'private');

        $reportRepository->create([
            'user_id' => $validated['user_id'],
            'file_name' => $fileName,
            'file_path' => $filePath,
            'month' => $validated['month'],
            'service' => $validated['service'],
        ]);

        $this->showModal = false;
        $this->dispatch('swal:success', message: 'Reporte subido con éxito.');
    }

    // --- CORRECCIÓN APLICADA AQUÍ ---
    // Se cambia el tipo de retorno a `?StreamedResponse` para indicar que puede ser nulo.
    public function download(int $reportId, ReportRepositoryInterface $reportRepository): ?\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $report = $reportRepository->findById($reportId);
        if ($report && Storage::disk('private')->exists($report->file_path)) {
            return Storage::disk('private')->download($report->file_path, $report->file_name);
        }

        $this->dispatch('swal:error', message: 'El archivo no se encontró o ha sido eliminado.');
        return null; // Devolver explícitamente null
    }

    public function delete(int $id, ReportRepositoryInterface $reportRepository): void
    {
        $reportRepository->delete($id);
        $this->dispatch('swal:success', message: 'Reporte eliminado con éxito.');
    }

    public function with(ReportRepositoryInterface $reportRepository): array
    {
        return [
            'reports' => $reportRepository->getPaginated(
                $this->search,
                10,
                $this->startDate,
                $this->endDate
            ),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Gestión de Reportes</h1>
        <x-primary-button wire:click="openModal">Subir Nuevo Reporte</x-primary-button>
    </div>

    <!-- Filtros -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-white rounded-lg shadow">
        <div class="md:col-span-2">
            <label for="search" class="sr-only">Buscar</label>
            <x-text-input wire:model.live.debounce.300ms="search" id="search" placeholder="Buscar por archivo o cliente..." class="w-full" />
        </div>
        <div>
            <label for="startDate" class="sr-only">Fecha de Inicio</label>
            <x-text-input wire:model.live="startDate" id="startDate" type="date" class="w-full" />
        </div>
        <div>
            <label for="endDate" class="sr-only">Fecha de Fin</label>
            <x-text-input wire:model.live="endDate" id="endDate" type="date" class="w-full" />
        </div>
    </div>

    <!-- Tabla de Reportes -->
    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre del Archivo</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa Cliente</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Subida</th>
                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($reports as $report)
                    <tr wire:key="{{ $report->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $report->file_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $report->user->company ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $report->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="download({{ $report->id }})" class="text-indigo-600 hover:text-indigo-900">Descargar</button>
                            <button wire:click="delete({{ $report->id }})" wire:confirm="¿Estás seguro?" class="text-red-600 hover:text-red-900 ml-4">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                                <p class="mt-2 text-sm text-gray-500">No se encontraron reportes</p>
                                <p class="mt-1 text-xs text-gray-400">Aún no se han subido reportes.</p>
                                <x-primary-button wire:click="openModal" class="mt-4">Subir Reporte</x-primary-button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($reports->hasPages())
        <div class="px-4 py-2">{{ $reports->links() }}</div>
    @endif

    <!-- Modal para Subir Reporte -->
    @if($showModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Subir Nuevo Reporte</h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="user_id" class="block text-sm font-medium text-gray-700">Cliente (Razón Social)</label>
                                    <select wire:model="user_id" id="user_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">Seleccione un cliente...</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->company }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                                </div>
                                <div>
                                    <label for="month" class="block text-sm font-medium text-gray-700">Mes del Reporte</label>
                                    <input type="month" wire:model="month" id="month" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <x-input-error :messages="$errors->get('month')" class="mt-2" />
                                </div>
                                <div>
                                    <label for="service" class="block text-sm font-medium text-gray-700">Servicio</label>
                                    <select wire:model="service" id="service" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">Seleccione un servicio...</option>
                                        @foreach(App\Domain\Enums\ServiceType::cases() as $serviceType)
                                            <option value="{{ $serviceType->value }}">{{ $serviceType->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('service')" class="mt-2" />
                                </div>
                                <div>
                                    <label for="file" class="block text-sm font-medium text-gray-700">Archivo PDF</label>
                                    <input type="file" wire:model="file" id="file" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                                    <div wire:loading wire:target="file" class="mt-2 text-sm text-gray-500">Cargando...</div>
                                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <x-primary-button type="submit" wire:loading.attr="disabled">
                                <span wire:loading.remove>Guardar Reporte</span>
                                <span wire:loading>Guardando...</span>
                            </x-primary-button>
                            <x-secondary-button type="button" wire:click="$set('showModal', false)" class="mr-2">Cancelar</x-secondary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>