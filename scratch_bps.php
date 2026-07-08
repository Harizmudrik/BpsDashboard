<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BpsStatistic;

echo "--- IHPB RANGE ---\n";
$ihpb_stats = BpsStatistic::where('metric', 'ihpb')
    ->selectRaw('MIN(value) as min_val, MAX(value) as max_val, AVG(value) as avg_val')
    ->first();
echo "  Min: {$ihpb_stats->min_val}, Max: {$ihpb_stats->max_val}, Avg: {$ihpb_stats->avg_val}\n";

echo "--- PDB RANGE ---\n";
$pdb_stats = BpsStatistic::where('metric', 'pdb_growth')
    ->selectRaw('MIN(value) as min_val, MAX(value) as max_val, AVG(value) as avg_val')
    ->first();
echo "  Min: {$pdb_stats->min_val}, Max: {$pdb_stats->max_val}, Avg: {$pdb_stats->avg_val}\n";


