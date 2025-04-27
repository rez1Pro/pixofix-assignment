<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'name',
        'is_open',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];

    /**
     * Get the order that owns the folder.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the subfolders in this folder.
     */
    public function subfolders(): HasMany
    {
        return $this->hasMany(Subfolder::class);
    }

    /**
     * Get the files in this folder (excluding those in subfolders).
     */
    public function files(): HasMany
    {
        return $this->hasMany(FileItem::class);
    }

    /**
     * Get all files in this folder including those in subfolders.
     */
    public function allFiles()
    {
        $folderFiles = $this->files;
        $subfolderFiles = $this->subfolders->flatMap(function ($subfolder) {
            return $subfolder->files;
        });

        return $folderFiles->concat($subfolderFiles);
    }

    /**
     * Get the count of approved files in this folder including subfolders
     */
    public function approvedFilesCount(): int
    {
        $folderCount = $this->files()->where('status', 'approved')->count();
        $subfolderCount = $this->subfolders->sum(function ($subfolder) {
            return $subfolder->files()->where('status', 'approved')->count();
        });

        return $folderCount + $subfolderCount;
    }

    /**
     * Get the count of pending files in this folder including subfolders
     */
    public function pendingFilesCount(): int
    {
        $folderCount = $this->files()->where('status', 'pending')->count();
        $subfolderCount = $this->subfolders->sum(function ($subfolder) {
            return $subfolder->files()->where('status', 'pending')->count();
        });

        return $folderCount + $subfolderCount;
    }

    /**
     * Get the count of rejected files in this folder including subfolders
     */
    public function rejectedFilesCount(): int
    {
        $folderCount = $this->files()->where('status', 'rejected')->count();
        $subfolderCount = $this->subfolders->sum(function ($subfolder) {
            return $subfolder->files()->where('status', 'rejected')->count();
        });

        return $folderCount + $subfolderCount;
    }

    /**
     * Get the count of in-progress files in this folder including subfolders
     */
    public function inProgressFilesCount(): int
    {
        $folderCount = $this->files()->where('status', 'in_progress')->count();
        $subfolderCount = $this->subfolders->sum(function ($subfolder) {
            return $subfolder->files()->where('status', 'in_progress')->count();
        });

        return $folderCount + $subfolderCount;
    }

    /**
     * Get the total count of files in this folder including subfolders
     */
    public function totalFilesCount(): int
    {
        $folderCount = $this->files()->count();
        $subfolderCount = $this->subfolders->sum(function ($subfolder) {
            return $subfolder->files()->count();
        });

        return $folderCount + $subfolderCount;
    }
}