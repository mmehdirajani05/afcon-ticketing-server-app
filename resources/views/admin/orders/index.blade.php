@extends('admin.layouts.app')

@section('title', 'Orders')
@section('page-title', 'Orders')
@section('breadcrumb', 'Admin › Orders Management')

@section('content')

{{-- Filters --}}
<div class="bg-white rounded-2xl shadow-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap gap-3 items-end">

        <div class="flex-1 min-w-48">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Search</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ref #, user name or email…"
                       class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
            </div>
        </div>

        <div class="w-36">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Booking Status</label>
            <select name="status" class="w-full py-2 px-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
                <option value="">All</option>
                <option value="pending" @selected(request('status')==='pending')>Pending</option>
                <option value="confirmed" @selected(request('status')==='confirmed')>Confirmed</option>
                <option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option>
                <option value="refunded" @selected(request('status')==='refunded')>Refunded</option>
            </select>
        </div>

        <div class="w-36">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Payment</label>
            <select name="payment_status" class="w-full py-2 px-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
                <option value="">All</option>
                <option value="paid" @selected(request('payment_status')==='paid')>Paid</option>
                <option value="pending" @selected(request('payment_status')==='pending')>Pending</option>
                <option value="failed" @selected(request('payment_status')==='failed')>Failed</option>
            </select>
        </div>

        <div class="w-36">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Min Amount</label>
            <input type="number" name="min_amount" value="{{ request('min_amount') }}" placeholder="0"
                   class="w-full py-2 px-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
        </div>

        <div class="w-36">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">Max Amount</label>
            <input type="number" name="max_amount" value="{{ request('max_amount') }}" placeholder="∞"
                   class="w-full py-2 px-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
        </div>

        <div class="w-40">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="w-full py-2 px-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
        </div>

        <div class="w-40">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="w-full py-2 px-3 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:border-primary-500 bg-white">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-xl transition-opacity hover:opacity-90" style="background:#008EC0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filter
            </button>
            @if(request()->hasAny(['search','status','payment_status','from','to','min_amount','max_amount']))
            <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Clear</a>
            @endif
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl shadow-card overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-800">
            All Orders
            <span class="ml-2 text-[11px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ $orders->total() }}</span>
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100 text-left">
                    <th class="px-5 py-3 text-slate-500 font-semibold">Ref #</th>
                    <th class="px-5 py-3 text-slate-500 font-semibold">User</th>
                    <th class="px-5 py-3 text-slate-500 font-semibold">Match</th>
                    <th class="px-5 py-3 text-slate-500 font-semibold">Amount</th>
                    <th class="px-5 py-3 text-slate-500 font-semibold">Payment</th>
                    <th class="px-5 py-3 text-slate-500 font-semibold">Booking</th>
                    <th class="px-5 py-3 text-slate-500 font-semibold">Date</th>
                    <th class="px-5 py-3 text-slate-500 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($orders as $order)
                @php
                $bsc = ['confirmed'=>'text-emerald-700 bg-emerald-50','pending'=>'text-amber-700 bg-amber-50','cancelled'=>'text-red-700 bg-red-50','refunded'=>'text-purple-700 bg-purple-50'];
                $psc = ['paid'=>'text-emerald-700 bg-emerald-50','pending'=>'text-amber-700 bg-amber-50','failed'=>'text-red-700 bg-red-50'];
                @endphp
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-3 font-mono text-[11px]">
                        <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline" style="color:#008EC0">
                            {{ $order->caf_ticket_ref ?? '#' . $order->id }}
                        </a>
                    </td>
                    <td class="px-5 py-3">
                        @if($order->user)
                        <a href="{{ route('admin.users.show', $order->user) }}" class="hover:underline text-slate-700 font-medium">{{ $order->user->name }}</a>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600 max-w-[180px] truncate">{{ $order->match_name ?? '—' }}</td>
                    <td class="px-5 py-3 font-semibold text-slate-800">TZS {{ number_format($order->amount) }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full font-semibold text-[10px] {{ $psc[$order->payment_status] ?? 'text-slate-600 bg-slate-100' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full font-semibold text-[10px] {{ $bsc[$order->booking_status] ?? 'text-slate-600 bg-slate-100' }}">
                            {{ ucfirst($order->booking_status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-slate-400">{{ $order->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="p-1.5 rounded-lg text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @if(!in_array($order->booking_status, ['cancelled','refunded']))
                            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}"
                                  x-data onsubmit.prevent="confirm('Cancel this order?') && $el.submit()">
                                @csrf @method('PATCH')
                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Cancel">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-16 text-center">
                        <svg class="w-12 h-12 mx-auto text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm text-slate-400">No orders found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $orders->links('pagination::tailwind') }}
    </div>
    @endif
</div>
@endsection
