<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Interfaces\OrderServiceInterface;
use App\Models\FileItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class OrderFilesController extends Controller
{
    protected OrderServiceInterface $orderService;

    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Update status for multiple files at once.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'required|exists:file_items,id',
            'status' => 'required|in:pending,in_progress,approved,rejected',
        ]);

        $fileIds = $request->input('file_ids');
        $newStatus = $request->input('status');
        $updatedCount = 0;

        foreach ($fileIds as $fileId) {
            $file = FileItem::findOrFail($fileId);

            // Make sure the file belongs to this order
            if ($file->order_id !== $order->id) {
                continue;
            }

            switch ($newStatus) {
                case 'pending':
                    $file->markAsPending();
                    break;
                case 'in_progress':
                    $file->assignTo(Auth::user());
                    break;
                case 'approved':
                    $file->markAsApproved();
                    break;
                case 'rejected':
                    $file->markAsRejected();
                    break;
            }

            $updatedCount++;
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
        $assignedCount = 0;

        foreach ($fileIds as $fileId) {
            $file = FileItem::findOrFail($fileId);

            // Make sure the file belongs to this order
            if ($file->order_id !== $order->id) {
                continue;
            }

            $file->update([
                'status' => 'in_progress',
                'assigned_to' => $userId,
            ]);

            $assignedCount++;
        }

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
        $assignedCount = 0;

        foreach ($fileIds as $fileId) {
            $file = FileItem::findOrFail($fileId);

            // Make sure the file belongs to this order
            if ($file->order_id !== $order->id) {
                continue;
            }

            $file->assignTo($user);
            $assignedCount++;
        }

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
        $files = FileItem::whereIn('id', $fileIds)
            ->where('order_id', $order->id)
            ->get();

        if ($files->isEmpty()) {
            return redirect()->back()->with('error', 'No files selected for download');
        }

        $zipName = "order_{$order->id}_files_" . date('Y-m-d_H-i-s') . ".zip";
        $zipPath = storage_path("app/temp/{$zipName}");

        // Ensure the temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Create new zip archive
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Could not create zip file');
        }

        // Add files to the zip
        foreach ($files as $file) {
            $filePath = storage_path("app/{$file->path}");
            if (file_exists($filePath)) {
                $fileNameInZip = $file->name;

                // If the file is in a folder, add folder structure to zip
                if ($file->folder) {
                    $folderName = $file->folder->name;
                    $fileNameInZip = $folderName . '/' . $fileNameInZip;

                    // If the file is in a subfolder, add that to the path
                    if ($file->subfolder) {
                        $fileNameInZip = $folderName . '/' . $file->subfolder->name . '/' . $file->name;
                    }
                }

                $zip->addFile($filePath, $fileNameInZip);
            }
        }

        $zip->close();

        return Response::download($zipPath, $zipName)->deleteFileAfterSend(true);
    }
}