<?php

namespace App\Domain\Contracts;

use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReportRepositoryInterface
{
    public function create(array $data): Report;
    public function delete(int $id): bool;
    public function findById(int $id): ?Report;
    public function getPaginated(string $search = '', int $perPage = 10): LengthAwarePaginator;
    public function getForUserPaginated(int $userId, string $search = '', int $perPage = 10): LengthAwarePaginator;
    public function getTotalCount(): int;
    public function getMonthlyActivity(int $months = 6): array;
}
