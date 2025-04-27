<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class FolderController extends Controller
{
    /**
     * Store a newly created folder.
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder = $order->folders()->create([
            'name' => $validated['name'],
            'is_open' => true,
        ]);

        return redirect()->back()->with('success', "Folder '{$folder->name}' created successfully.");
    }

    /**
     * Toggle the folder's open/close state.
     */
    public function toggleOpen(Request $request, Folder $folder): RedirectResponse
    {
        $folder->update([
            'is_open' => !$folder->is_open,
        ]);

        return redirect()->back();
    }

    /**
     * Update the folder.
     */
    public function update(Request $request, Folder $folder): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder->update([
            'name' => $validated['name'],
        ]);

        return redirect()->back()->with('success', "Folder updated successfully.");
    }

    /**
     * Remove the folder.
     */
    public function destroy(Folder $folder): RedirectResponse
    {
        // Check if folder has files or subfolders
        if ($folder->files()->count() > 0 || $folder->subfolders()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete folder. Remove all files and subfolders first.');
        }

        $folderName = $folder->name;
        $folder->delete();

        return redirect()->back()->with('success', "Folder '{$folderName}' deleted successfully.");
    }

    /**
     * Get folder information with files and subfolders.
     */
    public function show(Folder $folder): Response
    {
        $folder->load(['files', 'subfolders.files']);

        return Inertia::render('Folders/Show', [
            'folder' => $folder,
        ]);
    }

    /**
     * API endpoint to get folder data with files and subfolders.
     */
    public function getFolderData(Folder $folder)
    {
        $folder->load(['files', 'subfolders.files']);

        return response()->json([
            'folder' => $folder,
        ]);
    }
}