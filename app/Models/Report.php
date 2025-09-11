<?php

namespace App\Models;

use App\Domain\Enums\ServiceType; // Importar el Enum
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
        'month',    // Añadido
        'service',  // Añadido
    ];

    // Castear el campo 'service' al Enum de ServiceType
    protected $casts = [
        'service' => ServiceType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}