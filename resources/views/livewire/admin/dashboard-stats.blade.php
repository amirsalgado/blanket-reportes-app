<?php

use Livewire\Volt\Component;
use App\Domain\Contracts\UserRepositoryInterface;
use App\Domain\Contracts\ReportRepositoryInterface;

// CORRECCIÓN: Se reemplaza el uso de `state()` por el método `mount()`
// para una inicialización de estado y manejo de dependencias más robusto.
new class extends Component {
    public int $totalReports;
    public int $activeClients;
    public array $chartData;

    /**
     * El método mount es el estándar para inicializar el estado del componente.
     * Livewire inyectará automáticamente los repositorios.
     */
    public function mount(ReportRepositoryInterface $reportRepository, UserRepositoryInterface $userRepository): void
    {
        $this->totalReports = $reportRepository->getTotalCount();
        $this->activeClients = $userRepository->getActiveClientsCount();
        $this->chartData = $reportRepository->getMonthlyActivity();
    }
};

?>

<div>
    <!-- Contenedor de KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tarjeta de Reportes Subidos -->
        <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
            <div class="bg-blanket-blue text-white rounded-full p-4 mr-4">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm">Reportes Subidos</p>
                <p class="text-3xl font-bold text-blanket-blue">{{ $totalReports }}</p>
            </div>
        </div>

        <!-- Tarjeta de Clientes Activos -->
        <div class="bg-blanket-yellow text-blanket-blue p-6 rounded-lg shadow-md flex items-center">
            <div class="bg-white text-blanket-blue rounded-full p-4 mr-4">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="font-semibold text-sm">Clientes Activos</p>
                <p class="text-3xl font-bold">{{ $activeClients }}</p>
            </div>
        </div>
    </div>

    <!-- Contenedor de la Gráfica -->
    <div class="mt-8 bg-white p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold mb-4 text-blanket-blue">Actividad Reciente</h3>
        {{-- El `wire:ignore` es importante para que Livewire no interfiera con Chart.js --}}
        <div wire:ignore>
            <canvas id="activityChart"></canvas>
        </div>
    </div>

    {{-- Incluimos la librería Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Escuchamos un evento de Livewire para inicializar la gráfica
        document.addEventListener('livewire:navigated', () => {
            const ctx = document.getElementById('activityChart');
            if (ctx) {
                // Destruir la gráfica anterior si existe para evitar duplicados
                if (window.myChart instanceof Chart) {
                    window.myChart.destroy();
                }
                window.myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($chartData['labels']),
                        datasets: [{
                            label: 'Reportes por Mes',
                            data: @json($chartData['data']),
                            fill: true,
                            backgroundColor: 'rgba(215, 240, 69, 0.2)', // Amarillo con transparencia
                            borderColor: 'rgba(215, 240, 69, 1)', // Amarillo sólido
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        });
    </script>
</div>
