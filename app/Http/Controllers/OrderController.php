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

    /**
     * Display the dashboard view.
     */
    public function dashboard()
    {
        return Inertia::render('OrderManagement');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $status = $request->get('status');
        $search = $request->get('search');

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
            'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ]);

        $orderData = array_merge($validated, [
            'files' => $request->file('files'),
        ]);

        $order = $this->orderService->createOrder($orderData);

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $orderModel = $this->orderService->getOrderById($order->id);
        $orderData = $this->orderService->prepareOrderForDisplay($order->id);
        $formattedFolders = $this->orderService->getOrderFolderStructure($orderModel);
        $userPermissions = $this->orderService->getUserPermissionsForOrder($orderData);

        return Inertia::render('Orders/Show', [
            'order' => $orderData,
            'fileStructure' => $formattedFolders,
            'userPermissions' => $userPermissions,
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
        ]);

        $this->orderService->updateOrder($order, $validated);

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
        $this->orderService->markOrderAsCompleted($order);

        return redirect()->back()->with('success', 'Order marked as completed.');
    }

    /**
     * Approve a completed order
     */
    public function approve(Order $order): RedirectResponse
    {
        $this->orderService->approveOrder($order);

        return redirect()->back()->with('success', 'Order approved successfully.');
    }

    /**
     * Refresh order statistics.
     */
    public function refreshStats(Order $order)
    {
        return response()->json([
            'stats' => $this->orderService->getOrderStats($order),
        ]);
    }
}
