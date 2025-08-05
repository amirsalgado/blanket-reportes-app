<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\ReportRepositoryInterface;
use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class EloquentReportRepository implements ReportRepositoryInterface
{
    public function create(array $data): Report
    {
        return Report::create($data);
    }

    public function delete(int $id): bool
    {
        $report = $this->findById($id);
        if ($report) {
            // Eliminar el archivo físico del storage antes de borrar el registro
            Storage::disk('private')->delete($report->file_path);
            return $report->delete();
        }
        return false;
    }

    public function findById(int $id): ?Report
    {
        return Report::find($id);
    }

    public function getPaginated(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        return Report::with('user') // Carga la relación con el cliente
            ->when($search, function ($query, $search) {
                $query->where('file_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function getForUserPaginated(int $userId, string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        return Report::where('user_id', $userId)
            ->when($search, function ($query, $search) {
                $query->where('file_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);
    }
}
