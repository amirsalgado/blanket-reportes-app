<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * Encuentra un usuario por su ID.
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        // Aseguramos que la contraseña se encripte.
        // El rol es asignado desde el componente que llama a este método.
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    /**
     * Actualiza los datos de un usuario existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {        
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->findById($id);
        if ($user) {
            return $user->update($data);
        }
        return false;
    }

    /**
     * Elimina un usuario de la base de datos.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return User::destroy($id);
    }

    /**
     * Obtiene una lista paginada de usuarios.
     * El usuario de soporte puede ver a todos, mientras que los otros administradores solo ven clientes.
     * Permite filtrar por un término de búsqueda.
     *
     * @param string $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersPaginated(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $query = User::query();

        if (Auth::user()?->email !== 'soporte@compugigas.com') {
            $query->where('role', 'cliente');
        }

        return $query->where('email', '!=', 'soporte@compugigas.com') // Excluir al usuario de soporte de la lista
            ->when($search, function ($query, $search) {
                // Busca en múltiples columnas para un filtrado más completo
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('nit', 'like', "%{$search}%");
                });
            })
            ->latest() // Ordena los más recientes primero
            ->paginate($perPage);
    }

    /**
     * Obtiene la cantidad total de clientes
     *
     * @return integer
     */

    public function getActiveClientsCount(?string $startDate = null, ?string $endDate = null): int
    {
        return User::where('role', 'cliente')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->count();
    }
}
