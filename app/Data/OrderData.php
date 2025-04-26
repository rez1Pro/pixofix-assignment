<?php

namespace App\Data;

use App\Models\Order;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\DataAttributesCollection;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class OrderData extends Data
{
    public function __construct(
        public int $id,
        public string $order_number,
        public string $name,
        public ?string $description,
        public int $created_by,
        public string $status,
        public ?string $completed_at,
        public ?string $approved_at,
        public string $created_at,
        public string $updated_at,
        public UserData $creator,
        #[DataCollectionOf(FileItemData::class)]
        public ?DataCollection $fileItems = null,
        public ?array $stats = null,
    ) {
        //
    }

    public static function fromModel(Order $order): self
    {
        $stats = null;
        if ($order->relationLoaded('fileItems')) {
            $stats = [
                'total' => $order->totalFilesCount(),
                'pending' => $order->pendingFilesCount(),
                'claimed' => $order->claimedFilesCount(),
                'completed' => $order->completedFilesCount(),
            ];
        }

        return new self(
            id: $order->id,
            order_number: $order->order_number,
            name: $order->name,
            description: $order->description,
            created_by: $order->created_by,
            status: $order->status,
            completed_at: $order->completed_at?->format('Y-m-d H:i:s'),
            approved_at: $order->approved_at?->format('Y-m-d H:i:s'),
            created_at: $order->created_at->format('Y-m-d H:i:s'),
            updated_at: $order->updated_at->format('Y-m-d H:i:s'),
            creator: UserData::from($order->creator),
            fileItems: $order->relationLoaded('fileItems')
            ? FileItemData::collect($order->fileItems, DataCollection::class)
            : null,
            stats: $stats,
        );
    }
}