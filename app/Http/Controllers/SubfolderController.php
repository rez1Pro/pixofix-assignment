<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Subfolder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SubfolderController extends Controller
{
    /**
     * Store a newly created subfolder.
     */
    public function store(Request $request, Folder $folder): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $subfolder = $folder->subfolders()->create([
            'name' => $validated['name'],
            'is_open' => true,
        ]);

        return redirect()->back()->with('success', "Subfolder '{$subfolder->name}' created successfully.");
    }

    /**
     * Toggle the subfolder's open/close state.
     */
    public function toggleOpen(Request $request, Subfolder $subfolder): RedirectResponse
    {
        $subfolder->update([
            'is_open' => !$subfolder->is_open,
        ]);

        return redirect()->back();
    }

    /**
     * Update the subfolder.
     */
    public function update(Request $request, Subfolder $subfolder): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $subfolder->update([
            'name' => $validated['name'],
        ]);

        return redirect()->back()->with('success', "Subfolder updated successfully.");
    }

    /**
     * Remove the subfolder.
     */
    public function destroy(Subfolder $subfolder): RedirectResponse
    {
        // Check if subfolder has files
        if ($subfolder->files()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete subfolder. Remove all files first.');
        }

        $subfolderName = $subfolder->name;
        $subfolder->delete();

        return redirect()->back()->with('success', "Subfolder '{$subfolderName}' deleted successfully.");
    }

    /**
     * Get subfolder information with files.
     */
    public function show(Subfolder $subfolder): Response
    {
        $subfolder->load('files');

        return Inertia::render('Subfolders/Show', [
            'subfolder' => $subfolder,
            'folder' => $subfolder->folder,
        ]);
    }

    /**
     * API endpoint to get subfolder data with files.
     */
    public function getSubfolderData(Subfolder $subfolder)
    {
        $subfolder->load('files');

        return response()->json([
            'subfolder' => $subfolder,
        ]);
    }
}