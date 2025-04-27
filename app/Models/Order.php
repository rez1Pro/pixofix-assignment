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
        'deadline',
        'customer_name',
        'completed_at',
        'approved_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'deadline' => 'date',
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
     * Get the folders for this order.
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
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
     * Get the count of approved files
     */
    public function approvedFilesCount(): int
    {
        return $this->fileItems()->where('status', 'approved')->count();
    }

    /**
     * Get the count of rejected files
     */
    public function rejectedFilesCount(): int
    {
        return $this->fileItems()->where('status', 'rejected')->count();
    }

    /**
     * Get the count of in-progress files
     */
    public function inProgressFilesCount(): int
    {
        return $this->fileItems()->where('status', 'in_progress')->count();
    }

    /**
     * Get the total count of files
     */
    public function totalFilesCount(): int
    {
        return $this->fileItems()->count();
    }

    /**
     * Get order statistics
     */
    public function getStats(): array
    {
        return [
            'total_files' => $this->totalFilesCount(),
            'approved_files' => $this->approvedFilesCount(),
            'pending_files' => $this->pendingFilesCount(),
            'rejected_files' => $this->rejectedFilesCount(),
        ];
    }

    /**
     * Check if all files are completed
     */
    public function isCompleted(): bool
    {
        return $this->approvedFilesCount() === $this->totalFilesCount() && $this->totalFilesCount() > 0;
    }

    /**
     * Check if order is approved
     */
    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    /**
     * Mark order as completed
     */
    public function markAsCompleted(): self
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark order as approved
     */
    public function approve(): self
    {
        $this->update([
            'approved_at' => now(),
        ]);

        return $this;
    }
}
