<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\ReportRepositoryInterface;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EloquentReportRepository implements ReportRepositoryInterface
{
    public function findById(int $id): ?Report
    {
        return Report::find($id);
    }

    public function getPaginated(string $search = '', int $perPage = 10, ?string $startDate = null, ?string $endDate = null): LengthAwarePaginator
    {
        return Report::with('user')
            ->when($search, function ($query, $search) {
                $query->where('file_name', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('company', 'like', "%{$search}%");
                    });
            })
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->latest()
            ->paginate($perPage);
    }

    // --- MÉTODO AÑADIDO ---
    // Este método faltaba y causaba el error.
    public function getForUserPaginated(int $userId, string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        return Report::where('user_id', $userId)
            ->when($search, function ($query, $search) {
                $query->where('file_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Report
    {
        return Report::create($data);
    }

    public function delete(int $id): bool
    {
        return Report::destroy($id);
    }

    public function getTotalCount(?string $startDate = null, ?string $endDate = null): int
    {
        return Report::query()
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->count();
    }

    public function getMonthlyActivity(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->subMonths(5)->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfMonth();

        $activity = Report::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('YEAR(created_at)'), 'asc')
            ->orderBy(DB::raw('MONTH(created_at)'), 'asc')
            ->get();

        // Formatear los datos para la gráfica
        $labels = [];
        $data = [];
        $currentDate = $start->copy();

        while ($currentDate <= $end) {
            $labels[] = $currentDate->format('M Y');
            $data[$currentDate->format('Y-n')] = 0;
            $currentDate->addMonth();
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