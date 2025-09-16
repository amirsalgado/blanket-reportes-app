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

    // --- NUEVAS PROPIEDADES PARA LA VISTA PREVIA ---
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

    // --- NUEVOS MÉTODOS PARA LA VISTA PREVIA ---
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
    {{-- (Contenido del componente: Búsqueda, Botón "Subir", etc. - sin cambios) --}}
    <div class="p-4 sm:p-6 lg:p-8 bg-white rounded-lg shadow">
        {{-- ... --}}

        <!-- Tabla de Reportes -->
        <div class="shadow overflow-x-auto border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        {{-- --- CABECERAS DE TABLA ACTUALIZADAS --- --}}
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
                            {{-- --- CELDAS DE DATOS ACTUALIZADAS --- --}}
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
                        {{-- ... (Estado Vacío) ... --}}
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $reports->links() }}</div>
    </div>

    {{-- (Modal de Carga de Archivo - código actualizado) --}}
    @if ($showModal)
        {{-- ... (código del modal) --}}
    @endif

    {{-- --- NUEVO MODAL PARA LA VISTA PREVIA --- --}}
    @if ($showPreviewModal)
        <div class="fixed z-20 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen">
                <div wire:click="closePreview()" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-4xl" style="height: 90vh;">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Vista Previa del Reporte</h3>
                        <button wire:click="closePreview()" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <div class="p-4 h-full">
                        <iframe src="{{ $previewUrl }}" frameborder="0" class="w-full h-full"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>