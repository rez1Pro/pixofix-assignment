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
        'folder_id',
        'subfolder_id',
        'name',
        'original_name',
        'path',
        'file_type',
        'file_size',
        'status',
        'assigned_to',
        'created_at',
    ];

    protected $casts = [
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
     * Get the folder that contains this file.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the subfolder that contains this file.
     */
    public function subfolder(): BelongsTo
    {
        return $this->belongsTo(Subfolder::class);
    }

    /**
     * Get the user that this file is assigned to.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Check if this file is currently in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if this file is currently approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if this file is currently rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if this file is currently pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the full storage path for the file
     */
    public function getFullPath(): string
    {
        return storage_path('app/' . $this->path);
    }

    /**
     * Mark the file as assigned to a user
     */
    public function assignTo(User $user): self
    {
        $this->update([
            'status' => 'in_progress',
            'assigned_to' => $user->id,
        ]);
        return $this;
    }

    /**
     * Mark the file as approved
     */
    public function markAsApproved(): self
    {
        $this->update(['status' => 'approved']);
        return $this;
    }

    /**
     * Mark the file as rejected
     */
    public function markAsRejected(): self
    {
        $this->update(['status' => 'rejected']);
        return $this;
    }

    /**
     * Mark the file as pending
     */
    public function markAsPending(): self
    {
        $this->update(['status' => 'pending']);
        return $this;
    }
}
