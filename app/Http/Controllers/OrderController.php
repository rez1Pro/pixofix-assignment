<?php

namespace App\Http\Controllers;

use App\Data\OrderData;
use App\Interfaces\FileItemServiceInterface;
use App\Interfaces\OrderServiceInterface;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
            'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ]);

        $order = $this->orderService->createOrder([
            'name' => $request->name,
            'description' => $request->description,
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
        $order = $this->orderService->getOrderById($order->id);

        // Get files grouped by directory path
        $filesGrouped = $this->fileItemService->getFileItemsGroupedByDirectory($order);

        // Get stats for the order
        $stats = $this->orderService->getOrderStats($order);

        // Convert to Data object for API consistency
        $orderData = OrderData::fromModel($order);

        return Inertia::render('Orders/Show', [
            'order' => $orderData,
            'filesGrouped' => $filesGrouped,
            'stats' => $stats,
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
        ]);

        $this->orderService->updateOrder($order, [
            'name' => $request->name,
            'description' => $request->description,
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
    public function markAsCompleted(Order $order)
    {
        $order = $this->orderService->markOrderAsCompleted($order);

        if ($order->status === 'completed') {
            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Order marked as completed.');
        }

        return redirect()->route('orders.show', $order->id)
            ->with('error', 'Not all files are processed yet.');
    }

    /**
     * Approve a completed order
     */
    public function approve(Order $order)
    {
        $order = $this->orderService->approveOrder($order);

        if ($order->status === 'approved') {
            return redirect()->route('orders.show', $order->id)
                ->with('success', 'Order approved successfully.');
        }

        return redirect()->route('orders.show', $order->id)
            ->with('error', 'Order must be completed before it can be approved.');
    }
}
