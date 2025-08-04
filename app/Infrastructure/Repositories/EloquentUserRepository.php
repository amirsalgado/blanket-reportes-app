<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function create(array $data): User
    {
        // Aseguramos que la contraseña se encripte y el rol sea 'cliente'
        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'cliente';
        return User::create($data);
    }

    public function update(int $id, array $data): bool
    {
        // Removemos la contraseña del array si está vacía para no sobreescribirla
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        return User::find($id)->update($data);
    }

    public function delete(int $id): bool
    {
        return User::destroy($id);
    }

    public function getClientsPaginated(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        return User::where('role', 'cliente')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('nit', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);
    }
}
