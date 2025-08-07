<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\ReportRepositoryInterface;
use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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

    public function getTotalCount(): int
    {
        return Report::count();
    }

    public function getMonthlyActivity(int $months = 6): array
    {
        $activity = Report::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Formatear los datos para la gráfica
        $labels = [];
        $data = [];

        // Rellenar con meses vacíos para una gráfica continua
        for ($i = $months; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M'); // 'Jan', 'Feb', etc.
            $data[$date->format('Y-n')] = 0;
        }

        foreach ($activity as $record) {
            $key = $record->year . '-' . $record->month;
            if (isset($data[$key])) {
                $data[$key] = $record->count;
            }
        }

        return [
            'labels' => $labels,
            'data' => array_values($data),
        ];
    }
}
