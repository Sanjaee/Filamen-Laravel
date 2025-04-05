<?php


namespace App\Filament\Resources\DashboardResource\Widgets;

use App\Models\Treatment;
use App\Models\Patient;
use App\Models\Owner;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TreatmentsChart extends ChartWidget
{
    protected static ?string $heading = 'Treatments Overview';

    protected function getData(): array
    {
        // Get treatments data with relationship information
        $treatments = Treatment::select(
                DB::raw('EXTRACT(MONTH FROM treatments.created_at) as month'),
                DB::raw('EXTRACT(YEAR FROM treatments.created_at) as year'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(treatments.price) as total_revenue'),
                DB::raw('COUNT(DISTINCT treatments.patient_id) as unique_patients'),
                DB::raw('COUNT(DISTINCT patients.owner_id) as unique_owners')
            )
            ->join('patients', 'treatments.patient_id', '=', 'patients.id')
            ->whereDate('treatments.created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $treatmentCounts = [];
        $revenueData = [];
        $patientCounts = [];

        foreach ($treatments as $treatment) {
            $monthName = Carbon::createFromDate($treatment->year, $treatment->month, 1)->format('M Y');
            $labels[] = $monthName;
            $treatmentCounts[] = $treatment->count;
            $revenueData[] = $treatment->total_revenue / 100; // Assuming price is stored in cents
            $patientCounts[] = $treatment->unique_patients;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Number of Treatments',
                    'data' => $treatmentCounts,
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#36A2EB',
                ],
                [
                    'label' => 'Unique Patients',
                    'data' => $patientCounts,
                    'backgroundColor' => '#FF6384',
                    'borderColor' => '#FF6384',
                    'type' => 'line',
                ],
                [
                    'label' => 'Revenue (in currency)',
                    'data' => $revenueData,
                    'backgroundColor' => '#4BC0C0',
                    'borderColor' => '#4BC0C0',
                    'yAxisID' => 'y1',
                ]
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Count'
                    ]
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue'
                    ]
                ],
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.2 // Slightly curved lines
                ]
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top'
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false
                ]
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }

    // Methods to filter data
    public function getPatientTreatments(int $patientId): array
    {
        $treatments = Treatment::where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Process data for the specific patient
        return $this->formatTreatmentData($treatments);
    }
    
    public function getOwnerTreatments(int $ownerId): array
    {
        $treatments = Treatment::join('patients', 'treatments.patient_id', '=', 'patients.id')
            ->where('patients.owner_id', $ownerId)
            ->orderBy('treatments.created_at', 'desc')
            ->get();
            
        // Process data for all the owner's patients
        return $this->formatTreatmentData($treatments);
    }
    
    private function formatTreatmentData($treatments): array
    {
        // Format data for chart display
        // Implementation depends on your specific needs
        return [];
    }

    protected static ?array $middleware = ['auth'];

    // Adjust the polling interval (in seconds) or set to null to disable polling
    protected static ?string $pollingInterval = null;
    
    // Customize the widget size
    protected int | string | array $columnSpan = 'full';
}