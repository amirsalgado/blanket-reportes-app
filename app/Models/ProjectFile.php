<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFile extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'folder_id', 'file_name', 'file_path'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function folder(): BelongsTo { return $this->belongsTo(Folder::class); }
}