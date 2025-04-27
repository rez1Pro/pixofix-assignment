<?php

namespace App\Http\Controllers;

use App\Models\FileItem;
use App\Models\Folder;
use App\Models\Order;
use App\Models\Subfolder;
use App\Models\User;
use App\Services\FileBatchService;
use App\Services\FileItemService;
use App\Services\FileNamingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Services\OrderService;

class FileController extends Controller
{
    protected FileItemService $fileItemService;
    protected FileBatchService $fileBatchService;
    protected FileNamingService $fileNamingService;
    protected OrderService $orderService;

    public function __construct(
        FileItemService $fileItemService,
        FileBatchService $fileBatchService,
        FileNamingService $fileNamingService,
        OrderService $orderService
    ) {
        $this->fileItemService = $fileItemService;
        $this->fileBatchService = $fileBatchService;
        $this->fileNamingService = $fileNamingService;
        $this->orderService = $orderService;
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
    public function download(FileItem $file): BinaryFileResponse
    {
        $path = storage_path('app/' . $file->path);

        if (!file_exists($path)) {
            abort(404, 'File not found');
        }

        return response()->download($path, $file->name);
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
    public function destroy(FileItem $file): RedirectResponse
    {
        // Delete the file from storage
        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        // Delete the record
        $file->delete();

        return redirect()->back()->with('success', 'File deleted successfully.');
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

    /**
     * Upload files to a folder.
     */
    public function uploadToFolder(Request $request, Folder $folder): RedirectResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|file|max:10240', // 10MB max per file
        ]);

        $uploadedFiles = $this->orderService->uploadFilesToFolder($folder, $request->file('files'));

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

        $uploadedFiles = $this->orderService->uploadFilesToSubfolder($subfolder, $request->file('files'));

        return redirect()->back()->with('success', count($uploadedFiles) . ' files uploaded successfully.');
    }

    /**
     * Assign a file to the current user.
     */
    public function assignToSelf(Request $request, FileItem $file): RedirectResponse
    {
        $file->assignTo(Auth::user());

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

        $user = Auth::user();
        $count = 0;

        foreach ($request->file_ids as $fileId) {
            $file = FileItem::findOrFail($fileId);
            $file->assignTo($user);
            $count++;
        }

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

        $status = $request->status;

        switch ($status) {
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
