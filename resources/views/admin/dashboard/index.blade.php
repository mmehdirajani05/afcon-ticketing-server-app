@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', config('admin.app_name') . ' › Overview')

@section('content')

{{-- Stats cards --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">

    @php
    $cards = [
        ['label' => 'Total Users',    'value' => number_format($stats['total_users']),   'icon' => 'users',   'color' => '#008EC0', 'bg' => '#e0f4fe'],
        ['label' => 'Active Users',   'value' => number_format($stats['active_users']),  'icon' => 'check',   'color' => '#10b981', 'bg' => '#d1fae5'],
        ['label' => 'Total Orders',   'value' => number_format($stats['total_orders']),  'icon' => 'orders',  'color' => '#8b5cf6', 'bg' => '#ede9fe'],
        ['label' => 'Revenue (TZS)',  'value' => 'TZS ' . number_format($stats['total_revenue'], 0), 'icon' => 'cash', 'color' => '#f59e0b', 'bg' => '#fef3c7'],
        ['label' => 'Pending Orders', 'value' => number_format($stats['pending_orders']), 'icon' => 'clock',  'color' => '#ef4444', 'bg' => '#fee2e2'],
        ['label' => 'Unread Chats',   'value' => number_format($stats['unread_chats']),  'icon' => 'chat',    'color' => '#06b6d4', 'bg' => '#cffafe'],
    ];
    @endphp

    @foreach($cards as $card)
    <div class="bg-white rounded-2xl p-4 shadow-card hover:shadow-card-hover transition-shadow col-span-1">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center justify-center w-9 h-9 rounded-xl"
                 style="background:{{ $card['bg'] }}">
                @if($card['icon'] === 'users')
                    <svg class="w-4.5 h-4.5" style="color:{{ $card['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                @elseif($card['icon'] === 'check')
                    <svg class="w-4.5 h-4.5" style="color:{{ $card['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($card['icon'] === 'orders')
                    <svg class="w-4.5 h-4.5" style="color:{{ $card['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                @elseif($card['icon'] === 'cash')
                    <svg class="w-4.5 h-4.5" style="color:{{ $card['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @elseif($card['icon'] === 'clock')
                    <svg class="w-4.5 h-4.5" style="color:{{ $card['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @else
                    <svg class="w-4.5 h-4.5" style="color:{{ $card['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                @endif
            </div>
        </div>
        <p class="text-lg font-extrabold text-slate-800 leading-none mb-1">{{ $card['value'] }}</p>
        <p class="text-[11px] text-slate-500 font-medium">{{ $card['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Charts row --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6">

    {{-- Revenue chart (2/3 width) --}}
    <div class="xl:col-span-2 bg-white rounded-2xl p-5 shadow-card">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Revenue Overview</h3>
                <p class="text-[11px] text-slate-400">Last 6 months · TZS</p>
            </div>
            <div class="flex items-center gap-2 text-[11px] text-slate-500">
                <span class="w-2 h-2 rounded-full inline-block" style="background:#008EC0"></span> Revenue
                <span class="w-2 h-2 rounded-full inline-block ml-2 bg-slate-300"></span> New Users
            </div>
        </div>
        <div class="h-56">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Booking status doughnut (1/3 width) --}}
    <div class="bg-white rounded-2xl p-5 shadow-card">
        <div class="mb-4">
            <h3 class="text-sm font-bold text-slate-800">Booking Status</h3>
            <p class="text-[11px] text-slate-400">Distribution breakdown</p>
        </div>
        <div class="h-40 flex items-center justify-center">
            <canvas id="statusChart"></canvas>
        </div>
        <div class="mt-4 space-y-2">
            @php
            $statusColors = ['confirmed'=>'#10b981','pending'=>'#f59e0b','cancelled'=>'#ef4444','refunded'=>'#8b5cf6'];
            @endphp
            @foreach($bookingStatuses as $status => $count)
            <div class="flex items-center justify-between text-[11px]">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full" style="background:{{ $statusColors[$status] ?? '#94a3b8' }}"></span>
                    <span class="capitalize text-slate-600">{{ $status }}</span>
                </div>
                <span class="font-semibold text-slate-800">{{ number_format($count) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Recent activity row --}}
<div class="grid grid-cols-1 xl:grid-cols-5 gap-4">

    {{-- Recent orders table (3/5) --}}
    <div class="xl:col-span-3 bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Recent Orders</h3>
            <a href="{{ route('admin.orders.index') }}" class="text-[11px] font-semibold hover:underline" style="color:#008EC0">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-5 py-2.5 text-slate-500 font-semibold">Ref</th>
                        <th class="text-left px-5 py-2.5 text-slate-500 font-semibold">User</th>
                        <th class="text-left px-5 py-2.5 text-slate-500 font-semibold">Amount</th>
                        <th class="text-left px-5 py-2.5 text-slate-500 font-semibold">Status</th>
                        <th class="text-left px-5 py-2.5 text-slate-500 font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($recentOrders as $order)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3 font-mono text-[11px] text-slate-600">
                            <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline" style="color:#008EC0">
                                {{ $order->caf_ticket_ref ?? '#' . $order->id }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-slate-700">{{ $order->user?->name ?? '—' }}</td>
                        <td class="px-5 py-3 font-semibold text-slate-800">{{ number_format($order->amount) }}</td>
                        <td class="px-5 py-3">
                            @php
                            $sc = ['confirmed'=>'text-emerald-700 bg-emerald-50','pending'=>'text-amber-700 bg-amber-50','cancelled'=>'text-red-700 bg-red-50','refunded'=>'text-purple-700 bg-purple-50'];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full font-semibold text-[10px] {{ $sc[$order->booking_status] ?? 'text-slate-600 bg-slate-100' }}">
                                {{ ucfirst($order->booking_status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-400">{{ $order->created_at->format('d M') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent users (2/5) --}}
    <div class="xl:col-span-2 bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">New Users</h3>
            <a href="{{ route('admin.users.index') }}" class="text-[11px] font-semibold hover:underline" style="color:#008EC0">View all →</a>
        </div>
        <ul class="divide-y divide-slate-50">
            @forelse($recentUsers as $user)
            <li class="flex items-center gap-3 px-5 py-3 hover:bg-slate-50 transition-colors">
                <div class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold text-white shrink-0"
                     style="background: linear-gradient(135deg, #008EC0, #40BADF)">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-800 truncate">{{ $user->name }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ $user->email }}</p>
                </div>
                <div>
                    @if($user->is_active)
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                    @else
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 inline-block"></span>
                    @endif
                </div>
            </li>
            @empty
            <li class="px-5 py-8 text-center text-xs text-slate-400">No users yet.</li>
            @endforelse
        </ul>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Revenue + Users combo chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: @json($months),
            datasets: [
                {
                    label: 'Revenue (TZS)',
                    data: @json($revenues),
                    backgroundColor: 'rgba(0,142,192,0.15)',
                    borderColor: '#008EC0',
                    borderWidth: 2,
                    borderRadius: 6,
                    yAxisID: 'y',
                },
                {
                    label: 'New Users',
                    data: @json($newUsers),
                    type: 'line',
                    borderColor: '#94a3b8',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#94a3b8',
                    tension: 0.4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 11 }, color: '#94a3b8',
                             callback: v => v >= 1000000 ? (v/1000000).toFixed(1) + 'M' : v >= 1000 ? (v/1000).toFixed(0) + 'K' : v }
                },
                y1: { display: false, position: 'right' }
            }
        }
    });

    // Status doughnut
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json($bookingStatuses);
    const statusColors = { confirmed: '#10b981', pending: '#f59e0b', cancelled: '#ef4444', refunded: '#8b5cf6' };
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: Object.keys(statusData).map(s => statusColors[s] || '#94a3b8'),
                borderWidth: 0,
                hoverOffset: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false }, tooltip: { callbacks: {
                label: ctx => ` ${ctx.label}: ${ctx.parsed}`
            }}}
        }
    });
});
</script>
@endpush
