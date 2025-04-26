<?php

namespace App\Data;

use App\Models\FileItem;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class FileItemData extends Data
{
    public function __construct(
        public int $id,
        public int $order_id,
        public string $filename,
        public string $original_filename,
        public string $filepath,
        public ?string $directory_path,
        public string $file_type,
        public int $file_size,
        public string $status,
        public bool $is_processed,
        public string $created_at,
        public string $updated_at,
    ) {
        //
    }

    public static function fromModel(FileItem $fileItem): self
    {
        return new self(
            id: $fileItem->id,
            order_id: $fileItem->order_id,
            filename: $fileItem->filename,
            original_filename: $fileItem->original_filename,
            filepath: $fileItem->filepath,
            directory_path: $fileItem->directory_path,
            file_type: $fileItem->file_type,
            file_size: $fileItem->file_size,
            status: $fileItem->status,
            is_processed: $fileItem->is_processed,
            created_at: $fileItem->created_at->format('Y-m-d H:i:s'),
            updated_at: $fileItem->updated_at->format('Y-m-d H:i:s'),
        );
    }
}