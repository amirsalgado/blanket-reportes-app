{{-- 
    ================================================================
    Componente Completo: Gestor de Archivos con Drag & Drop
    ================================================================
    Archivo: `resources/views/livewire/admin/file-manager.blade.php`
    Funcionalidades:
    - Navegación por carpetas y breadcrumbs.
    - Carga de archivos (clic y Drag & Drop).
    - Creación de nuevas carpetas.
    - Acciones en lote: Selección múltiple, "Seleccionar Todo" y eliminación masiva.

    Esta versión consolida todas las funcionalidades y correcciones para
    asegurar la correcta sincronización de datos al crear, modificar
    o eliminar archivos y carpetas.
--}}

<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use App\Models\Folder;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    use WithFileUploads;

    public User $client;
    public ?Folder $currentFolder = null;
    public array $breadcrumbs = [];

    #[Validate('nullable|array')]
    public $files = [];

    public string $newFolderName = '';
    
    // Properties for actions
    public $renamingType = null;
    public $renamingId = null;
    public string $renamingName = '';
    public array $selectedItems = [];
    public bool $selectAll = false;

    // Properties for preview
    public bool $showPreviewModal = false;
    public ?string $previewUrl = null;

    public function mount(User $client): void
    {
        $this->client = $client;
        $this->generateBreadcrumbs();
    }

    #[Computed]
    public function folders(): Collection
    {
        $query = Folder::where('user_id', $this->client->id)->orderBy('name');
        if ($this->currentFolder) {
            $query->where('parent_id', $this->currentFolder->id);
        } else {
            $query->whereNull('parent_id');
        }
        return $query->get();
    }

    #[Computed]
    public function projectFiles(): Collection
    {
        $query = ProjectFile::where('user_id', $this->client->id)->orderBy('file_name');
        if ($this->currentFolder) {
            $query->where('folder_id', $this->currentFolder->id);
        } else {
            $query->whereNull('folder_id');
        }
        return $query->get();
    }
    
    public function updatedFiles(): void
    {
        $this->saveFiles();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $folderIds = $this->folders()->pluck('id')->map(fn ($id) => 'folder-' . $id);
            $fileIds = $this->projectFiles()->pluck('id')->map(fn ($id) => 'file-' . $id);
            $this->selectedItems = $folderIds->concat($fileIds)->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    protected function refreshView(): void
    {
        unset($this->folders);
        unset($this->projectFiles);
    }

    public function openFolder(int $folderId): void
    {
        $this->currentFolder = Folder::findOrFail($folderId);
        $this->generateBreadcrumbs();
        $this->reset('selectAll', 'selectedItems');
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
        $this->reset('selectAll', 'selectedItems');
    }

    public function createFolder(): void
    {
        $this->validate(['newFolderName' => 'required|string|max:255']);
        Folder::create(['user_id' => $this->client->id, 'parent_id' => $this->currentFolder?->id, 'name' => $this->newFolderName]);
        $this->newFolderName = '';
        $this->refreshView();
        $this->dispatch('swal:success', message: 'Carpeta creada.');
    }

    public function saveFiles(): void
    {
        $this->validate(['files.*' => 'required|file|max:10240']);
        foreach ($this->files as $file) {
            $originalName = $file->getClientOriginalName();
            $exists = ProjectFile::where('user_id', $this->client->id)->where('folder_id', $this->currentFolder?->id)->where('file_name', $originalName)->exists();
            if ($exists) {
                $this->dispatch('swal:error', message: "El archivo '{$originalName}' ya existe.");
                $this->reset('files'); return;
            }
            $path = $file->storeAs("projects/{$this->client->id}", $originalName, 'private');
            ProjectFile::create(['user_id' => $this->client->id, 'folder_id' => $this->currentFolder?->id, 'file_name' => $originalName, 'file_path' => $path]);
        }
        $this->reset('files');
        $this->refreshView();
        $this->dispatch('swal:success', message: 'Archivos subidos.');
    }
    
    public function startRenaming($type, $id, $name): void
    {
        $this->renamingType = $type;
        $this->renamingId = $id;
        $this->renamingName = $name;
    }

    public function cancelRenaming(): void
    {
        $this->reset('renamingType', 'renamingId', 'renamingName');
    }

    public function saveRename(): void
    {
        $this->validate(['renamingName' => 'required|string|max:255']);
        if ($this->renamingType === 'folder') {
            Folder::find($this->renamingId)->update(['name' => $this->renamingName]);
        } elseif ($this->renamingType === 'project_file') {
            ProjectFile::find($this->renamingId)->update(['file_name' => $this->renamingName]);
        }
        $this->cancelRenaming();
        $this->refreshView();
        $this->dispatch('swal:success', message: 'Nombre actualizado.');
    }

    public function downloadFile(int $fileId)
    {
        $file = ProjectFile::findOrFail($fileId);
        return Storage::disk('private')->download($file->file_path, $file->file_name);
    }

    public function delete($type, $id): void
    {
        if ($type === 'folder') {
            $folder = Folder::withCount(['children', 'projectFiles'])->find($id);
            if ($folder->children_count > 0 || $folder->projectFiles_count > 0) {
                $this->dispatch('swal:error', message: 'La carpeta no está vacía.'); return;
            }
            $folder->delete();
        } elseif ($type === 'project_file') {
            $projectFile = ProjectFile::find($id);
            Storage::disk('private')->delete($projectFile->file_path);
            $projectFile->delete();
        }
        $this->refreshView();
        $this->dispatch('swal:success', message: 'Elemento eliminado.');
    }

    public function deleteSelected(): void
    {
        $folderIds = []; $fileIds = [];
        foreach ($this->selectedItems as $item) {
            [$type, $id] = explode('-', $item);
            if ($type === 'folder') $folderIds[] = $id;
            if ($type === 'file') $fileIds[] = $id;
        }
        $foldersToDelete = Folder::withCount(['children', 'projectFiles'])->find($folderIds);
        foreach ($foldersToDelete as $folder) {
            if ($folder->children_count > 0 || $folder->projectFiles_count > 0) {
                $this->dispatch('swal:error', message: "La carpeta '{$folder->name}' no está vacía."); return;
            }
        }
        $files = ProjectFile::find($fileIds);
        foreach ($files as $file) {
            Storage::disk('private')->delete($file->file_path);
            $file->delete();
        }
        Folder::destroy($folderIds);
        $this->reset('selectedItems');
        $this->refreshView();
        $this->dispatch('swal:success', message: 'Elementos eliminados.');
    }
    
    public function showPreview(int $fileId): void
    {
        $this->previewUrl = route('admin.projects.files.preview', $fileId);
        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewUrl = null;
    }
}; ?>

