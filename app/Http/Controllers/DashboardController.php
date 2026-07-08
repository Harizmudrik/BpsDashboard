<?php
// Automated Deployment Sync v2.1

namespace App\Http\Controllers;

use App\Models\BpsStatistic;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the BPS Homepage with metric slider.
     */
    public function home()
    {
        $metricsConfig = [
            'tpt' => [
                'label' => 'Tingkat Pengangguran Terbuka',
                'unit' => '%',
                'icon' => 'briefcase',
                'national_code' => '9999',
            ],
            'gini_ratio' => [
                'label' => 'Gini Ratio',
                'unit' => '',
                'icon' => 'scale',
                'national_code' => '9999',
            ],
            'kemiskinan' => [
                'label' => 'Persentase Penduduk Miskin',
                'unit' => '%',
                'icon' => 'home',
                'national_code' => '9999',
            ],
            'inflasi_tahunan' => [
                'label' => 'Inflasi Tahunan (YoY)',
                'unit' => '%',
                'icon' => 'trending-up',
                'national_code' => '9999',
            ],
            'inflasi_bulanan' => [
                'label' => 'Inflasi Bulanan (MtM)',
                'unit' => '%',
                'icon' => 'activity',
                'national_code' => '9999',
            ],
            'pdb_growth' => [
                'label' => 'Laju Pertumbuhan PDB',
                'unit' => '%',
                'icon' => 'pie-chart',
                'national_code' => '99003', // C. PRODUK DOMESTIK BRUTO
            ],
            'ihpb' => [
                'label' => 'Indeks Harga Perdagangan Besar',
                'unit' => '',
                'icon' => 'shopping-cart',
                'national_code' => null, // Average of all sectors
            ],
        ];

        $cards = [];

        foreach ($metricsConfig as $key => $conf) {
            // Find latest record for this metric
            $query = BpsStatistic::where('metric', $key);
            
            if ($conf['national_code']) {
                $query->where('kode_wilayah', $conf['national_code']);
            }

            $latestRecord = $query->orderBy('tahun', 'desc')
                ->orderByRaw("CASE 
                    WHEN periode = 'Tahunan' THEN 13 
                    WHEN periode = 'Desember' THEN 12
                    WHEN periode = 'November' THEN 11
                    WHEN periode = 'Oktober' THEN 10
                    WHEN periode = 'September' THEN 9
                    WHEN periode = 'Agustus' THEN 8
                    WHEN periode = 'Juli' THEN 7
                    WHEN periode = 'Juni' THEN 6
                    WHEN periode = 'Mei' THEN 5
                    WHEN periode = 'April' THEN 4
                    WHEN periode = 'Maret' THEN 3
                    WHEN periode = 'Februari' THEN 2
                    WHEN periode = 'Januari' THEN 1
                    ELSE 0 END DESC")
                ->first();

            if ($latestRecord) {
                $value = (float)$latestRecord->value;
                $tahun = $latestRecord->tahun;
                $periode = $latestRecord->periode;
                
                // Calculate previous period value for trend
                $prevValue = null;
                $prevYear = (string)((int)$tahun - 1);
                
                $prevQuery = BpsStatistic::where('metric', $key)
                    ->where('tahun', $prevYear)
                    ->where('periode', $periode);

                if ($conf['national_code']) {
                    $prevQuery->where('kode_wilayah', $conf['national_code']);
                }

                $prevRecord = $prevQuery->first();
                if ($prevRecord) {
                    $prevValue = (float)$prevRecord->value;
                }

                $trend = null;
                if ($prevValue !== null) {
                    $trend = round($value - $prevValue, 2);
                }

                $cards[] = [
                    'key' => $key,
                    'label' => $conf['label'],
                    'value' => number_format($value, $key === 'gini_ratio' ? 3 : 2),
                    'unit' => $conf['unit'],
                    'icon' => $conf['icon'],
                    'tahun' => $tahun,
                    'periode' => $periode,
                    'trend' => $trend,
                ];
            } else {
                // If it's IHPB and has no direct national record, calculate average of all sectors
                if ($key === 'ihpb') {
                    $latestItem = BpsStatistic::where('metric', 'ihpb')->orderBy('tahun', 'desc')->first();
                    if ($latestItem) {
                        $tahun = $latestItem->tahun;
                        $periode = $latestItem->periode;
                        $avgVal = BpsStatistic::where('metric', 'ihpb')
                            ->where('tahun', $tahun)
                            ->where('periode', $periode)
                            ->avg('value');

                        $prevYear = (string)((int)$tahun - 1);
                        $prevAvg = BpsStatistic::where('metric', 'ihpb')
                            ->where('tahun', $prevYear)
                            ->where('periode', $periode)
                            ->avg('value');

                        $trend = null;
                        if ($prevAvg) {
                            $trend = round((float)$avgVal - (float)$prevAvg, 2);
                        }

                        $cards[] = [
                            'key' => $key,
                            'label' => $conf['label'],
                            'value' => number_format((float)$avgVal, 2),
                            'unit' => $conf['unit'],
                            'icon' => $conf['icon'],
                            'tahun' => $tahun,
                            'periode' => $periode,
                            'trend' => $trend,
                        ];
                    }
                }
            }
        }

        $provincialMetrics = ['tpt', 'gini_ratio', 'kemiskinan', 'inflasi_tahunan'];
        $provincesData = [];

        foreach ($provincialMetrics as $m) {
            $maxYear = BpsStatistic::where('metric', $m)->max('tahun');
            if ($maxYear) {
                $latestPeriodRecord = BpsStatistic::where('metric', $m)
                    ->where('tahun', $maxYear)
                    ->orderByRaw("CASE 
                        WHEN periode = 'Tahunan' THEN 13 
                        WHEN periode = 'Desember' THEN 12
                        WHEN periode = 'November' THEN 11
                        WHEN periode = 'Oktober' THEN 10
                        WHEN periode = 'September' THEN 9
                        WHEN periode = 'Agustus' THEN 8
                        WHEN periode = 'Juli' THEN 7
                        WHEN periode = 'Juni' THEN 6
                        WHEN periode = 'Mei' THEN 5
                        WHEN periode = 'April' THEN 4
                        WHEN periode = 'Maret' THEN 3
                        WHEN periode = 'Februari' THEN 2
                        WHEN periode = 'Januari' THEN 1
                        ELSE 0 END DESC")
                    ->first();
                
                if ($latestPeriodRecord) {
                    $period = $latestPeriodRecord->periode;
                    $items = BpsStatistic::where('metric', $m)
                        ->where('tahun', $maxYear)
                        ->where('periode', $period)
                        ->where('kode_wilayah', '!=', '9999')
                        ->where('kode_wilayah', '!=', '0000')
                        ->get();
                    
                    foreach ($items as $item) {
                        $code = $item->kode_wilayah;
                        if (!isset($provincesData[$code])) {
                            $provincesData[$code] = [
                                'name' => $item->nama_wilayah,
                                'tpt' => null,
                                'gini_ratio' => null,
                                'kemiskinan' => null,
                                'inflasi_tahunan' => null,
                            ];
                        }
                        $provincesData[$code][$m] = (float)$item->value;
                    }
                }
            }
        }

        return view('home', compact('cards', 'provincesData'));
    }

    /**
     * Display a dedicated detail page for a metric.
     */
    public function detail($metric)
    {
        $metricsConfig = [
            'tpt' => [
                'label' => 'Tingkat Pengangguran Terbuka',
                'unit' => '%',
                'description' => 'Persentase jumlah pengangguran terhadap jumlah angkatan kerja. TPT mengindikasikan besarnya angkatan kerja yang tidak terserap oleh lapangan kerja.',
                'is_provincial' => true,
            ],
            'gini_ratio' => [
                'label' => 'Gini Ratio',
                'unit' => '',
                'description' => 'Ukuran kemerataan pendapatan masyarakat yang berkisar antara 0 (kemerataan sempurna) hingga 1 (ketimpangan sempurna). Semakin rendah angka Gini Ratio, semakin merata pendapatan masyarakat.',
                'is_provincial' => true,
            ],
            'kemiskinan' => [
                'label' => 'Persentase Penduduk Miskin (P0)',
                'unit' => '%',
                'description' => 'Persentase penduduk yang memiliki rata-rata pengeluaran per kapita per bulan di bawah Garis Kemiskinan. Menunjukkan tingkat kemiskinan suatu wilayah.',
                'is_provincial' => true,
            ],
            'inflasi_tahunan' => [
                'label' => 'Inflasi Tahunan (YoY)',
                'unit' => '%',
                'description' => 'Kenaikan harga barang dan jasa secara umum dan terus menerus dalam jangka waktu satu tahun (dibandingkan dengan bulan yang sama di tahun sebelumnya).',
                'is_provincial' => true,
            ],
            'inflasi_bulanan' => [
                'label' => 'Inflasi Bulanan (MtM)',
                'unit' => '%',
                'description' => 'Kenaikan harga barang dan jasa secara umum pada suatu bulan dibandingkan dengan bulan sebelumnya.',
                'is_provincial' => false,
            ],
            'pdb_growth' => [
                'label' => 'Laju Pertumbuhan PDB',
                'unit' => '%',
                'description' => 'Laju pertumbuhan Produk Domestik Bruto (PDB) atas dasar harga konstan seri 2010 menurut lapangan usaha, menggambarkan perkembangan sektor-sektor ekonomi riil nasional.',
                'is_provincial' => false,
            ],
            'ihpb' => [
                'label' => 'Indeks Harga Perdagangan Besar',
                'unit' => '',
                'description' => 'Indeks yang menggambarkan perubahan harga pada tingkat perdagangan besar (grosir) dari komoditas-komoditas yang diperdagangkan.',
                'is_provincial' => false,
            ],
        ];

        if (!array_key_exists($metric, $metricsConfig)) {
            abort(404);
        }

        $config = $metricsConfig[$metric];

        // Fetch distinct filters
        $tahuns = BpsStatistic::where('metric', $metric)
            ->select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->get();

        $periodes = BpsStatistic::where('metric', $metric)
            ->select('periode')
            ->distinct()
            ->get();

        $provinsis = [];
        if ($config['is_provincial']) {
            $provinsis = BpsStatistic::where('metric', $metric)
                ->where('kode_wilayah', '!=', '9999')
                ->where('kode_wilayah', '!=', '0000')
                ->select('kode_wilayah', 'nama_wilayah')
                ->distinct()
                ->orderBy('nama_wilayah')
                ->get();
        } else {
            // For sectors or categories (like in PDB or IHPB)
            $provinsis = BpsStatistic::where('metric', $metric)
                ->select('kode_wilayah', 'nama_wilayah')
                ->distinct()
                ->orderBy('nama_wilayah')
                ->get();
        }

        return view('detail', compact('metric', 'config', 'tahuns', 'periodes', 'provinsis'));
    }

    /**
     * Get dynamic chart/table/map data for a metric.
     */
    public function getData($metric, Request $request)
    {
        $tahun = $request->input('tahun', 'all');
        $provinsi = $request->input('provinsi', 'all'); // represents kode_wilayah
        $periode = $request->input('periode', 'all');

        // Identify metric config
        $isProvincial = in_array($metric, ['tpt', 'gini_ratio', 'kemiskinan', 'inflasi_tahunan']);
        $nationalCode = in_array($metric, ['pdb_growth']) ? '99003' : '9999';

        // 1. Determine Default Values if 'all' is passed to query latest max
        $maxYear = BpsStatistic::where('metric', $metric)->max('tahun');
        $activeYear = ($tahun === 'all' || !$tahun) ? $maxYear : $tahun;

        $maxPeriodeQuery = BpsStatistic::where('metric', $metric)->where('tahun', $activeYear);
        // Sort periods logic
        $latestPeriodRecord = $maxPeriodeQuery->orderByRaw("CASE 
                    WHEN periode = 'Tahunan' THEN 13 
                    WHEN periode = 'Desember' THEN 12
                    WHEN periode = 'November' THEN 11
                    WHEN periode = 'Oktober' THEN 10
                    WHEN periode = 'September' THEN 9
                    WHEN periode = 'Agustus' THEN 8
                    WHEN periode = 'Juli' THEN 7
                    WHEN periode = 'Juni' THEN 6
                    WHEN periode = 'Mei' THEN 5
                    WHEN periode = 'April' THEN 4
                    WHEN periode = 'Maret' THEN 3
                    WHEN periode = 'Februari' THEN 2
                    WHEN periode = 'Januari' THEN 1
                    ELSE 0 END DESC")
                ->first();
        $activePeriode = ($periode === 'all' || !$periode) 
            ? ($latestPeriodRecord ? $latestPeriodRecord->periode : 'all') 
            : $periode;

        // 2. Fetch Base Stats for cards
        $statsQuery = BpsStatistic::where('metric', $metric)
            ->where('tahun', $activeYear);
        if ($activePeriode !== 'all') {
            $statsQuery->where('periode', $activePeriode);
        }

        // Filter out aggregate totals for provincial/regional statistics to get clean high/low/average
        if ($isProvincial) {
            $statsQuery->where('kode_wilayah', '!=', '9999')->where('kode_wilayah', '!=', '0000');
        } else {
            // For PDB or IHPB, filter out main aggregates when calculating min/max/avg
            if ($metric === 'pdb_growth') {
                $statsQuery->whereNotIn('kode_wilayah', ['99001', '99002', '99003']);
            }
        }

        $allRegionData = $statsQuery->get();

        $avgValue = $allRegionData->avg('value') ?? 0;
        $maxValueItem = $allRegionData->sortByDesc('value')->first();
        $minValueItem = $allRegionData->sortBy('value')->first();

        // Trend calculation for national / overall value
        $trend = null;
        $overallValue = 0;
        
        $nationalRecordQuery = BpsStatistic::where('metric', $metric)
            ->where('tahun', $activeYear);
        if ($activePeriode !== 'all') {
            $nationalRecordQuery->where('periode', $activePeriode);
        }

        if ($provinsi && $provinsi !== 'all') {
            $nationalRecordQuery->where('kode_wilayah', $provinsi);
        } elseif ($isProvincial) {
            $nationalRecordQuery->where('kode_wilayah', '9999');
        } else {
            if ($metric === 'pdb_growth') {
                $nationalRecordQuery->where('kode_wilayah', '99003');
            } else {
                // If it is IHPB or has no direct national, use overall average
                $nationalRecordQuery = null;
            }
        }

        $nationalRecord = $nationalRecordQuery ? $nationalRecordQuery->first() : null;
        
        if ($nationalRecord) {
            $overallValue = (float)$nationalRecord->value;
        } else {
            $overallValue = (float)$avgValue;
        }

        // Get value from previous year for comparison
        $prevYear = (string)((int)$activeYear - 1);
        $prevNationalQuery = BpsStatistic::where('metric', $metric)
            ->where('tahun', $prevYear);
        if ($activePeriode !== 'all') {
            $prevNationalQuery->where('periode', $activePeriode);
        }

        if ($provinsi && $provinsi !== 'all') {
            $prevNationalQuery->where('kode_wilayah', $provinsi);
        } elseif ($isProvincial) {
            $prevNationalQuery->where('kode_wilayah', '9999');
        } else {
            if ($metric === 'pdb_growth') {
                $prevNationalQuery->where('kode_wilayah', '99003');
            } else {
                $prevNationalQuery = null;
            }
        }

        $prevNationalRecord = $prevNationalQuery ? $prevNationalQuery->first() : null;
        if ($prevNationalRecord) {
            $trend = round($overallValue - (float)$prevNationalRecord->value, 2);
        } elseif ($metric === 'ihpb') {
            // Calculate trend based on avg
            $prevAvg = BpsStatistic::where('metric', 'ihpb')
                ->where('tahun', $prevYear)
                ->where('periode', $activePeriode)
                ->avg('value');
            if ($prevAvg) {
                $trend = round($avgValue - (float)$prevAvg, 2);
            }
        }

        $summary = [
            'value' => number_format($overallValue, $metric === 'gini_ratio' ? 3 : 2),
            'avg_val' => number_format($avgValue, $metric === 'gini_ratio' ? 3 : 2),
            'max_val' => $maxValueItem ? number_format((float)$maxValueItem->value, $metric === 'gini_ratio' ? 3 : 2) : '0.00',
            'max_name' => $maxValueItem ? $maxValueItem->nama_wilayah : '-',
            'min_val' => $minValueItem ? number_format((float)$minValueItem->value, $metric === 'gini_ratio' ? 3 : 2) : '0.00',
            'min_name' => $minValueItem ? $minValueItem->nama_wilayah : '-',
            'current_year' => $activeYear,
            'current_periode' => $activePeriode,
            'trend' => $trend,
        ];

        // 3. Line Chart Trend: Trend over years
        $trendQuery = BpsStatistic::selectRaw('tahun, AVG(value) as avg_val')
            ->where('metric', $metric);

        if ($provinsi && $provinsi !== 'all' && $isProvincial) {
            $trendQuery->where('kode_wilayah', $provinsi);
        } else {
            if ($isProvincial) {
                $trendQuery->where('kode_wilayah', '9999');
            } else {
                if ($metric === 'pdb_growth') {
                    $trendQuery->where('kode_wilayah', '99003');
                }
            }
        }

        if ($activePeriode !== 'all') {
            $trendQuery->where('periode', $activePeriode);
        }

        $trendData = $trendQuery->groupBy('tahun')->orderBy('tahun')->get();

        $lineChart = [
            'labels' => $trendData->pluck('tahun')->values(),
            'datasets' => [[
                'label' => $provinsi !== 'all' ? BpsStatistic::where('kode_wilayah', $provinsi)->value('nama_wilayah') : 'Nasional / Rata-rata',
                'data' => $trendData->pluck('avg_val')->map(fn($v) => round((float)$v, 3))->values(),
                'borderColor' => '#1F3C88',
                'backgroundColor' => 'rgba(31,60,136,0.08)',
                'fill' => true,
                'tension' => 0.3,
                'pointBackgroundColor' => '#3FA9F5',
            ]]
        ];

        // 4. Bar Chart: Comparative breakdown
        $barChartData = $allRegionData->sortByDesc('value');
        if ($isProvincial) {
            $barChartData = $barChartData->take(15); // Top 15 provinces
        }

        $barColors = $barChartData->map(function ($item) use ($metric) {
            $v = (float)$item->value;
            if ($metric === 'tpt') {
                if ($v >= 6) return '#e74c3c';
                if ($v >= 5) return '#f39c12';
                return '#3FA9F5';
            } elseif ($metric === 'gini_ratio') {
                if ($v >= 0.38) return '#e74c3c';
                if ($v >= 0.35) return '#f39c12';
                return '#3FA9F5';
            } elseif ($metric === 'kemiskinan') {
                if ($v >= 15) return '#e74c3c';
                if ($v >= 10) return '#f39c12';
                return '#3FA9F5';
            }
            return '#1F3C88';
        });

        $barChart = [
            'labels' => $barChartData->pluck('nama_wilayah')->values(),
            'datasets' => [[
                'label' => $activeYear . ' (' . $activePeriode . ')',
                'data' => $barChartData->pluck('value')->map(fn($v) => round((float)$v, 3))->values(),
                'backgroundColor' => $barColors->values(),
                'borderRadius' => 4,
            ]]
        ];

        // 5. Map choropleth data (ALWAYS populated for ALL provinces, regardless of filter)
        $mapData = [];
        // Always fetch ALL region data for the map (ignoring province filter)
        $mapQuery = BpsStatistic::where('metric', $metric)
            ->where('tahun', $activeYear);
        if ($activePeriode !== 'all') {
            $mapQuery->where('periode', $activePeriode);
        }

        if ($isProvincial) {
            $mapQuery->where('kode_wilayah', '!=', '9999')
                     ->where('kode_wilayah', '!=', '0000');
            $mapItems = $mapQuery->get();
            foreach ($mapItems as $item) {
                $mapData[$item->kode_wilayah] = (float)$item->value;
            }
        } elseif ($metric === 'inflasi_bulanan') {
            $mapItems = $mapQuery->get();
            $grouped = [];
            foreach ($mapItems as $item) {
                $code = $item->kode_wilayah;
                if ($code !== '9999' && strlen($code) >= 2) {
                    $provCode = substr($code, 0, 2) . '00';
                    if (!isset($grouped[$provCode])) {
                        $grouped[$provCode] = [];
                    }
                    $grouped[$provCode][] = (float)$item->value;
                }
            }
            foreach ($grouped as $provCode => $values) {
                $mapData[$provCode] = count($values) > 0 ? round(array_sum($values) / count($values), 2) : 0;
            }
        } elseif (in_array($metric, ['pdb_growth', 'ihpb'])) {
            $activeSectorValue = $overallValue;
            if ($provinsi && $provinsi !== 'all') {
                $sectorRecord = BpsStatistic::where('metric', $metric)
                    ->where('tahun', $activeYear)
                    ->where('periode', $activePeriode)
                    ->where('kode_wilayah', $provinsi)
                    ->first();
                if ($sectorRecord) {
                    $activeSectorValue = (float)$sectorRecord->value;
                }
            }

            // Fetch province codes dynamically from 'kemiskinan'
            $provCodes = BpsStatistic::where('metric', 'kemiskinan')
                ->where('kode_wilayah', '!=', '9999')
                ->where('kode_wilayah', '!=', '0000')
                ->select('kode_wilayah')
                ->distinct()
                ->pluck('kode_wilayah');
            
            foreach ($provCodes as $pCode) {
                $mapData[$pCode] = $activeSectorValue;
            }
        }

        // 6. Data Table Rows
        $tableQuery = BpsStatistic::where('metric', $metric);
        if ($tahun !== 'all') $tableQuery->where('tahun', $tahun);
        if ($periode !== 'all') $tableQuery->where('periode', $periode);
        if ($provinsi !== 'all' && $isProvincial) $tableQuery->where('kode_wilayah', $provinsi);

        $tableData = $tableQuery->orderBy('tahun', 'desc')
            ->orderBy('nama_wilayah')
            ->get()
            ->map(fn($item) => [
                'nama' => $item->nama_wilayah,
                'kode' => $item->kode_wilayah,
                'tahun' => $item->tahun,
                'periode' => $item->periode,
                'value' => round((float)$item->value, $metric === 'gini_ratio' ? 3 : 2),
                'sub_kategori' => $item->sub_kategori,
            ]);

        return response()->json([
            'summary' => $summary,
            'lineChart' => $lineChart,
            'barChart' => $barChart,
            'mapData' => $mapData,
            'tableData' => $tableData,
        ]);
    }

    /**
     * Get autocomplete search suggestions for the homepage.
     */
    public function getSuggestions(Request $request)
    {
        $query = $request->input('q', '');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = [];

        // 1. Check matching metrics
        $metricsConfig = [
            'tpt' => 'Tingkat Pengangguran Terbuka (TPT)',
            'gini_ratio' => 'Gini Ratio',
            'kemiskinan' => 'Persentase Penduduk Miskin (P0)',
            'inflasi_tahunan' => 'Inflasi Tahunan (YoY)',
            'inflasi_bulanan' => 'Inflasi Bulanan (MtM)',
            'pdb_growth' => 'Laju Pertumbuhan PDB',
            'ihpb' => 'Indeks Harga Perdagangan Besar (IHPB)',
        ];

        foreach ($metricsConfig as $key => $label) {
            if (stripos($label, $query) !== false || stripos($key, $query) !== false) {
                $suggestions[] = [
                    'label' => $label,
                    'category' => 'Indikator Utama',
                    'url' => route('metric.detail', $key),
                ];
            }
        }

        // 2. Check matching provinces
        $provinces = BpsStatistic::where('kode_wilayah', '!=', '9999')
            ->where('kode_wilayah', '!=', '0000')
            ->where('kode_wilayah', 'LIKE', '%00') // Standard provinces end with 00
            ->where('nama_wilayah', 'LIKE', '%' . $query . '%')
            ->select('kode_wilayah', 'nama_wilayah')
            ->distinct()
            ->take(5)
            ->get();

        foreach ($provinces as $p) {
            $suggestions[] = [
                'label' => $p->nama_wilayah,
                'category' => 'Wilayah (Provinsi)',
                'url' => route('metric.detail', 'tpt') . '?provinsi=' . $p->kode_wilayah,
            ];
        }

        // 3. Check matching sectors/categories for PDB or IHPB
        $sectors = BpsStatistic::whereIn('metric', ['pdb_growth', 'ihpb'])
            ->where('kode_wilayah', '!=', '9999')
            ->where('nama_wilayah', 'LIKE', '%' . $query . '%')
            ->select('metric', 'kode_wilayah', 'nama_wilayah')
            ->distinct()
            ->take(5)
            ->get();

        foreach ($sectors as $s) {
            $suggestions[] = [
                'label' => $s->nama_wilayah,
                'category' => $s->metric === 'pdb_growth' ? 'Sektor PDB' : 'Sektor IHPB',
                'url' => route('metric.detail', $s->metric) . '?provinsi=' . $s->kode_wilayah,
            ];
        }

        return response()->json($suggestions);
    }

    /**
     * Get data comparison between base and target regions/sectors.
     */
    public function compareData($metric, Request $request)
    {
        $tahun = $request->input('tahun', 'all');
        $periode = $request->input('periode', 'all');
        $baseRegion = $request->input('base_region', '9999');
        $compareWith = $request->input('compare_with');

        if (!$compareWith) {
            return response()->json(['error' => 'Comparison region/sector code is required.'], 400);
        }

        // Get latest year and period if 'all' is passed
        $maxYear = BpsStatistic::where('metric', $metric)->max('tahun');
        $activeYear = ($tahun === 'all' || !$tahun) ? $maxYear : $tahun;

        $maxPeriodeQuery = BpsStatistic::where('metric', $metric)->where('tahun', $activeYear);
        $latestPeriodRecord = $maxPeriodeQuery->orderByRaw("CASE 
                    WHEN periode = 'Tahunan' THEN 13 
                    WHEN periode = 'Desember' THEN 12
                    WHEN periode = 'November' THEN 11
                    WHEN periode = 'Oktober' THEN 10
                    WHEN periode = 'September' THEN 9
                    WHEN periode = 'Agustus' THEN 8
                    WHEN periode = 'Juli' THEN 7
                    WHEN periode = 'Juni' THEN 6
                    WHEN periode = 'Mei' THEN 5
                    WHEN periode = 'April' THEN 4
                    WHEN periode = 'Maret' THEN 3
                    WHEN periode = 'Februari' THEN 2
                    WHEN periode = 'Januari' THEN 1
                    ELSE 0 END DESC")
                ->first();
        $activePeriode = ($periode === 'all' || !$periode) 
            ? ($latestPeriodRecord ? $latestPeriodRecord->periode : 'all') 
            : $periode;

        // Fetch values for both base and compare regions for the active year/period
        $baseRecord = BpsStatistic::where('metric', $metric)
            ->where('tahun', $activeYear)
            ->where('periode', $activePeriode)
            ->where('kode_wilayah', $baseRegion)
            ->first();

        $compareRecord = BpsStatistic::where('metric', $metric)
            ->where('tahun', $activeYear)
            ->where('periode', $activePeriode)
            ->where('kode_wilayah', $compareWith)
            ->first();

        $baseValue = $baseRecord ? (float)$baseRecord->value : null;
        $compareValue = $compareRecord ? (float)$compareRecord->value : null;
        $delta = ($baseValue !== null && $compareValue !== null) ? round($baseValue - $compareValue, 4) : null;

        // Fetch historical trends for line chart
        $baseTrendQuery = BpsStatistic::selectRaw('tahun, AVG(value) as avg_val')
            ->where('metric', $metric)
            ->where('kode_wilayah', $baseRegion);
        if ($activePeriode !== 'all') {
            $baseTrendQuery->where('periode', $activePeriode);
        }
        $baseTrend = $baseTrendQuery->groupBy('tahun')->orderBy('tahun')->get();

        $compareTrendQuery = BpsStatistic::selectRaw('tahun, AVG(value) as avg_val')
            ->where('metric', $metric)
            ->where('kode_wilayah', $compareWith);
        if ($activePeriode !== 'all') {
            $compareTrendQuery->where('periode', $activePeriode);
        }
        $compareTrend = $compareTrendQuery->groupBy('tahun')->orderBy('tahun')->get();

        // Standardize labels (years)
        $years = $baseTrend->pluck('tahun')->merge($compareTrend->pluck('tahun'))->unique()->sort()->values();

        $baseDataMapped = [];
        foreach ($years as $y) {
            $match = $baseTrend->firstWhere('tahun', $y);
            $baseDataMapped[] = $match ? round((float)$match->avg_val, 3) : null;
        }

        $compareDataMapped = [];
        foreach ($years as $y) {
            $match = $compareTrend->firstWhere('tahun', $y);
            $compareDataMapped[] = $match ? round((float)$match->avg_val, 3) : null;
        }

        $baseName = $baseRecord ? $baseRecord->nama_wilayah : BpsStatistic::where('kode_wilayah', $baseRegion)->value('nama_wilayah') ?? 'Nasional';
        $compareName = $compareRecord ? $compareRecord->nama_wilayah : BpsStatistic::where('kode_wilayah', $compareWith)->value('nama_wilayah') ?? 'Kategori Pembanding';

        return response()->json([
            'activeYear' => $activeYear,
            'activePeriode' => $activePeriode,
            'base' => [
                'code' => $baseRegion,
                'name' => $baseName,
                'value' => $baseValue !== null ? round($baseValue, 3) : '-',
            ],
            'compare' => [
                'code' => $compareWith,
                'name' => $compareName,
                'value' => $compareValue !== null ? round($compareValue, 3) : '-',
            ],
            'delta' => $delta !== null ? round($delta, 3) : '-',
            'lineChart' => [
                'labels' => $years,
                'datasets' => [
                    [
                        'label' => $baseName,
                        'data' => $baseDataMapped,
                        'borderColor' => '#1F3C88',
                        'backgroundColor' => 'rgba(31,60,136,0.05)',
                        'fill' => true,
                        'tension' => 0.3,
                    ],
                    [
                        'label' => $compareName,
                        'data' => $compareDataMapped,
                        'borderColor' => '#ef4444',
                        'backgroundColor' => 'rgba(239,68,68,0.05)',
                        'fill' => true,
                        'tension' => 0.3,
                    ]
                ]
            ]
        ]);
    }
}
