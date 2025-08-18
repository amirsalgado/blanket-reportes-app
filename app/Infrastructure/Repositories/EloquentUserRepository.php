<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

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
     * Crea un nuevo usuario (cliente) en la base de datos.
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        // Aseguramos que la contraseña se encripte y el rol sea 'cliente' por defecto
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'cliente';

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
        // Si la contraseña viene vacía en el formulario, la removemos del array
        // para no sobreescribir la existente con un valor nulo.
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
     * Obtiene una lista paginada de todos los usuarios con rol 'cliente'.
     * Permite filtrar por un término de búsqueda.
     *
     * @param string $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersPaginated(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        return User::where('role', 'cliente')
            ->where('role', '!=', 'super-admin')
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
