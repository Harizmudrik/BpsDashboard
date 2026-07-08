<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BpsStatistic extends Model
{
    use HasFactory;

    protected $table = 'bps_statistics';

    protected $fillable = [
        'metric',
        'tahun',
        'periode',
        'kode_wilayah',
        'nama_wilayah',
        'value',
        'sub_kategori',
    ];
}
