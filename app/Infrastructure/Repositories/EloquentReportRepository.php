<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Contracts\ReportRepositoryInterface;
use App\Models\Report;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public function getPaginated(string $search = '', int $perPage = 10, ?string $startDate = null, ?string $endDate = null): LengthAwarePaginator
{
    return Report::with('user')
        // Añadimos los filtros de fecha. `whereDate` se asegura de comparar solo la parte de la fecha.
        ->when($startDate, fn($query) => $query->whereDate('reports.created_at', '>=', $startDate))
        ->when($endDate, fn($query) => $query->whereDate('reports.created_at', '<=', $endDate))
        ->when($search, function ($query, $search) {
            // Se anida la búsqueda de texto para que no interfiera con los filtros de fecha.
            $query->where(function($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        })
        ->latest('reports.created_at') // Ordenar por fecha de creación del reporte
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

    public function getTotalCount(?string $startDate = null, ?string $endDate = null): int
    {
        return Report::when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })
            ->count();
    }

    public function getMonthlyActivity(?string $startDate = null, ?string $endDate = null): array
    {
        // Si no hay fechas, se muestran los últimos 5 meses más el actual.
        $start = $startDate ? Carbon::parse($startDate) : now()->subMonths(5)->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        // 1. Obtenemos los datos reales de la base de datos.
        $activity = Report::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy(function ($item) {
                // Creamos una clave 'YYYY-M' para un acceso fácil (ej: '2025-8')
                return $item->year . '-' . $item->month;
            });

        $labels = [];
        $data = [];

        // 2. Iteramos mes por mes desde la fecha de inicio hasta la de fin.
        $period = $start->copy();
        while ($period->lessThanOrEqualTo($end)) {
            $key = $period->format('Y-n');
            $labels[] = $period->format('M Y'); // Formato de etiqueta, ej: 'Ago 2025'

            // 3. Si encontramos datos para ese mes en la consulta, los usamos. Si no, el valor es 0.
            $data[] = $activity->get($key)->count ?? 0;

            $period->addMonth();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
