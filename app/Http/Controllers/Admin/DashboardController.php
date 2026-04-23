<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ChatMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // Summary stats
        $stats = [
            'total_users'   => User::where('global_role', 'customer')->count(),
            'active_users'  => User::where('global_role', 'customer')->where('is_active', true)->count(),
            'total_orders'  => Booking::count(),
            'total_revenue' => (float) Booking::where('payment_status', 'paid')->sum('amount'),
            'pending_orders'=> Booking::where('booking_status', 'pending')->count(),
            'unread_chats'  => ChatMessage::where('direction', 'user')->where('is_read', false)->count(),
        ];

        // Monthly revenue for the last 6 months (for chart)
        $revenueChart = Booking::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total')
            )
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $now->copy()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $months = [];
        $revenues = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = $now->copy()->subMonths($i)->format('Y-m');
            $months[]   = $now->copy()->subMonths($i)->format('M Y');
            $revenues[] = $revenueChart->get($key)?->total ?? 0;
        }

        // Monthly new users
        $usersChart = User::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as total')
            )
            ->where('global_role', 'customer')
            ->where('created_at', '>=', $now->copy()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $newUsers = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = $now->copy()->subMonths($i)->format('Y-m');
            $newUsers[] = $usersChart->get($key)?->total ?? 0;
        }

        // Booking status breakdown (for doughnut chart)
        $bookingStatuses = Booking::select('booking_status', DB::raw('COUNT(*) as count'))
            ->groupBy('booking_status')
            ->pluck('count', 'booking_status');

        // Recent orders
        $recentOrders = Booking::with('user')
            ->latest()
            ->limit(8)
            ->get();

        // Recent users
        $recentUsers = User::where('global_role', 'customer')
            ->latest()
            ->limit(6)
            ->get();

        return view('admin.dashboard.index', compact(
            'stats',
            'months',
            'revenues',
            'newUsers',
            'bookingStatuses',
            'recentOrders',
            'recentUsers'
        ));
    }
}
