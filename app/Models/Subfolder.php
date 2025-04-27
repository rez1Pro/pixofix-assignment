<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subfolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'name',
        'is_open',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];

    /**
     * Get the folder that owns the subfolder.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the order that this subfolder belongs to (through folder).
     */
    public function order()
    {
        return $this->folder->order;
    }

    /**
     * Get the files in this subfolder.
     */
    public function files(): HasMany
    {
        return $this->hasMany(FileItem::class);
    }

    /**
     * Get the count of approved files in this subfolder
     */
    public function approvedFilesCount(): int
    {
        return $this->files()->where('status', 'approved')->count();
    }

    /**
     * Get the count of pending files in this subfolder
     */
    public function pendingFilesCount(): int
    {
        return $this->files()->where('status', 'pending')->count();
    }

    /**
     * Get the count of rejected files in this subfolder
     */
    public function rejectedFilesCount(): int
    {
        return $this->files()->where('status', 'rejected')->count();
    }

    /**
     * Get the count of in-progress files in this subfolder
     */
    public function inProgressFilesCount(): int
    {
        return $this->files()->where('status', 'in_progress')->count();
    }

    /**
     * Get the total count of files in this subfolder
     */
    public function totalFilesCount(): int
    {
        return $this->files()->count();
    }
}