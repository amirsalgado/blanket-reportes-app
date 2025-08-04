<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Domain\Enums\ClientType;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

new class extends Component {
    use WithPagination;

    // Propiedades para la UI
    public string $search = '';
    public bool $showModal = false;
    public ?int $clientId = null;
    public string $modalTitle = '';

    // Propiedades del modelo User
    public string $name = '';
    public string $email = '';
    public ?string $company = null;
    public ?string $client_type = null;
    public ?string $nit = null;
    public string $password = '';
    public string $password_confirmation = '';

    // Inyecta el repositorio para usarlo en los métodos
    public function getRepository(): UserRepositoryInterface
    {
        return resolve(UserRepositoryInterface::class);
    }

    // Reglas de validación dinámicas
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($this->clientId)],
            'client_type' => ['required', Rule::in(array_column(ClientType::cases(), 'value'))],
            'company' => [Rule::requiredIf($this->client_type === ClientType::JURIDICA->value), 'nullable', 'string', 'max:255'],
            'nit' => [Rule::requiredIf($this->client_type === ClientType::JURIDICA->value), 'nullable', 'string', 'max:255', Rule::unique('users')->ignore($this->clientId)],
            'password' => [$this->clientId ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ];
    }

    // Abre el modal, ya sea para crear o para editar
    public function openModal(int $id = null): void
    {
        $this->resetValidation();
        $this->resetExcept('showModal');

        if ($id) {
            $client = $this->getRepository()->findById($id);
            $this->clientId = $client->id;
            $this->name = $client->name;
            $this->email = $client->email;
            $this->company = $client->company;
            $this->client_type = $client->client_type?->value;
            $this->nit = $client->nit;
            $this->modalTitle = 'Editar Cliente';
        } else {
            $this->modalTitle = 'Crear Nuevo Cliente';
        }

        $this->showModal = true;
    }

    // Guarda o actualiza un cliente
    public function save(): void
    {
        $data = $this->validate();
        $repository = $this->getRepository();

        if ($this->clientId) {
            $repository->update($this->clientId, $data);
        } else {
            $repository->create($data);
        }

        $this->showModal = false;
        // Aquí podrías despachar un evento para notificaciones (ej. con SweetAlert)
    }

    // Elimina un cliente
    public function delete(int $id): void
    {
        $this->getRepository()->delete($id);
    }

    // El método with() pasa los datos a la vista
    public function with(): array
    {
        return [
            'clients' => $this->getRepository()->getClientsPaginated($this->search),
        ];
    }
}; ?>

<div>
    <div class="p-4 sm:p-6 lg:p-8 bg-white rounded-lg shadow">
        <h1 class="text-2xl font-semibold text-gray-900">Gestión de Clientes</h1>

        <!-- Controles Superiores: Búsqueda y Botón -->
        <div class="py-4 flex justify-between items-center">
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por nombre, email, empresa o NIT..."
                class="w-1/3 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <button wire:click="openModal()"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md">
                Nuevo Cliente
            </button>
        </div>

        <!-- Tabla de Clientes -->
        <div class="shadow overflow-x-auto border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre / Razón Social</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contacto</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($clients as $client)
                        <tr wire:key="{{ $client->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">{{ $client->company ?? $client->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $client->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap capitalize">{{ $client->client_type?->value }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="openModal({{ $client->id }})"
                                    class="text-indigo-600 hover:text-indigo-900">Editar</button>
                                <button wire:click="delete({{ $client->id }})"
                                    wire:confirm="¿Estás seguro de que deseas eliminar este cliente?"
                                    class="text-red-600 hover:text-red-900 ml-4">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No se
                                encontraron clientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $clients->links() }}</div>
    </div>

    <!-- Modal para Crear/Editar -->
    @if ($showModal)
        <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div wire:click="$set('showModal', false)"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">{{ $modalTitle }}
                            </h3>
                            <div class="mt-4 space-y-4">
                                <!-- Selector de Tipo de Cliente -->
                                <div>
                                    <label for="client_type" class="block text-sm font-medium text-gray-700">Tipo de
                                        Cliente</label>
                                    <select wire:model.live="client_type" id="client_type"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="">Seleccione un tipo...</option>
                                        @foreach (App\Domain\Enums\ClientType::cases() as $type)
                                            <option value="{{ $type->value }}">{{ ucfirst($type->value) }}</option>
                                        @endforeach
                                    </select>
                                    @error('client_type')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Campos Condicionales -->
                                @if ($client_type === 'juridica')
                                    <div>
                                        <label for="company" class="block text-sm font-medium text-gray-700">Razón
                                            Social</label>
                                        <input type="text" wire:model="company" id="company"
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('company')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="nit"
                                            class="block text-sm font-medium text-gray-700">NIT</label>
                                        <input type="text" wire:model="nit" id="nit"
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        @error('nit')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                <!-- Campos Comunes -->
                                <div>
                                    <label for="name"
                                        class="block text-sm font-medium text-gray-700">{{ $client_type === 'juridica' ? 'Nombre del Contacto' : 'Nombre y Apellido' }}</label>
                                    <input type="text" wire:model="name" id="name"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('name')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Correo
                                        Electrónico</label>
                                    <input type="email" wire:model="email" id="email"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('email')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Campos de Contraseña -->
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña
                                        {{ $clientId ? '(Dejar en blanco para no cambiar)' : '' }}</label>
                                    <input type="password" wire:model="password" id="password"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('password')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label for="password_confirmation"
                                        class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                                    <input type="password" wire:model="password_confirmation" id="password_confirmation"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">Guardar</button>
                            <button type="button" wire:click="$set('showModal', false)"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
