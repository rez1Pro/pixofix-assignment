<?php

namespace App\Http\Controllers;

use App\Models\FileItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FileController extends Controller
{
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
            foreach ($request->file('files') as $file) {
                $originalFilename = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileSize = $file->getSize();
                $filename = pathinfo($originalFilename, PATHINFO_FILENAME) . '_' . time() . '.' . $extension;

                // Use the provided directory path or extract from the filename
                $directoryPath = $request->directory_path;
                if (!$directoryPath && strpos($originalFilename, '/') !== false) {
                    $parts = explode('/', $originalFilename);
                    $filename = array_pop($parts);
                    $directoryPath = implode('/', $parts);
                }

                // Store file in the storage
                $filepath = $file->storeAs(
                    "orders/{$order->id}" . ($directoryPath ? "/{$directoryPath}" : ""),
                    $filename
                );

                // Create file record
                FileItem::create([
                    'order_id' => $order->id,
                    'filename' => $filename,
                    'original_filename' => $originalFilename,
                    'filepath' => $filepath,
                    'directory_path' => $directoryPath,
                    'file_type' => $extension,
                    'file_size' => $fileSize,
                    'status' => 'pending',
                ]);
            }
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
        // Delete the physical file
        if (Storage::exists($file->filepath)) {
            Storage::delete($file->filepath);
        }

        // Delete the file record
        $orderId = $file->order_id;
        $file->delete();

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

        $file = Storage::get($file->filepath);
        $mimeType = Storage::mimeType($file->filepath);

        return Response::make($file, 200, [
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

        // Replace the existing file with the processed one
        if ($request->hasFile('processed_file')) {
            $processedFile = $request->file('processed_file');

            // Delete the existing file
            if (Storage::exists($file->filepath)) {
                Storage::delete($file->filepath);
            }

            // Store the new processed file
            $filepath = $processedFile->storeAs(
                dirname($file->filepath),
                $file->filename
            );

            // Update the file record
            $file->update([
                'filepath' => $filepath,
                'is_processed' => true,
                'status' => 'completed',
                'file_size' => $processedFile->getSize(),
            ]);
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
                // Add directory prefix if available to avoid filename collisions
                $fileNameInZip = $file->original_filename;

                // If files have the same name but are in different directories, prefix with directory path
                if ($file->directory_path) {
                    $fileNameInZip = str_replace('/', '_', $file->directory_path) . '_' . $fileNameInZip;
                }

                $zip->addFile(storage_path("app/{$file->filepath}"), $fileNameInZip);
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }
}
