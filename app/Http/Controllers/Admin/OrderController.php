<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with('user');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('caf_ticket_ref', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                                                     ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->get('status')) {
            $query->where('booking_status', $status);
        }

        if ($paymentStatus = $request->get('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($minAmount = $request->get('min_amount')) {
            $query->where('amount', '>=', $minAmount);
        }

        if ($maxAmount = $request->get('max_amount')) {
            $query->where('amount', '<=', $maxAmount);
        }

        $orders = $query->latest()->paginate(config('admin.per_page'))->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Booking $booking)
    {
        $booking->load('user');

        return view('admin.orders.show', compact('booking'));
    }

    public function cancel(Booking $booking)
    {
        $booking->update(['booking_status' => 'cancelled']);

        return back()->with('success', 'Order cancelled successfully.');
    }
}
