<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
    ];

    // Un reporte pertenece a un usuario (cliente)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
