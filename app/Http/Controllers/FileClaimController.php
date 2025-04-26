<?php

namespace App\Http\Controllers;

use App\Models\FileClaim;
use App\Models\FileItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FileClaimController extends Controller
{
    /**
     * Display a listing of all claims for the user
     */
    public function index()
    {
        $claims = FileClaim::with(['order'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Claims/Index', [
            'claims' => $claims,
        ]);
    }

    /**
     * Claim files from an order
     */
    public function claimFiles(Request $request, Order $order)
    {
        $request->validate([
            'max_files' => 'nullable|integer|min:1|max:20',
        ]);

        $maxFiles = $request->max_files ?? 10;

        // Get files that are available for claiming (status = pending)
        $availableFiles = $order->fileItems()
            ->where('status', 'pending')
            ->take($maxFiles)
            ->get();

        if ($availableFiles->isEmpty()) {
            return redirect()->back()
                ->with('error', 'No files available for claiming.');
        }

        // Create a claim
        $claim = FileClaim::create([
            'user_id' => Auth::id(),
            'order_id' => $order->id,
            'file_ids' => $availableFiles->pluck('id')->toArray(),
            'claimed_at' => now(),
            'is_completed' => false,
        ]);

        // Mark files as claimed
        $availableFiles->each(function ($file) {
            $file->markAsClaimed();
        });

        // Update order status to in_progress if it was pending
        if ($order->status === 'pending') {
            $order->update(['status' => 'in_progress']);
        }

        return redirect()->route('claims.show', $claim->id)
            ->with('success', 'Successfully claimed ' . $availableFiles->count() . ' files.');
    }

    /**
     * Show a claim with its files
     */
    public function show(FileClaim $claim)
    {
        // Ensure the user can only view their own claims
        if ($claim->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $claim->load('order');
        $files = FileItem::whereIn('id', $claim->file_ids)->get();

        return Inertia::render('Claims/Show', [
            'claim' => $claim,
            'files' => $files,
            'order' => $claim->order,
        ]);
    }

    /**
     * Mark a claim as completed
     */
    public function markCompleted(FileClaim $claim)
    {
        // Ensure the user can only complete their own claims
        if ($claim->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $claim->markAsCompleted();

        // Check if the order is now fully completed
        $order = $claim->order;
        if ($order->isCompleted()) {
            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        return redirect()->route('claims.index')
            ->with('success', 'Claim marked as completed.');
    }

    /**
     * Return files back to the queue for others to claim
     */
    public function returnFiles(FileClaim $claim)
    {
        // Ensure the user can only return their own claims
        if ($claim->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Mark the files as pending again
        FileItem::whereIn('id', $claim->file_ids)
            ->update(['status' => 'pending']);

        // Delete the claim
        $claim->delete();

        return redirect()->route('claims.index')
            ->with('success', 'Files returned to the queue.');
    }

    /**
     * List active claims for an order (admin view)
     */
    public function orderClaims(Order $order)
    {
        $claims = FileClaim::with('user')
            ->where('order_id', $order->id)
            ->orderBy('claimed_at', 'desc')
            ->paginate(10);

        return Inertia::render('Claims/OrderClaims', [
            'claims' => $claims,
            'order' => $order,
        ]);
    }
}
