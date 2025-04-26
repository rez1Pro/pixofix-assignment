<?php

namespace App\Services;

use App\Data\OrderData;
use App\Interfaces\FileItemServiceInterface;
use App\Interfaces\OrderServiceInterface;
use App\Models\FileItem;
use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelData\PaginatedDataCollection;

class OrderService implements OrderServiceInterface
{
    protected FileItemServiceInterface $fileItemService;

    public function __construct(FileItemServiceInterface $fileItemService)
    {
        $this->fileItemService = $fileItemService;
    }

    /**
     * Get all orders with pagination
     * 
     * @param int $perPage The number of items per page
     * @param string|null $search Search query for name or order number
     * @param string|null $status Filter by status
     */
    public function getAllOrders(int $perPage = 10, ?string $search = null, ?string $status = null): PaginatedDataCollection
    {
        $query = Order::with('creator.role')
            ->withCount([
                'fileItems as total_files_count',
                'fileItems as completed_files_count' => function ($query) {
                    $query->where('status', 'completed');
                }
            ])
            ->orderBy('created_at', 'desc');

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('order_number', 'like', "%{$search}%");
            });
        }

        // Apply status filter if provided
        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->paginate($perPage);

        return OrderData::collect(
            $orders,
            PaginatedDataCollection::class
        );
    }

    /**
     * Get order by ID with related data
     */
    public function getOrderById(int $id): Order
    {
        return Order::with(['creator.role', 'fileItems'])->findOrFail($id);
    }

    /**
     * Create a new order with uploaded files
     */
    public function createOrder(array $data): Order
    {
        // Generate order number
        $orderNumber = 'ORD-' . strtoupper(Str::random(8));

        // Create the order
        $order = Order::create([
            'order_number' => $orderNumber,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by' => Auth::id(),
            'status' => 'pending',
        ]);

        // Process the uploaded files if present
        if (isset($data['files']) && !empty($data['files'])) {
            $this->processOrderFiles($order, $data['files']);
        }

        return $order;
    }

    /**
     * Update an existing order
     */
    public function updateOrder(Order $order, array $data): Order
    {
        $order->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return $order;
    }

    /**
     * Delete an order and its associated files
     */
    public function deleteOrder(Order $order): bool
    {
        // Delete all files from storage
        Storage::deleteDirectory("orders/{$order->id}");

        // Delete the order (this will cascade delete file items due to foreign key constraint)
        return $order->delete();
    }

    /**
     * Mark an order as completed
     */
    public function markOrderAsCompleted(Order $order): Order
    {
        if ($order->isCompleted()) {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        return $order;
    }

    /**
     * Approve a completed order
     */
    public function approveOrder(Order $order): Order
    {
        if ($order->status === 'completed') {
            $order->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
        }

        return $order;
    }

    /**
     * Process uploaded files for an order
     */
    public function processOrderFiles(Order $order, array $files): void
    {
        $this->fileItemService->processFiles($order, $files);
    }

    /**
     * Get order statistics
     */
    public function getOrderStats(Order $order): array
    {
        return [
            'total' => $order->totalFilesCount(),
            'pending' => $order->pendingFilesCount(),
            'claimed' => $order->claimedFilesCount(),
            'completed' => $order->completedFilesCount(),
        ];
    }
}