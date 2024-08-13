<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'nuptk', 'nama', 'jenis_kelamin', 'agama', 'phone', 'email', 'id_jurusan', 'image'
    ];

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'guru_kelas', 'guru_id', 'kelas_id');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan');
    }

    public function mapel()
    {
        return $this->belongsToMany(Mapel::class, 'guru_mapels', 'guru_id', 'mapel_id');
    }
}
