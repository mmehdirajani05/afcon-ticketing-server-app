@extends('admin.layouts.app')

@section('title', 'Order #' . ($booking->caf_ticket_ref ?? $booking->id))
@section('page-title', 'Order Details')
@section('breadcrumb', 'Admin › Orders › #' . ($booking->caf_ticket_ref ?? $booking->id))

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

    {{-- Main details --}}
    <div class="xl:col-span-2 space-y-4">

        {{-- Header card --}}
        <div class="bg-white rounded-2xl shadow-card overflow-hidden">
            <div class="px-6 py-5" style="background: linear-gradient(135deg, #0B1629 0%, #006B91 100%)">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs text-white/50 font-semibold uppercase tracking-widest mb-1">Booking Reference</p>
                        <p class="text-xl font-extrabold text-white font-mono">{{ $booking->caf_ticket_ref ?? '#' . $booking->id }}</p>
                    </div>
                    <div class="text-right">
                        @php
                        $bsc = ['confirmed'=>'text-emerald-400 bg-emerald-900/30 border-emerald-500/30','pending'=>'text-amber-400 bg-amber-900/30 border-amber-500/30','cancelled'=>'text-red-400 bg-red-900/30 border-red-500/30','refunded'=>'text-purple-400 bg-purple-900/30 border-purple-500/30'];
                        @endphp
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold border {{ $bsc[$booking->booking_status] ?? 'text-slate-400 bg-slate-800/30 border-slate-600/30' }}">
                            {{ ucfirst($booking->booking_status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="p-6 grid grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                @foreach([
                    ['label'=>'Match',      'value'=> $booking->match_name ?? '—'],
                    ['label'=>'Venue',      'value'=> $booking->venue ?? '—'],
                    ['label'=>'Match Date', 'value'=> $booking->match_date ? \Carbon\Carbon::parse($booking->match_date)->format('d M Y, H:i') : '—'],
                    ['label'=>'Category',   'value'=> strtoupper($booking->ticket_category ?? '—')],
                    ['label'=>'Seat Info',  'value'=> $booking->seat_info ?? '—'],
                    ['label'=>'Fan ID',     'value'=> $booking->fan_id ?? '—'],
                ] as $f)
                <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">{{ $f['label'] }}</p>
                    <p class="font-semibold text-slate-800">{{ $f['value'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Payment details --}}
        <div class="bg-white rounded-2xl shadow-card p-6">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Payment Details</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                @php
                $psc = ['paid'=>'text-emerald-700 bg-emerald-50','pending'=>'text-amber-700 bg-amber-50','failed'=>'text-red-700 bg-red-50'];
                @endphp
                <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">Amount</p>
                    <p class="text-lg font-extrabold text-slate-900">{{ $booking->currency }} {{ number_format($booking->amount, 2) }}</p>
                </div>
                <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">Payment Status</p>
                    <span class="px-2 py-0.5 rounded-full font-semibold text-[10px] {{ $psc[$booking->payment_status] ?? 'text-slate-600 bg-slate-100' }}">
                        {{ ucfirst($booking->payment_status) }}
                    </span>
                </div>
                <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">Transaction ID</p>
                    <p class="font-mono font-semibold text-slate-700 break-all">{{ $booking->transaction_id ?? '—' }}</p>
                </div>
                @if($booking->refund_status)
                <div class="bg-purple-50 rounded-xl p-3">
                    <p class="text-[10px] text-purple-400 font-medium mb-0.5">Refund Status</p>
                    <p class="font-semibold text-purple-800">{{ ucfirst(str_replace('_',' ',$booking->refund_status)) }}</p>
                </div>
                @endif
                @if($booking->refund_requested_at)
                <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">Refund Requested</p>
                    <p class="font-semibold text-slate-700">{{ $booking->refund_requested_at->format('d M Y') }}</p>
                </div>
                @endif
                @if($booking->refunded_at)
                <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">Refunded At</p>
                    <p class="font-semibold text-slate-700">{{ $booking->refunded_at->format('d M Y') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right sidebar --}}
    <div class="space-y-4">

        {{-- Customer card --}}
        <div class="bg-white rounded-2xl shadow-card p-5">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Customer</h3>
            @if($booking->user)
            <div class="flex items-center gap-3 mb-4">
                <div class="flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold text-white"
                     style="background: linear-gradient(135deg, #008EC0, #40BADF)">
                    {{ strtoupper(substr($booking->user->name, 0, 2)) }}
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800">{{ $booking->user->name }}</p>
                    <p class="text-xs text-slate-400">{{ $booking->user->email }}</p>
                </div>
            </div>
            <a href="{{ route('admin.users.show', $booking->user) }}"
               class="flex items-center justify-center gap-2 w-full py-2 text-xs font-semibold rounded-xl border hover:bg-slate-50 transition-colors"
               style="color:#008EC0; border-color:#008EC0">
                View Customer Profile →
            </a>
            @else
            <p class="text-xs text-slate-400">User not found.</p>
            @endif
        </div>

        {{-- Actions --}}
        <div class="bg-white rounded-2xl shadow-card p-5">
            <h3 class="text-sm font-bold text-slate-800 mb-3">Actions</h3>
            <div class="space-y-2">
                @if(!in_array($booking->booking_status, ['cancelled','refunded']))
                <form method="POST" action="{{ route('admin.orders.cancel', $booking) }}"
                      x-data onsubmit.prevent="confirm('Cancel this order?') && $el.submit()">
                    @csrf @method('PATCH')
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 text-xs font-semibold text-red-700 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel Order
                    </button>
                </form>
                @endif
                <a href="{{ route('admin.orders.index') }}"
                   class="flex items-center justify-center gap-2 w-full py-2 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">
                    ← Back to Orders
                </a>
            </div>
        </div>

        {{-- Meta --}}
        <div class="bg-white rounded-2xl shadow-card p-5 text-xs space-y-2">
            <h3 class="text-sm font-bold text-slate-800 mb-3">Metadata</h3>
            <div class="flex justify-between"><span class="text-slate-400">Created</span><span class="font-semibold text-slate-700">{{ $booking->created_at->format('d M Y, H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">Updated</span><span class="font-semibold text-slate-700">{{ $booking->updated_at->format('d M Y, H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-slate-400">Currency</span><span class="font-semibold text-slate-700">{{ $booking->currency }}</span></div>
        </div>
    </div>
</div>
@endsection
