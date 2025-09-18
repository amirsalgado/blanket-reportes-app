<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Domain\Contracts\ReportRepositoryInterface;
use App\Domain\Enums\ServiceType;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

new class extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public bool $showModal = false;
    
    // Propiedades del formulario
    public $file;
    public ?int $selectedClientId = null;
    public string $month = '';
    public string $service = '';

    public bool $showPreviewModal = false;
    public ?string $previewUrl = null;

    protected function rules(): array
    {
        return [
            'selectedClientId' => ['required', 'exists:users,id'],
            'month' => ['required', 'date_format:Y-m'],
            'service' => ['required', new \Illuminate\Validation\Rules\Enum(ServiceType::class)],
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    public function getRepository(): ReportRepositoryInterface
    {
        return resolve(ReportRepositoryInterface::class);
    }
    
    public function getClientsForSelect(): Collection
    {
        return User::where('role', 'cliente')
            ->where('client_type', 'juridica')
            ->orderBy('company')
            ->get();
    }

    public function save(): void
    {
        $data = $this->validate();
        $originalName = $data['file']->getClientOriginalName();
        $path = $data['file']->store('reports', 'private');

        $this->getRepository()->create([
            'user_id' => $data['selectedClientId'],
            'file_name' => $originalName,
            'file_path' => $path,
            'month' => $data['month'],
            'service' => $data['service'],
        ]);

        $this->showModal = false;
        $this->reset('file', 'selectedClientId', 'month', 'service');
        $this->dispatch('swal:success', message: 'Reporte guardado.');
    }

    public function delete(int $id): void
    {
        $report = $this->getRepository()->findById($id);
        if ($report && Storage::disk('private')->exists($report->file_path)) {
            Storage::disk('private')->delete($report->file_path);
        }
        $this->getRepository()->delete($id);
        $this->dispatch('swal:success', message: 'Reporte eliminado.');
    }

    public function download(int $id): ?StreamedResponse
    {
        $report = $this->getRepository()->findById($id);
        if ($report && Storage::disk('private')->exists($report->file_path)) {
            return Storage::disk('private')->download($report->file_path, $report->file_name);
        }
        $this->dispatch('swal:error', message: 'Archivo no encontrado.');
        return null;
    }

    public function showPreview(int $reportId): void
    {
        $this->previewUrl = route('admin.reports.preview', $reportId);
        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewUrl = null;
    }
    
    public function with(): array
    {
        return [
            'reports' => $this->getRepository()->getPaginated($this->search),
            'clients' => $this->getClientsForSelect(),
        ];
    }
}; ?>

<div>
    <div class="p-4 sm:p-6 lg:p-8 bg-white rounded-lg shadow">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Gestión de Reportes</h1>
            <x-primary-button wire:click="$set('showModal', true)">Subir Nuevo Reporte</x-primary-button>
        </div>
        <div class="mb-4">
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Buscar por nombre de archivo o cliente..." 
                class="w-full border-gray-300 rounded-md shadow-sm"
            >
        </div>

        <!-- Tabla de Reportes -->
        <div class="shadow overflow-x-auto border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre del Archivo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empresa Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mes del Reporte</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servicio</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reports as $report)
                        <tr wire:key="{{ $report->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->file_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->company }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($report->month)->format('F Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->service }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                <button wire:click="showPreview({{ $report->id }})" class="text-indigo-600 hover:text-indigo-900">Ver</button>
                                <button wire:click="download({{ $report->id }})" class="text-green-600 hover:text-green-900">Descargar</button>
                                <button wire:click="delete({{ $report->id }})" wire:confirm="¿Estás seguro?" class="text-red-600 hover:text-red-900">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron reportes</h3>
                                    <p class="mt-1 text-sm text-gray-500">Comienza subiendo el primer reporte.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $reports->links() }}</div>
    </div>

    {{-- --- MODAL DE CARGA RESTAURADO --- --}}
    @if ($showModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
    
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Subir Nuevo Reporte</h3>
                            <div class="mt-4 space-y-4">
                                <!-- Selector de Cliente -->
                                <div>
                                    <label for="client" class="block text-sm font-medium text-gray-700">Cliente</label>
                                    <select wire:model="selectedClientId" id="client" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">Seleccione un cliente...</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->company ?? $client->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedClientId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
    
                                <!-- Mes del Reporte -->
                                <div>
                                    <label for="month" class="block text-sm font-medium text-gray-700">Mes del Reporte</label>
                                    <input type="month" wire:model="month" id="month" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('month') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
    
                                <!-- Tipo de Servicio -->
                                <div>
                                    <label for="service" class="block text-sm font-medium text-gray-700">Tipo de Servicio</label>
                                    <select wire:model="service" id="service" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">Seleccione un servicio...</option>
                                        @foreach(App\Domain\Enums\ServiceType::cases() as $serviceType)
                                            <option value="{{ $serviceType->value }}">{{ $serviceType->value }}</option>
                                        @endforeach
                                    </select>
                                    @error('service') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
    
                                <!-- Archivo PDF -->
                                <div>
                                    <label for="file" class="block text-sm font-medium text-gray-700">Archivo (PDF)</label>
                                    <input type="file" wire:model="file" id="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                    <div wire:loading wire:target="file" class="mt-2 text-sm text-gray-500">Cargando...</div>
                                    @error('file') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                Guardar Reporte
                            </button>
                            <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal para la Vista Previa --}}
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