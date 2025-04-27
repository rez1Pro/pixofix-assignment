<?php

namespace App\Services;

use App\Data\OrderData;
use App\Interfaces\FileItemServiceInterface;
use App\Interfaces\OrderServiceInterface;
use App\Models\FileItem;
use App\Models\Folder;
use App\Models\Order;
use App\Models\Subfolder;
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
                'fileItems as approved_files_count' => function ($query) {
                    $query->where('status', 'approved');
                },
                'fileItems as pending_files_count' => function ($query) {
                    $query->where('status', 'pending');
                }
            ])
            ->orderBy('created_at', 'desc');

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
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
        return Order::with([
            'creator.role',
            'folders.files',
            'folders.subfolders.files'
        ])->findOrFail($id);
    }

    /**
     * Create a new order with uploaded files
     */
    public function createOrder(array $data): Order
    {
        // Generate order number
        $orderNumber = 'ORD-' . date('Y-m') . '-' . str_pad(Order::count() + 1, 3, '0', STR_PAD_LEFT);

        // Create the order
        $order = Order::create([
            'order_number' => $orderNumber,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'customer_name' => $data['customer_name'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'created_by' => Auth::id(),
            'status' => 'pending',
        ]);

        // Create default folders
        $this->createFolder($order, 'Original Images');
        $this->createFolder($order, 'Edited Images');

        // Process the uploaded files if present
        if (isset($data['files']) && !empty($data['files'])) {
            $originalFolder = $order->folders()->where('name', 'Original Images')->first();
            if ($originalFolder) {
                $this->uploadFilesToFolder($originalFolder, $data['files']);
            }
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
            'description' => $data['description'] ?? $order->description,
            'customer_name' => $data['customer_name'] ?? $order->customer_name,
            'deadline' => $data['deadline'] ?? $order->deadline,
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
        $order->markAsCompleted();
        return $order;
    }

    /**
     * Approve a completed order
     */
    public function approveOrder(Order $order): Order
    {
        $order->approve();
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
        return $order->getStats();
    }

    /**
     * Create a new folder in the order
     */
    public function createFolder(Order $order, string $name): Folder
    {
        return $order->folders()->create([
            'name' => $name,
            'is_open' => true,
        ]);
    }

    /**
     * Create a new subfolder in a folder
     */
    public function createSubfolder(Folder $folder, string $name): Subfolder
    {
        return $folder->subfolders()->create([
            'name' => $name,
            'is_open' => true,
        ]);
    }

    /**
     * Upload files to a folder
     */
    public function uploadFilesToFolder(Folder $folder, array $files): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $path = $file->storeAs(
                'orders/' . $folder->order_id . '/' . $folder->id,
                Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension()
            );

            $fileItem = $folder->files()->create([
                'order_id' => $folder->order_id,
                'name' => $originalName,
                'original_name' => $originalName,
                'path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'status' => 'pending',
            ]);

            $uploadedFiles[] = $fileItem;
        }

        return $uploadedFiles;
    }

    /**
     * Upload files to a subfolder
     */
    public function uploadFilesToSubfolder(Subfolder $subfolder, array $files): array
    {
        $uploadedFiles = [];
        $folder = $subfolder->folder;

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $path = $file->storeAs(
                'orders/' . $folder->order_id . '/' . $folder->id . '/' . $subfolder->id,
                Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension()
            );

            $fileItem = $subfolder->files()->create([
                'order_id' => $folder->order_id,
                'folder_id' => $folder->id,
                'name' => $originalName,
                'original_name' => $originalName,
                'path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'status' => 'pending',
            ]);

            $uploadedFiles[] = $fileItem;
        }

        return $uploadedFiles;
    }

    /**
     * Get the order's folder structure with files
     */
    public function getOrderFolderStructure(Order $order): array
    {
        // Load the order with all folders, subfolders, and files
        $order->load([
            'folders.files.assignedTo',
            'folders.subfolders.files.assignedTo'
        ]);

        $formattedFolders = [];
        foreach ($order->folders as $folder) {
            $formattedSubfolders = [];
            foreach ($folder->subfolders as $subfolder) {
                $formattedSubfolders[$subfolder->name] = [
                    'isOpen' => $subfolder->is_open,
                    'files' => $subfolder->files->map(function ($file) {
                        return [
                            'id' => $file->id,
                            'name' => $file->name,
                            'path' => $file->path,
                            'status' => $file->status,
                            'created_at' => $file->created_at->toDateString(),
                            'assignedTo' => $file->assignedTo ? [
                                'id' => $file->assignedTo->id,
                                'name' => $file->assignedTo->name,
                                'avatar' => $file->assignedTo->profile_photo_url ?? null,
                            ] : null,
                        ];
                    })->toArray(),
                ];
            }

            $formattedFolders[$folder->name] = [
                'isOpen' => $folder->is_open,
                'files' => $folder->files->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->name,
                        'path' => $file->path,
                        'status' => $file->status,
                        'created_at' => $file->created_at->toDateString(),
                        'assignedTo' => $file->assignedTo ? [
                            'id' => $file->assignedTo->id,
                            'name' => $file->assignedTo->name,
                            'avatar' => $file->assignedTo->profile_photo_url ?? null,
                        ] : null,
                    ];
                })->toArray(),
                'subfolders' => $formattedSubfolders,
            ];
        }

        return $formattedFolders;
    }
}