<?php

namespace App\Http\Controllers;

use App\Data\OrderData;
use App\Interfaces\FileItemServiceInterface;
use App\Interfaces\OrderServiceInterface;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
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

    public function dashboard()
    {
        return Inertia::render('OrderManagement', [
            //
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get query parameters for sorting and filtering
        $perPage = $request->get('per_page', 10);
        $status = $request->get('status');
        $search = $request->get('search');

        // Get orders with pagination through the service
        $orders = $this->orderService->getAllOrders($perPage, $search, $status);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Orders/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
            'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ]);

        $order = $this->orderService->createOrder([
            'name' => $request->name,
            'description' => $request->description,
            'customer_name' => $request->customer_name,
            'deadline' => $request->deadline,
            'files' => $request->file('files'),
        ]);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        // Get the order with its folder structure
        $order = $this->orderService->getOrderById($order->id);

        // Format the folder structure for the frontend
        $formattedFolders = $this->orderService->getOrderFolderStructure($order);

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'name' => $order->name,
                'status' => $order->status,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'deadline' => $order->deadline ? $order->deadline->toDateString() : null,
                'is_approved' => $order->isApproved(),
                'folders' => $order->folders->map(function ($folder) {
                    return [
                        'id' => $folder->id,
                        'name' => $folder->name,
                        'isOpen' => $folder->is_open,
                        'files' => $folder->files->map(function ($file) {
                            return [
                                'id' => $file->id,
                                'name' => $file->name,
                                'path' => $file->path,
                                'status' => $file->status,
                                'created_at' => $file->created_at->toDateString(),
                                'assignedTo' => $file->assignedTo ? [
                                    'id' => $file->assignedTo->id,
                                    'name' => $file->assignedTo->name,
                                    'avatar' => $file->assignedTo->profile_photo_url ?? null,
                                ] : null,
                            ];
                        })->values()->toArray(),
                        'subfolders' => $folder->subfolders->map(function ($subfolder) {
                            return [
                                'id' => $subfolder->id,
                                'name' => $subfolder->name,
                                'isOpen' => $subfolder->is_open,
                                'files' => $subfolder->files->map(function ($file) {
                                    return [
                                        'id' => $file->id,
                                        'name' => $file->name,
                                        'path' => $file->path,
                                        'status' => $file->status,
                                        'created_at' => $file->created_at->toDateString(),
                                        'assignedTo' => $file->assignedTo ? [
                                            'id' => $file->assignedTo->id,
                                            'name' => $file->assignedTo->name,
                                            'avatar' => $file->assignedTo->profile_photo_url ?? null,
                                        ] : null,
                                    ];
                                })->values()->toArray(),
                            ];
                        })->values()->toArray(),
                    ];
                })->values()->toArray(),
                'stats' => $order->getStats(),
            ],
            'fileStructure' => $formattedFolders,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        $order = $this->orderService->getOrderById($order->id);
        $orderData = OrderData::fromModel($order);

        return Inertia::render('Orders/Edit', [
            'order' => $orderData,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
        ]);

        $this->orderService->updateOrder($order, [
            'name' => $request->name,
            'description' => $request->description,
            'customer_name' => $request->customer_name,
            'deadline' => $request->deadline,
        ]);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $this->orderService->deleteOrder($order);

        return redirect()->route('orders.index')
            ->with('success', 'Order deleted successfully.');
    }

    /**
     * Mark an order as completed
     */
    public function markAsCompleted(Order $order): RedirectResponse
    {
        $order->markAsCompleted();

        return redirect()->back()->with('success', 'Order marked as completed.');
    }

    /**
     * Approve a completed order
     */
    public function approve(Order $order): RedirectResponse
    {
        $order->approve();

        return redirect()->back()->with('success', 'Order approved successfully.');
    }

    /**
     * Refresh order statistics.
     */
    public function refreshStats(Order $order)
    {
        return response()->json([
            'stats' => $order->getStats(),
        ]);
    }
}
