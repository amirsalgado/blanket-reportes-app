<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Domain\Contracts\UserRepositoryInterface;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(UserRepositoryInterface $userRepository): array
    {
        return [
            'clients' => $userRepository->getClientsPaginated($this->search),
        ];
    }
}; ?>

<div class="p-4 sm:p-6 lg:p-8 bg-white rounded-lg shadow">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Proyectos por Cliente</h1>
            <p class="mt-1 text-sm text-gray-600">Selecciona un cliente para gestionar sus archivos y carpetas.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Buscar por empresa, contacto o email..." 
                class="block w-full sm:w-72 border-gray-300 rounded-md shadow-sm">
        </div>
    </div>

    <!-- Tabla de Clientes -->
    <div class="shadow overflow-x-auto border-b border-gray-200 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Razón Social</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Gestionar</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($clients as $client)
                    <tr wire:key="{{ $client->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $client->company ?? $client->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $client->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $client->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.projects.show', $client) }}" class="text-indigo-600 hover:text-indigo-900">Gestionar Proyectos</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron clientes</h3>
                                <p class="mt-1 text-sm text-gray-500">Intenta ajustar tu búsqueda o crea un nuevo cliente.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    @if ($clients->hasPages())
        <div class="mt-6">
            {{ $clients->links() }}
        </div>
    @endif
</div>