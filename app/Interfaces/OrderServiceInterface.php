<?php

namespace App\Interfaces;

use App\Models\Folder;
use App\Models\Order;
use App\Models\Subfolder;
use Spatie\LaravelData\PaginatedDataCollection;

interface OrderServiceInterface
{
    /**
     * Get all orders with pagination
     * 
     * @param int $perPage The number of items per page
     * @param string|null $search Search query for name or order number
     * @param string|null $status Filter by status
     */
    public function getAllOrders(int $perPage = 10, ?string $search = null, ?string $status = null): PaginatedDataCollection;

    /**
     * Get order by ID with related data
     */
    public function getOrderById(int $id): Order;

    /**
     * Create a new order with uploaded files
     */
    public function createOrder(array $data): Order;

    /**
     * Update an existing order
     */
    public function updateOrder(Order $order, array $data): Order;

    /**
     * Delete an order and its associated files
     */
    public function deleteOrder(Order $order): bool;

    /**
     * Mark an order as completed
     */
    public function markOrderAsCompleted(Order $order): Order;

    /**
     * Approve a completed order
     */
    public function approveOrder(Order $order): Order;

    /**
     * Process uploaded files for an order
     */
    public function processOrderFiles(Order $order, array $files): void;

    /**
     * Get order statistics
     */
    public function getOrderStats(Order $order): array;

    /**
     * Create a new folder in the order
     */
    public function createFolder(Order $order, string $name): Folder;

    /**
     * Create a new subfolder in a folder
     */
    public function createSubfolder(Folder $folder, string $name): Subfolder;

    /**
     * Upload files to a folder
     */
    public function uploadFilesToFolder(Folder $folder, array $files): array;

    /**
     * Upload files to a subfolder
     */
    public function uploadFilesToSubfolder(Subfolder $subfolder, array $files): array;

    /**
     * Get the order's folder structure with files
     */
    public function getOrderFolderStructure(Order $order): array;
}