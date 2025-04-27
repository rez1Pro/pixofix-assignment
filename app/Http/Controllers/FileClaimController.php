<?php

namespace App\Http\Controllers;

use App\Models\FileClaim;
use App\Models\FileItem;
use App\Models\Order;
use App\Services\FileBatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FileClaimController extends Controller
{
    protected FileBatchService $fileBatchService;

    public function __construct(FileBatchService $fileBatchService)
    {
        $this->fileBatchService = $fileBatchService;
    }

    /**
     * Display a listing of the claims for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();
        $claims = $this->fileBatchService->getUserActiveBatches($user);

        return Inertia::render('FileClaims/Index', [
            'claims' => $claims,
        ]);
    }

    /**
     * Display the specified claim with its files.
     */
    public function show(FileClaim $fileclaim)
    {
        $files = $this->fileBatchService->getBatchFiles($fileclaim);
        $stats = $this->fileBatchService->getBatchStats($fileclaim);

        return Inertia::render('FileClaims/Show', [
            'claim' => $fileclaim,
            'files' => $files,
            'stats' => $stats,
            'order' => $fileclaim->order,
        ]);
    }

    /**
     * Claim a batch of files for an order.
     */
    public function claimFiles(Request $request, Order $order)
    {
        $request->validate([
            'batch_size' => 'required|integer|min:1|max:50',
            'directory' => 'nullable|string',
        ]);

        $user = Auth::user();
        $batchSize = $request->input('batch_size');
        $directory = $request->input('directory');

        $fileClaim = $this->fileBatchService->claimBatch($order, $user, $batchSize, $directory);

        if (!$fileClaim) {
            return redirect()->back()->with('error', 'No files available to claim');
        }

        return redirect()->route('fileclaims.show', $fileClaim->id)
            ->with('success', 'Successfully claimed a batch of ' . count($fileClaim->file_ids) . ' files');
    }

    /**
     * Mark a claim as completed.
     */
    public function complete(FileClaim $fileclaim)
    {
        // Authorization check - only the owner can complete a claim
        if ($fileclaim->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to complete this claim');
        }

        $this->fileBatchService->completeBatch($fileclaim);

        return redirect()->route('claims.index')
            ->with('success', 'Batch marked as completed');
    }

    /**
     * Release a claim (return files to the pool)
     */
    public function release(FileClaim $fileclaim)
    {
        // Authorization check - only the owner can release a claim
        if ($fileclaim->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to release this claim');
        }

        $this->fileBatchService->releaseBatch($fileclaim);

        return redirect()->route('claims.index')
            ->with('success', 'Files released successfully');
    }

    /**
     * List all claims for an order (admin view)
     */
    public function orderClaims(Order $order)
    {
        $claims = FileClaim::where('order_id', $order->id)
            ->with(['user'])
            ->get();

        return Inertia::render('Orders/Claims', [
            'order' => $order,
            'claims' => $claims,
        ]);
    }
}