<div 
    x-data="{ 
        isDropping: false,
        contextMenu: { open: false, x: 0, y: 0, type: null, id: null, name: '' },
        openContextMenu($event, type, id, name) {
            this.contextMenu.open = true; this.contextMenu.x = $event.clientX; this.contextMenu.y = $event.clientY;
            this.contextMenu.type = type; this.contextMenu.id = id; this.contextMenu.name = name;
        },
        closeContextMenu() { this.contextMenu.open = false; }
    }"
    @dragover.prevent="isDropping = true"
    @dragleave.prevent="isDropping = false"
    @drop.prevent="isDropping = false; 
        const files = $event.dataTransfer.files;
        if (files.length > 0) {
            @this.uploadMultiple('files', files, () => {}, () => {}, () => {});
        }
    "
    @click.away="closeContextMenu()"
    @keydown.escape.window="closeContextMenu()"
    class="relative"
>
    <!-- Overlay visual que aparece al arrastrar archivos -->
    <div x-show="isDropping" x-transition class="absolute inset-0 bg-blanket-blue bg-opacity-50 border-4 border-dashed border-white rounded-lg flex items-center justify-center z-50">
        <div class="text-center text-white">
            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
            <p class="mt-2 text-lg font-semibold">Suelte los archivos para subirlos</p>
        </div>
    </div>
    
    <div class="p-4 sm:p-6 lg:p-8 bg-white rounded-lg shadow">
        
        <!-- Breadcrumbs -->
        <nav class="mb-4 text-sm text-gray-600 flex items-center space-x-2">
            <a wire:click.prevent="goToFolder(null)" href="#" class="hover:underline">Raíz de {{ $client->company ?? $client->name }}</a>
            @foreach($breadcrumbs as $breadcrumb)
                <span>/</span>
                <a wire:click.prevent="goToFolder({{ $breadcrumb->id }})" href="#" class="hover:underline">{{ $breadcrumb->name }}</a>
            @endforeach
        </nav>
        
        <!-- Zona de Carga / Panel de Acciones -->
        <div class="my-4 p-6 border-2 border-dashed border-gray-300 rounded-lg text-center">
            <label for="file-upload-{{ $this->getId() }}" class="cursor-pointer">
                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                <p class="mt-2 text-sm text-gray-600">
                    <span class="font-semibold text-blanket-blue">Arrastra y suelta tus archivos aquí</span> o haz clic para seleccionarlos
                </p>
                <p class="text-xs text-gray-500">Puedes seleccionar múltiples archivos a la vez.</p>
                <input 
                    wire:model="files" 
                    id="file-upload-{{ $this->getId() }}" 
                    type="file" 
                    class="sr-only" 
                    multiple 
                >
            </label>
        </div>

        <!-- Barra de Progreso -->
        <div x-data="{ progress: 0 }" x-on:livewire-upload-start="progress = 0" x-on:livewire-upload-finish="progress = 0;" x-on:livewire-upload-progress="progress = $event.detail.progress">
            <div x-show="progress > 0" class="w-full bg-gray-200 rounded-full mt-2">
                <div class="bg-blanket-blue text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full" :style="`width: ${progress}%`" x-text="`${progress}%`"></div>
            </div>
        </div>
        
        <!-- Acciones (Crear Carpeta) y Barra de Lote -->
        <div class="my-4 flex items-center justify-between">
            @if ($selectedItems)
                <div class="p-2 bg-gray-100 rounded-md flex items-center justify-between flex-grow">
                    <span class="text-sm font-medium text-gray-700">{{ count($selectedItems) }} elemento(s) seleccionado(s)</span>
                    <button wire:click="deleteSelected" wire:confirm="¿Estás seguro?" class="bg-red-600 text-white px-3 py-1 text-sm rounded-md hover:bg-red-700">
                        Eliminar
                    </button>
                </div>
            @else
                <div></div>
            @endif

            <div class="flex items-center space-x-2 ml-4">
                <input type="text" wire:model="newFolderName" placeholder="Nombre de la carpeta" class="border-gray-300 rounded-md shadow-sm">
                <x-primary-button wire:click="createFolder">Crear Carpeta</x-primary-button>
            </div>
        </div>
        
        <!-- Tabla de Contenidos -->
        <div class="shadow overflow-x-auto border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 w-12"><input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300"></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @if ($this->folders->isEmpty() && $this->projectFiles->isEmpty())
                        <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">Esta carpeta está vacía.</td></tr>
                    @else
                        @foreach($this->folders as $folder)
                            <tr wire:key="folder-{{ $folder->id }}" @contextmenu.prevent="openContextMenu($event, 'folder', {{ $folder->id }}, '{{ $folder->name }}')" class="hover:bg-gray-50">
                                <td class="px-6 py-4"><input type="checkbox" wire:model.live="selectedItems" value="folder-{{ $folder->id }}" class="rounded border-gray-300"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($renamingType === 'folder' && $renamingId === $folder->id)
                                        <input type="text" wire:model="renamingName" wire:keydown.enter="saveRename" wire:keydown.escape="cancelRenaming" class="border-gray-300 rounded-md shadow-sm w-full" autofocus>
                                    @else
                                        <a wire:click.prevent="openFolder({{ $folder->id }})" href="#" class="flex items-center text-indigo-600 hover:text-indigo-900">
                                            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                            {{ $folder->name }}
                                        </a>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Carpeta</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $folder->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                        @foreach($this->projectFiles as $projectFile)
                             <tr wire:key="project-file-{{ $projectFile->id }}" @contextmenu.prevent="openContextMenu($event, 'project_file', {{ $projectFile->id }}, '{{ $projectFile->file_name }}')" class="hover:bg-gray-50">
                                <td class="px-6 py-4"><input type="checkbox" wire:model.live="selectedItems" value="file-{{ $projectFile->id }}" class="rounded border-gray-300"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                     @if($renamingType === 'project_file' && $renamingId === $projectFile->id)
                                        <input type="text" wire:model="renamingName" wire:keydown.enter="saveRename" wire:keydown.escape="cancelRenaming" class="border-gray-300 rounded-md shadow-sm w-full" autofocus>
                                    @else
                                        <span class="flex items-center"><svg class="h-5 w-5 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg> {{ $projectFile->file_name }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Archivo</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $projectFile->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Menú Contextual -->
    <div x-show="contextMenu.open" x-transition :style="`top: ${contextMenu.y}px; left: ${contextMenu.x}px;`" class="fixed bg-white rounded-md shadow-lg border w-48 py-1 z-50">
        <template x-if="contextMenu.type === 'project_file'">
            <div>
                <a href="#" @click.prevent="$wire.showPreview(contextMenu.id); closeContextMenu();" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ver</a>
                <a href="#" @click.prevent="$wire.downloadFile(contextMenu.id)" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Descargar</a>
            </div>
        </template>
        <a href="#" @click.prevent="$wire.startRenaming(contextMenu.type, contextMenu.id, contextMenu.name); closeContextMenu();" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Renombrar</a>
        <a href="#" @click.prevent="if (confirm('¿Estás seguro?')) { $wire.delete(contextMenu.type, contextMenu.id); } closeContextMenu();" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Eliminar</a>
    </div>

    <!-- Modal para Vista Previa -->
    @if ($showPreviewModal)
        <div class="fixed z-20 inset-0 overflow-y-auto" x-data @keydown.escape.window="$wire.closePreview()">
            <div class="flex items-center justify-center min-h-screen">
                <div wire:click="closePreview()" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-4xl" style="height: 90vh;">
                    <div class="flex justify-between items-center p-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Vista Previa del Archivo</h3>
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