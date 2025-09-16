<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\Folder;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public User $client;
    public ?Folder $currentFolder = null;
    public array $breadcrumbs = [];

    // --- NUEVAS PROPIEDADES PARA LA VISTA PREVIA ---
    public bool $showPreviewModal = false;
    public ?string $previewUrl = null;

    public function mount(): void
    {
        $this->client = Auth::user();
        $this->generateBreadcrumbs();
    }

    #[Computed]
    public function folders(): Collection
    {
        return Folder::where('user_id', $this->client->id)
            ->where('parent_id', $this->currentFolder?->id)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function projectFiles(): Collection
    {
        return ProjectFile::where('user_id', $this->client->id)
            ->where('folder_id', $this->currentFolder?->id)
            ->orderBy('file_name')
            ->get();
    }

    public function openFolder(int $folderId): void
    {
        $this->currentFolder = Folder::findOrFail($folderId);
        $this->generateBreadcrumbs();
    }

    public function generateBreadcrumbs(): void
    {
        $this->breadcrumbs = [];
        $folder = $this->currentFolder;
        while ($folder) {
            array_unshift($this->breadcrumbs, $folder);
            $folder = $folder->parent;
        }
    }

    public function goToFolder(?int $folderId): void
    {
        $this->currentFolder = $folderId ? Folder::findOrFail($folderId) : null;
        $this->generateBreadcrumbs();
    }

    public function showPreview(int $fileId): void
    {
        $this->previewUrl = route('client.projects.files.preview', $fileId);
        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewUrl = null;
    }
}; ?>

<div class="p-4 sm:p-6 lg:p-8 bg-white rounded-lg shadow">
    <h1 class="text-2xl font-semibold text-gray-900 mb-4">Mis Proyectos</h1>

    <!-- Breadcrumbs -->
    <nav class="mb-4 text-sm text-gray-600 flex items-center space-x-2">
        <a wire:click.prevent="goToFolder(null)" href="#" class="hover:underline">Raíz</a>
        @foreach($breadcrumbs as $breadcrumb)
            <span>/</span>
            <a wire:click.prevent="goToFolder({{ $breadcrumb->id }})" href="#" class="hover:underline">{{ $breadcrumb->name }}</a>
        @endforeach
    </nav>
    
    <!-- Tabla de Contenidos -->
    <div class="shadow overflow-x-auto border-b border-gray-200 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if ($this->folders->isEmpty() && $this->projectFiles->isEmpty())
                    <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">Esta carpeta está vacía.</td></tr>
                @else
                    @foreach($this->folders as $folder)
                        <tr wire:key="folder-{{ $folder->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a wire:click.prevent="openFolder({{ $folder->id }})" href="#" class="flex items-center text-indigo-600 hover:text-indigo-900">
                                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                    {{ $folder->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Carpeta</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $folder->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4"></td>
                        </tr>
                    @endforeach
                    @foreach($this->projectFiles as $projectFile)
                         <tr wire:key="project-file-{{ $projectFile->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="flex items-center"><svg class="h-5 w-5 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg> {{ $projectFile->file_name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Archivo</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $projectFile->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="showPreview({{ $projectFile->id }})" class="text-indigo-600 hover:text-indigo-900">Ver</button>
                                <a href="{{ route('client.projects.files.download', $projectFile) }}" class="text-green-600 hover:text-green-900">Descargar</a>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

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