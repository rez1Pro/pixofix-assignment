<?php

namespace App\Http\Controllers;

use App\Interfaces\FileItemServiceInterface;
use App\Models\FileItem;
use App\Models\Folder;
use App\Models\Order;
use App\Models\Subfolder;
use App\Services\FileBatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;

class FileController extends Controller
{
    protected FileItemServiceInterface $fileItemService;
    protected FileBatchService $fileBatchService;

    public function __construct(
        FileItemServiceInterface $fileItemService,
        FileBatchService $fileBatchService
    ) {
        $this->fileItemService = $fileItemService;
        $this->fileBatchService = $fileBatchService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Order $order)
    {
        $files = $order->fileItems()
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->directory, function ($query, $directory) {
                return $query->where('directory_path', $directory);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Files/Index', [
            'order' => $order,
            'files' => $files,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Order $order)
    {
        return Inertia::render('Files/Create', [
            'order' => $order,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'directory_path' => 'nullable|string',
        ]);

        // Process the uploaded files
        if ($request->hasFile('files')) {
            $this->fileItemService->processFiles($order, $request->file('files'));
        }

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Files added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FileItem $file)
    {
        return Inertia::render('Files/Show', [
            'file' => $file,
            'order' => $file->order,
        ]);
    }

    /**
     * Download a file
     */
    public function download(FileItem $file): BinaryFileResponse
    {
        return $this->fileItemService->downloadFile($file);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FileItem $file): RedirectResponse
    {
        $request->validate([
            'status' => 'sometimes|required|in:pending,in_progress,approved,rejected',
        ]);

        if ($request->has('status')) {
            $this->fileItemService->updateFileStatus($file, $request->status);
        }

        return redirect()->back()
            ->with('success', 'File updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FileItem $file): RedirectResponse
    {
        $this->fileItemService->deleteFile($file);

        return redirect()->back()->with('success', 'File deleted successfully.');
    }

    /**
     * Preview a file (serve for display)
     */
    public function preview(FileItem $file)
    {
        if (!file_exists(storage_path('app/' . $file->filepath))) {
            abort(404, 'File not found');
        }

        $fileContent = file_get_contents(storage_path('app/' . $file->filepath));
        $mimeType = mime_content_type(storage_path('app/' . $file->filepath));

        return response($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . ($file->original_filename ?? $file->filename) . '"',
        ]);
    }

    /**
     * Upload a processed file replacement
     */
    public function uploadProcessed(Request $request, FileItem $file): RedirectResponse
    {
        $request->validate([
            'processed_file' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ]);

        // Process the edited file
        if ($request->hasFile('processed_file')) {
            $this->fileItemService->processEditedFile($file, $request->file('processed_file'));
        }

        return redirect()->back()
            ->with('success', 'Processed file uploaded successfully.');
    }

    /**
     * Download selected files as a zip archive
     */
    public function downloadSelected(Request $request, Order $order)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'required|integer|exists:file_items,id'
        ]);

        $fileIds = $request->input('file_ids');
        $zipPath = $this->fileItemService->createZipArchiveFromFiles($order, $fileIds);
        $zipName = basename($zipPath);

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    /**
     * Claim a batch of files for processing
     */
    public function claimBatch(Request $request, Order $order)
    {
        $request->validate([
            'batch_size' => 'required|integer|min:1|max:50',
            'directory' => 'nullable|string',
        ]);

        $batchSize = $request->input('batch_size', 10);
        $directory = $request->input('directory');

        $fileClaim = $this->fileBatchService->claimBatch($order, auth()->user(), $batchSize, $directory);

        if (!$fileClaim) {
            return redirect()->back()->with('error', 'No files available to claim');
        }

        return redirect()->route('fileclaims.show', $fileClaim->id)
            ->with('success', 'Successfully claimed a batch of ' . count($fileClaim->file_ids) . ' files');
    }

    /**
     * Mark a file as completed
     */
    public function markAsCompleted(FileItem $file): RedirectResponse
    {
        $this->fileItemService->markAsCompleted($file);

        return redirect()->back()->with('success', 'File marked as completed');
    }

    /**
     * Get files grouped by directory
     */
    public function getDirectoryStructure(Order $order)
    {
        $groupedFiles = $this->fileItemService->getFileItemsGroupedByDirectory($order);

        return response()->json($groupedFiles);
    }

    /**
     * Upload files to a folder.
     */
    public function uploadToFolder(Request $request, Folder $folder): RedirectResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|max:10240', // 10MB max per file
        ]);

        $uploadedFiles = $this->fileItemService->uploadFilesToFolder($folder, $request->file('files'));

        return redirect()->back()->with('success', count($uploadedFiles) . ' files uploaded successfully.');
    }

    /**
     * Upload files to a subfolder.
     */
    public function uploadToSubfolder(Request $request, Subfolder $subfolder): RedirectResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|max:10240', // 10MB max per file
        ]);

        $uploadedFiles = $this->fileItemService->uploadFilesToSubfolder($subfolder, $request->file('files'));

        return redirect()->back()->with('success', count($uploadedFiles) . ' files uploaded successfully.');
    }

    /**
     * Assign a file to the current user.
     */
    public function assignToSelf(Request $request, FileItem $file): RedirectResponse
    {
        $this->fileItemService->assignFileToUser($file, Auth::user());

        return redirect()->back()->with('success', 'File assigned to you successfully.');
    }

    /**
     * Assign multiple files to the current user.
     */
    public function assignMultipleToSelf(Request $request): RedirectResponse
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'required|exists:file_items,id',
        ]);

        $count = $this->fileItemService->assignMultipleFilesToUser($request->file_ids, Auth::user());

        return redirect()->back()->with('success', $count . ' files assigned to you successfully.');
    }

    /**
     * Update the status of a file.
     */
    public function updateStatus(Request $request, FileItem $file): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,approved,rejected',
        ]);

        $this->fileItemService->updateFileStatus($file, $request->status);

        return redirect()->back()->with('success', 'File status updated successfully.');
    }

    /**
     * Get file information.
     */
    public function getFileInfo(FileItem $file)
    {
        $file->load(['folder', 'subfolder', 'assignedTo']);

        return response()->json([
            'file' => $file,
        ]);
    }
}
