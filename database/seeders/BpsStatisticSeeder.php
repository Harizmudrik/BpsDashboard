<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BpsStatistic;
use Illuminate\Support\Facades\DB;

class BpsStatisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable query logs to save memory during bulk inserts
        DB::connection()->disableQueryLog();

        // Truncate existing statistics
        BpsStatistic::truncate();

        $dataDir = database_path('seeders/data');

        $metrics = [
            'tpt' => 'tpt.json',
            'gini_ratio' => 'gini_ratio.json',
            'kemiskinan' => 'kemiskinan.json',
            'inflasi_tahunan' => 'inflasi_tahunan.json',
            'inflasi_bulanan' => 'inflasi_bulanan.json',
            'pdb_growth' => 'pdb_growth.json',
            'ihpb' => 'ihpb.json',
        ];

        // Load master wilayah map from tpt.json to resolve region names for files without vervar
        $tptPath = $dataDir . '/tpt.json';
        $wilayahMap = ['9999' => 'Indonesia', '0000' => 'Indonesia'];
        if (file_exists($tptPath)) {
            $tptJson = json_decode(file_get_contents($tptPath));
            if (isset($tptJson->vervar) && is_array($tptJson->vervar)) {
                foreach ($tptJson->vervar as $vv) {
                    $wilayahMap[$vv->val] = strip_tags($vv->label);
                }
            }
        }

        foreach ($metrics as $key => $filename) {
            $path = $dataDir . '/' . $filename;
            if (!file_exists($path)) {
                $this->command->error("File not found: {$path}");
                continue;
            }

            $this->command->info("Seeding metric: {$key} from {$filename}...");
            $this->seedMetric($key, $path, $wilayahMap);
        }
    }

    private function seedMetric(string $metricKey, string $jsonPath, array $wilayahMap): void
    {
        $jsonContent = file_get_contents($jsonPath);
        $json = json_decode($jsonContent);

        if (!$json || !isset($json->datacontent)) {
            $this->command->error("Invalid JSON content or missing datacontent for {$metricKey}");
            return;
        }

        // 1. Fallback for vervar (vertical variable, like provinces, cities, or sectors)
        $vervars = [];
        if (isset($json->vervar) && is_array($json->vervar)) {
            $vervars = $json->vervar;
        } elseif ($metricKey === 'ihpb') {
            $vervars = [
                (object)['val' => 1, 'label' => 'Sektor Pertanian'],
                (object)['val' => 2, 'label' => 'Sektor Pertambangan dan Penggalian'],
                (object)['val' => 3, 'label' => 'Sektor Industri Pengolahan'],
                (object)['val' => 4, 'label' => 'Sektor Konstruksi'],
                (object)['val' => 5, 'label' => 'Kelompok Barang Impor'],
                (object)['val' => 6, 'label' => 'Kelompok Barang Ekspor'],
            ];
        } elseif ($metricKey === 'inflasi_tahunan') {
            foreach ($wilayahMap as $code => $name) {
                $vervars[] = (object)['val' => $code, 'label' => $name];
            }
        }

        // 2. Fallback for turvar (subcategories or derived variables)
        $turvars = [];
        if (isset($json->turvar) && is_array($json->turvar)) {
            $turvars = $json->turvar;
        } elseif ($metricKey === 'inflasi_tahunan') {
            $turvars = [
                (object)['val' => 30, 'label' => 'Inflasi Y-on-Y']
            ];
        } else {
            $turvars = [
                (object)['val' => 0, 'label' => 'Tidak ada']
            ];
        }

        // 3. Fallback for var ID
        $varVal = 0;
        if (isset($json->var) && is_array($json->var) && isset($json->var[0]->val)) {
            $varVal = $json->var[0]->val;
        } elseif ($metricKey === 'ihpb') {
            $varVal = 2498;
        } elseif ($metricKey === 'inflasi_tahunan') {
            $varVal = 226;
        }

        // 4. Map years
        $tahunMap = [];
        if (isset($json->tahun) && is_array($json->tahun)) {
            foreach ($json->tahun as $th) {
                $tahunMap[$th->val] = $th->label;
            }
        }

        // 5. Map turtahun (periods or months)
        $turtahunMap = [];
        if (isset($json->turtahun) && is_array($json->turtahun)) {
            foreach ($json->turtahun as $tth) {
                $turtahunMap[$tth->val] = $tth->label;
            }
        }

        $datacontent = (array) $json->datacontent;
        $records = [];
        $insertedCount = 0;

        foreach ($vervars as $vv) {
            foreach ($turvars as $tv) {
                foreach ($json->tahun as $th) {
                    foreach ($json->turtahun as $tth) {
                        // Key format: {vervar}{var}{turvar}{tahun}{turtahun}
                        $key = "{$vv->val}{$varVal}{$tv->val}{$th->val}{$tth->val}";

                        if (isset($datacontent[$key])) {
                            $value = $datacontent[$key];

                            // Skip empty/null/dash values
                            if ($value === '-' || $value === '' || $value === null) {
                                continue;
                            }

                            // Clean string name (remove HTML bold tags e.g. in PDB)
                            $namaWilayah = strip_tags($vv->label);
                            $namaWilayah = trim($namaWilayah);

                            // Determine subcategory
                            $subKategori = ($tv->val == 0 || $tv->label === 'Tidak ada') ? null : $tv->label;

                            $records[] = [
                                'metric' => $metricKey,
                                'tahun' => (string)$tahunMap[$th->val],
                                'periode' => (string)$turtahunMap[$tth->val],
                                'kode_wilayah' => (string)$vv->val,
                                'nama_wilayah' => $namaWilayah,
                                'value' => (float)$value,
                                'sub_kategori' => $subKategori,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];

                            $insertedCount++;

                            // Insert in chunks of 500
                            if (count($records) >= 500) {
                                BpsStatistic::insert($records);
                                $records = [];
                            }
                        }
                    }
                }
            }
        }

        if (count($records) > 0) {
            BpsStatistic::insert($records);
        }

        $this->command->info("Successfully seeded {$insertedCount} records for {$metricKey}.");
    }
}
