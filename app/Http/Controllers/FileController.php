<?php

namespace App\Http\Controllers;

use App\Models\FileItem;
use App\Models\Order;
use App\Services\FileBatchService;
use App\Services\FileItemService;
use App\Services\FileNamingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FileController extends Controller
{
    protected FileItemService $fileItemService;
    protected FileBatchService $fileBatchService;
    protected FileNamingService $fileNamingService;

    public function __construct(
        FileItemService $fileItemService,
        FileBatchService $fileBatchService,
        FileNamingService $fileNamingService
    ) {
        $this->fileItemService = $fileItemService;
        $this->fileBatchService = $fileBatchService;
        $this->fileNamingService = $fileNamingService;
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
    public function store(Request $request, Order $order)
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
    public function download(FileItem $file)
    {
        if (!Storage::exists($file->filepath)) {
            abort(404, 'File not found');
        }

        return Storage::download(
            $file->filepath,
            $file->original_filename
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FileItem $file)
    {
        $request->validate([
            'status' => 'sometimes|required|in:pending,claimed,processing,completed',
            'is_processed' => 'sometimes|required|boolean',
        ]);

        $file->update($request->only(['status', 'is_processed']));

        return redirect()->back()
            ->with('success', 'File updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FileItem $file)
    {
        $orderId = $file->order_id;
        $this->fileItemService->deleteFile($file);

        return redirect()->route('orders.show', $orderId)
            ->with('success', 'File deleted successfully.');
    }

    /**
     * Preview a file (serve for display)
     */
    public function preview(FileItem $file)
    {
        if (!Storage::exists($file->filepath)) {
            abort(404, 'File not found');
        }

        $fileContent = Storage::get($file->filepath);
        $mimeType = Storage::mimeType($file->filepath);

        return Response::make($fileContent, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $file->original_filename . '"',
        ]);
    }

    /**
     * Upload a processed file replacement
     */
    public function uploadProcessed(Request $request, FileItem $file)
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
            'fileIds' => 'required|array',
            'fileIds.*' => 'required|integer|exists:file_items,id'
        ]);

        $fileIds = $request->input('fileIds');
        $files = FileItem::whereIn('id', $fileIds)->where('order_id', $order->id)->get();

        if ($files->isEmpty()) {
            return redirect()->back()->with('error', 'No files selected for download');
        }

        $zipName = "order_{$order->id}_selected_files.zip";
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
            if (Storage::exists($file->filepath)) {
                // Use original filename instead of just the basename
                // Add directory prefix to maintain original structure
                $fileNameInZip = $file->original_filename;

                // If the file has a directory path, create that structure in the zip
                if ($file->directory_path) {
                    $fileNameInZip = $file->directory_path . '/' . $fileNameInZip;
                }

                $zip->addFile(storage_path("app/{$file->filepath}"), $fileNameInZip);
            }
        }

        $zip->close();

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
    public function markAsCompleted(FileItem $file)
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
}
