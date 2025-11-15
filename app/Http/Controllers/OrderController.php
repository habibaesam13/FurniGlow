<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
       // dd(config('services.stripe.secret'));

        $this->orderService = $orderService;
    }

    /**
     * Place order and redirect to checkout (if visa) or order page (cash)
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,visa',
        ]);

        $userId = $request->user()->id;
        $paymentMethod = $request->input('payment_method');

        try {
            $order = $this->orderService->createOrder( $paymentMethod);
            if ($paymentMethod === 'visa') {
                return redirect()->route('checkout.show', ['order' => $order->id])
                    ->with('success', 'Order created — please complete payment.');
            }

            return redirect()->route('orders.show', $order)->with('success', 'Order placed.');
        } catch (\Throwable $e) {
            // Log error in production
            return back()->with('error', $e->getMessage());
        }
    }


    public function index()
    {
        $ordersByMonth = $this->orderService->getUserOrders();
        return view('orders.index', compact('ordersByMonth'));
    }
    public function show($id)
    {
        $order = $this->orderService->getOrderById($id);
        return view('orders.show', compact('order'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $this->orderService->updateOrder($id, $request->only('status'));
        return redirect()->route('orders.index')->with('success', 'Order updated successfully');
    }

    public function destroy($id)
    {
        $this->orderService->deleteOrder($id);
        return redirect()->route('orders.index')->with('success', 'Order deleted successfully');
    }
}
