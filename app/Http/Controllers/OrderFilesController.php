<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Interfaces\FileItemServiceInterface;
use App\Interfaces\OrderServiceInterface;
use App\Models\FileItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class OrderFilesController extends Controller
{
    protected OrderServiceInterface $orderService;
    protected FileItemServiceInterface $fileItemService;

    public function __construct(
        OrderServiceInterface $orderService,
        FileItemServiceInterface $fileItemService
    ) {
        $this->orderService = $orderService;
        $this->fileItemService = $fileItemService;
    }

    /**
     * Update status for multiple files at once.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'required|exists:file_items,id',
            'status' => 'required|in:pending,claimed,processing,completed',
        ]);

        $fileIds = $request->input('file_ids');
        $newStatus = $request->input('status');
        $user = Auth::user();
        $updatedCount = 0;

        foreach ($fileIds as $fileId) {
            try {
                $file = $this->fileItemService->getFileItemById($fileId);

                // Make sure the file belongs to this order
                if ($file->order_id !== $order->id) {
                    continue;
                }

                // If status is processing or claimed, assign to the current user
                if ($newStatus === 'processing' || $newStatus === 'claimed') {
                    $this->fileItemService->assignFileToUser($file, $user);
                }

                $this->fileItemService->updateFileStatus($file, $newStatus);
                $updatedCount++;
            } catch (\Exception $e) {
                Log::error('Error updating file status', [
                    'file_id' => $fileId,
                    'status' => $newStatus,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->back()->with('success', "{$updatedCount} files updated to status '{$newStatus}'.");
    }

    /**
     * Assign multiple files to a user.
     */
    public function assignToUser(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'required|exists:file_items,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $fileIds = $request->input('file_ids');
        $userId = $request->input('user_id');
        $user = User::findOrFail($userId);

        // Filter file IDs to only include those belonging to this order
        $validFileIds = FileItem::whereIn('id', $fileIds)
            ->where('order_id', $order->id)
            ->pluck('id')
            ->toArray();

        $assignedCount = $this->fileItemService->assignMultipleFilesToUser($validFileIds, $user);

        return redirect()->back()->with('success', "{$assignedCount} files assigned to {$user->name}.");
    }

    /**
     * Assign multiple files to the current user.
     */
    public function assignToSelf(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'required|exists:file_items,id',
        ]);

        $fileIds = $request->input('file_ids');
        $user = Auth::user();

        // Filter file IDs to only include those belonging to this order
        $validFileIds = FileItem::whereIn('id', $fileIds)
            ->where('order_id', $order->id)
            ->pluck('id')
            ->toArray();

        $assignedCount = $this->fileItemService->assignMultipleFilesToUser($validFileIds, $user);

        return redirect()->back()->with('success', "{$assignedCount} files assigned to you.");
    }

    /**
     * Download multiple files as a zip.
     */
    public function downloadFiles(Request $request, Order $order)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'required|exists:file_items,id',
        ]);

        $fileIds = $request->input('file_ids');

        // Filter file IDs to only include those belonging to this order
        $validFileIds = FileItem::whereIn('id', $fileIds)
            ->where('order_id', $order->id)
            ->pluck('id')
            ->toArray();

        if (empty($validFileIds)) {
            return redirect()->back()->with('error', 'No files selected for download');
        }

        try {
            $zipPath = $this->fileItemService->createZipArchiveFromFiles($order, $validFileIds);
            $zipName = basename($zipPath);

            return Response::download($zipPath, $zipName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error creating download zip', [
                'order_id' => $order->id,
                'file_ids' => $validFileIds,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to create download: ' . $e->getMessage());
        }
    }
}