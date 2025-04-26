<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'filename',
        'original_filename',
        'filepath',
        'directory_path',
        'file_type',
        'file_size',
        'is_processed',
        'status',
    ];

    protected $casts = [
        'is_processed' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Get the order that owns the file item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if this file is currently claimed
     */
    public function isClaimed(): bool
    {
        return $this->status === 'claimed' || $this->status === 'processing';
    }

    /**
     * Get the full storage path for the file
     */
    public function getFullPath(): string
    {
        return storage_path('app/' . $this->filepath);
    }

    /**
     * Mark the file as claimed
     */
    public function markAsClaimed(): self
    {
        $this->update(['status' => 'claimed']);
        return $this;
    }

    /**
     * Mark the file as being processed
     */
    public function markAsProcessing(): self
    {
        $this->update(['status' => 'processing']);
        return $this;
    }

    /**
     * Mark the file as completed
     */
    public function markAsCompleted(): self
    {
        $this->update([
            'status' => 'completed',
            'is_processed' => true,
        ]);
        return $this;
    }
}
