<?php

namespace App\Console\Commands;

use App\Models\TptStatistic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncBpsTptData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bps:sync-tpt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch TPT data from BPS WebAPI and save to database';

    // BPS tahun IDs: th=122 -> 2022, th=123 -> 2023, th=124 -> 2024, th=125 -> 2025
    private array $tahunMap = [
        121 => '2021',
        122 => '2022',
        123 => '2023',
        124 => '2024',
        125 => '2025',
    ];

    // BPS turtahun (periode)
    private array $periodeMap = [
        189 => 'Februari',
        190 => 'Agustus',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $apiKey = config('services.bps.api_key', env('BPS_API_KEY'));

        if (empty($apiKey) || $apiKey === 'YOUR_BPS_API_KEY_HERE') {
            $this->error('BPS API Key belum diatur di .env!');
            return 1;
        }

        $this->info('🔄 Memulai sinkronisasi data TPT dari BPS WebAPI...');
        $this->newLine();

        $totalInserted = 0;
        $totalUpdated = 0;

        foreach ($this->tahunMap as $thId => $tahunLabel) {
            $this->info("📅 Mengambil data tahun {$tahunLabel} (th={$thId})...");

            $url = "https://webapi.bps.go.id/v1/api/list/model/data/lang/ind/domain/0000/var/543/th/{$thId}/key/{$apiKey}";

            try {
                $response = Http::timeout(30)->get($url);

                if (!$response->successful()) {
                    $this->warn("  ⚠ HTTP {$response->status()} untuk tahun {$tahunLabel}. Dilewati.");
                    continue;
                }

                $data = $response->json();

                if (!isset($data['datacontent']) || empty($data['datacontent'])) {
                    $this->warn("  ⚠ Tidak ada datacontent untuk tahun {$tahunLabel}. Dilewati.");
                    continue;
                }

                // Build province lookup from vervar
                $provinsiLookup = [];
                if (isset($data['vervar'])) {
                    foreach ($data['vervar'] as $prov) {
                        $provinsiLookup[(string)$prov['val']] = $prov['label'];
                    }
                }

                // Parse datacontent
                // Key format: {kode_wilayah}0{var_id}0{th_id}{turtahun_id}
                // Example: 11005430125190 -> kode_wilayah=1100, var=543, th=125, turtahun=190
                foreach ($data['datacontent'] as $key => $value) {
                    $parsed = $this->parseDataKey($key, $thId);

                    if (!$parsed) continue;

                    // Skip 'Tahunan' (191) and Indonesia aggregate (9999)
                    if ($parsed['turtahun'] == 191) continue;
                    if ($parsed['kode_wilayah'] == '9999') continue;

                    $periodeLabel = $this->periodeMap[$parsed['turtahun']] ?? null;
                    if (!$periodeLabel) continue;

                    $namaWilayah = $provinsiLookup[$parsed['kode_wilayah']] ?? 'UNKNOWN';

                    $record = TptStatistic::updateOrCreate(
                        [
                            'tahun' => $tahunLabel,
                            'periode' => $periodeLabel,
                            'kode_wilayah' => $parsed['kode_wilayah'],
                        ],
                        [
                            'nama_wilayah' => $namaWilayah,
                            'tpt_value' => $value,
                        ]
                    );

                    if ($record->wasRecentlyCreated) {
                        $totalInserted++;
                    } else {
                        $totalUpdated++;
                    }
                }

                $this->info("  ✅ Data tahun {$tahunLabel} berhasil diproses.");

            } catch (\Exception $e) {
                $this->error("  ❌ Error tahun {$tahunLabel}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("🎉 Sinkronisasi selesai!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Data baru', $totalInserted],
                ['Data diperbarui', $totalUpdated],
                ['Total di database', TptStatistic::count()],
            ]
        );

        return 0;
    }

    /**
     * Parse a BPS datacontent key.
     * Key format: {kode_wilayah}{var_id}0{th_id}{turtahun_id}
     * Example: 11005430125190
     *   kode_wilayah = 1100
     *   var = 543
     *   tahun_id = 125
     *   turtahun_id = 190
     */
    private function parseDataKey(string $key, int $expectedTh): ?array
    {
        // Key length is typically 14 chars
        // Pattern: WWWW VVV 0 TTT PPP (where spaces are just for readability)
        // But var 543 -> 3 chars, so:
        //   kode_wilayah = first 4 chars
        //   var_id = next 3 chars
        //   separator 0 = next 1 char
        //   th_id = next 3 chars
        //   turtahun_id = last 3 chars
        if (strlen($key) < 14) return null;

        $kodeWilayah = substr($key, 0, 4);
        $turtahunId = (int)substr($key, -3);
        $thId = (int)substr($key, -6, 3);

        return [
            'kode_wilayah' => $kodeWilayah,
            'turtahun' => $turtahunId,
        ];
    }
}
