<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Domain\Enums\ClientType;

new class extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showModal = false;
    public ?int $clientId = null;
    public string $modalTitle = '';

    // Propiedades del formulario
    public string $name = '';
    public string $email = '';
    public ?string $company = null;
    public ?string $client_type = null;
    public ?string $nit = null;
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = 'cliente';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->clientId)],
            'role' => ['required', Rule::in(['cliente', 'admin'])],
            'client_type' => [Rule::requiredIf($this->role === 'cliente'), 'nullable', Rule::in(array_column(ClientType::cases(), 'value'))],
            'company' => [Rule::requiredIf($this->client_type === ClientType::JURIDICA->value), 'nullable', 'string', 'max:255'],
            'nit' => [Rule::requiredIf($this->client_type === ClientType::JURIDICA->value), 'nullable', 'string', 'max:255', Rule::unique('users')->ignore($this->clientId)],
            'password' => [$this->clientId ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ];
    }

    public function updating($property): void
    {
        if ($property === 'search') {
            $this->resetPage();
        }
    }

    public function openModal(int $id = null): void
    {
        $this->resetValidation();
        $this->resetExcept('showModal');

        if ($id) {
            $user = resolve(UserRepositoryInterface::class)->findById($id);
            $this->clientId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->company = $user->company;
            $this->client_type = $user->client_type?->value;
            $this->nit = $user->nit;
            $this->role = $user->role;
            $this->modalTitle = 'Editar Usuario';
        } else {
            $this->modalTitle = 'Crear Nuevo Usuario';
        }

        $this->showModal = true;
    }

    public function save(UserRepositoryInterface $userRepository): void
    {
        $data = $this->validate();

        if ($data['role'] === 'admin') {
            $data['client_type'] = null;
            $data['company'] = null;
            $data['nit'] = null;
        }

        if ($this->clientId) {
            $userRepository->update($this->clientId, $data);
        } else {
            $userRepository->create($data);
        }

        $this->showModal = false;
        $this->dispatch('swal:success', message: 'Usuario guardado con éxito.');
    }

    public function delete(int $id, UserRepositoryInterface $userRepository): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('swal:error', message: 'No puedes eliminar tu propia cuenta.');
            return;
        }
        $userRepository->delete($id);
        $this->dispatch('swal:success', message: 'Usuario eliminado con éxito.');
    }

    public function with(UserRepositoryInterface $userRepository): array
    {
        return [
            'users' => $userRepository->getUsersPaginated($this->search),
        ];
    }
}; ?>

<div>
    <div class="p-4 sm:p-6 lg:p-8 bg-white rounded-lg shadow">
        <h1 class="text-2xl font-semibold text-gray-900">Gestión de Usuarios</h1>

        <!-- Controles Superiores: Búsqueda y Botón -->
        <div class="py-4 flex justify-between items-center">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre, email o empresa..." class="w-1/3 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <button wire:click="openModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md">
                Nuevo Usuario
            </button>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="shadow overflow-x-auto border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre / Razón Social</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr wire:key="{{ $user->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->company ?? $user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span @class([
                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                    'bg-blue-100 text-blue-800' => $user->role === 'admin',
                                    'bg-green-100 text-green-800' => $user->role === 'cliente',
                                ])>
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="openModal({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                                <button wire:click="delete({{ $user->id }})" wire:confirm="¿Estás seguro de que deseas eliminar este usuario?" class="text-red-600 hover:text-red-900 ml-4">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No se encontraron usuarios</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        @if(empty($search))
                                            Aún no hay usuarios creados. ¡Crea el primero!
                                        @else
                                            Intenta ajustar tu búsqueda.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </div>

    <!-- Modal para Crear/Editar -->
    @if($showModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="$set('showModal', false)" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">{{ $modalTitle }}</h3>
                            <div class="mt-4 space-y-4">
                                <!-- Selector de Rol -->
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700">Rol del Usuario</label>
                                    <select wire:model.live="role" id="role" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="cliente">Cliente</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                    @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Campos condicionales para el rol 'cliente' -->
                                @if ($role === 'cliente')
                                    <div>
                                        <label for="client_type" class="block text-sm font-medium text-gray-700">Tipo de Cliente</label>
                                        <select wire:model.live="client_type" id="client_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            <option value="">Seleccione un tipo...</option>
                                            @foreach(App\Domain\Enums\ClientType::cases() as $type)
                                                <option value="{{ $type->value }}">{{ ucfirst($type->value) }}</option>
                                            @endforeach
                                        </select>
                                        @error('client_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    @if($client_type === 'juridica')
                                        <div>
                                            <label for="company" class="block text-sm font-medium text-gray-700">Razón Social</label>
                                            <input type="text" wire:model="company" id="company" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('company') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label for="nit" class="block text-sm font-medium text-gray-700">NIT</label>
                                            <input type="text" wire:model="nit" id="nit" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            @error('nit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                @endif

                                <!-- Campos Comunes -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">{{ $role === 'cliente' && $client_type === 'juridica' ? 'Nombre del Contacto' : 'Nombre Completo' }}</label>
                                    <input type="text" wire:model="name" id="name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                                    <input type="email" wire:model="email" id="email" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <!-- Campos de Contraseña -->
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña {{ $clientId ? '(Dejar en blanco para no cambiar)' : '' }}</label>
                                    <input type="password" wire:model="password" id="password" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                                    <input type="password" wire:model="password_confirmation" id="password_confirmation" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                <span wire:loading wire:target="save" class="mr-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                Guardar
                            </button>
                            <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>