<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'file_ids',
        'claimed_at',
        'completed_at',
        'is_completed',
    ];

    protected $casts = [
        'file_ids' => 'array',
        'claimed_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the user that claimed the files.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order that owns the file claim.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the fileItems that are part of this claim
     */
    public function fileItems()
    {
        return FileItem::whereIn('id', $this->file_ids);
    }

    /**
     * Mark this claim as completed
     */
    public function markAsCompleted(): self
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        // Mark all the individual files as completed
        $this->fileItems()->update([
            'status' => 'completed',
            'is_processed' => true,
        ]);

        return $this;
    }

    /**
     * Check if all files in this claim are completed
     */
    public function checkCompletion(): bool
    {
        $completedFiles = $this->fileItems()->where('status', 'completed')->count();
        return $completedFiles === count($this->file_ids);
    }
}
