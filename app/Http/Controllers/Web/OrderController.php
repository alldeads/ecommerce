<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService
    ) {}

    /**
     * Display checkout page.
     */
    public function checkout(): Response
    {
        $user = Auth::user();

        return Inertia::render('Checkout', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Store a new order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'shipping_address' => 'nullable|string',
        ]);

        $user = Auth::user();

        try {
            $order = $this->checkoutService->createOrder(
                $user,
                $validated['items'],
                [
                    'customer_name' => $validated['customer_name'],
                    'customer_email' => $validated['customer_email'],
                    'shipping_address' => $validated['shipping_address'] ?? null,
                ]
            );

            return redirect()->route('orders.index')->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display order history page.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $orders = $this->checkoutService->getUserOrders($user);

        return Inertia::render('Orders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display single order details page.
     */
    public function show(int $id): Response
    {
        $order = $this->checkoutService->getOrder($id);

        if (!$order) {
            abort(404, 'Order not found');
        }

        // Check if the order belongs to the authenticated user
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('OrderDetail', [
            'order' => $order,
        ]);
    }
}
