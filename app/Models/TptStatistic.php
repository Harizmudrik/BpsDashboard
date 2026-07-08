<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TptStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun',
        'periode',
        'kode_wilayah',
        'nama_wilayah',
        'tpt_value',
    ];
}
