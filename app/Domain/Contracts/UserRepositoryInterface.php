<?php

namespace App\Domain\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function create(array $data): User;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getClientsPaginated(string $search = '', int $perPage = 10): LengthAwarePaginator;
    public function getActiveClientsCount(): int;
}
