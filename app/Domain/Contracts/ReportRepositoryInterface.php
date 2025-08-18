<?php

namespace App\Domain\Contracts;

use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReportRepositoryInterface
{
    public function create(array $data): Report;
    public function delete(int $id): bool;
    public function findById(int $id): ?Report;
    public function getPaginated(string $search = '', int $perPage = 10, ?string $startDate = null, ?string $endDate = null): LengthAwarePaginator;
    public function getForUserPaginated(int $userId, string $search = '', int $perPage = 10): LengthAwarePaginator;
    public function getTotalCount(?string $startDate = null, ?string $endDate = null): int;
    public function getMonthlyActivity(?string $startDate = null, ?string $endDate = null): array;
}
