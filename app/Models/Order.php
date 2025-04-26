<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'name',
        'description',
        'created_by',
        'status',
        'completed_at',
        'approved_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the creator of the order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the file items for this order.
     */
    public function fileItems(): HasMany
    {
        return $this->hasMany(FileItem::class);
    }

    /**
     * Get the file claims for this order.
     */
    public function fileClaims(): HasMany
    {
        return $this->hasMany(FileClaim::class);
    }

    /**
     * Get the count of pending files
     */
    public function pendingFilesCount(): int
    {
        return $this->fileItems()->where('status', 'pending')->count();
    }

    /**
     * Get the count of completed files
     */
    public function completedFilesCount(): int
    {
        return $this->fileItems()->where('status', 'completed')->count();
    }

    /**
     * Get the count of claimed files
     */
    public function claimedFilesCount(): int
    {
        return $this->fileItems()->whereIn('status', ['claimed', 'processing'])->count();
    }

    /**
     * Get the total count of files
     */
    public function totalFilesCount(): int
    {
        return $this->fileItems()->count();
    }

    /**
     * Check if all files are completed
     */
    public function isCompleted(): bool
    {
        return $this->completedFilesCount() === $this->totalFilesCount() && $this->totalFilesCount() > 0;
    }
}
