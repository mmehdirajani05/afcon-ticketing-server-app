@extends('admin.layouts.app')

@section('title', $user->name)
@section('page-title', $user->name)
@section('breadcrumb', 'Admin › Users › ' . $user->name)

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

    {{-- Profile card --}}
    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        {{-- Cover gradient --}}
        <div class="h-20" style="background: linear-gradient(135deg, #0B1629 0%, #008EC0 100%)"></div>

        <div class="px-5 pb-5 -mt-10">
            <div class="flex items-end justify-between mb-4">
                <div class="flex items-center justify-center w-16 h-16 rounded-2xl text-lg font-extrabold text-white border-4 border-white"
                     style="background: linear-gradient(135deg, #008EC0 0%, #40BADF 100%)">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div class="flex gap-2 mt-10">
                    <a href="{{ route('admin.users.edit', $user) }}"
                       class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white rounded-lg transition-opacity hover:opacity-90"
                       style="background:#008EC0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                </div>
            </div>

            <h2 class="text-base font-extrabold text-slate-900">{{ $user->name }}</h2>
            <p class="text-xs text-slate-400 mb-4">{{ $user->email }}</p>

            <div class="flex flex-wrap gap-1.5 mb-5">
                @if($user->is_active)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold text-emerald-700 bg-emerald-50">Active</span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold text-red-700 bg-red-50">Suspended</span>
                @endif
                @if($user->email_verified_at)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold text-blue-700 bg-blue-50">Email Verified</span>
                @endif
                @if($user->fan_id)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold text-purple-700 bg-purple-50">Fan ID: {{ $user->fan_id }}</span>
                @endif
            </div>

            <div class="space-y-3 text-xs">
                @foreach([
                    ['label' => 'Phone', 'value' => $user->phone ?? '—'],
                    ['label' => 'Registered via', 'value' => ucfirst($user->registration_source ?? 'email')],
                    ['label' => 'Last login', 'value' => $user->last_login_at?->diffForHumans() ?? 'Never'],
                    ['label' => 'Joined', 'value' => $user->created_at->format('d M Y, H:i')],
                ] as $field)
                <div class="flex justify-between items-center py-2 border-b border-slate-50">
                    <span class="text-slate-400 font-medium">{{ $field['label'] }}</span>
                    <span class="text-slate-700 font-semibold">{{ $field['value'] }}</span>
                </div>
                @endforeach
            </div>

            {{-- Action buttons --}}
            <div class="mt-5 flex flex-col gap-2">
                @if($user->is_active)
                <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 text-xs font-semibold text-orange-700 bg-orange-50 rounded-xl hover:bg-orange-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Suspend User
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Reactivate User
                    </button>
                </form>
                @endif
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                      x-data onsubmit.prevent="confirm('Permanently delete this user?') && $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 text-xs font-semibold text-red-700 bg-red-50 rounded-xl hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div class="xl:col-span-2 space-y-4">

        {{-- Fan ID info --}}
        @if($user->fan_id_status)
        <div class="bg-white rounded-2xl shadow-card p-5">
            <h3 class="text-sm font-bold text-slate-800 mb-4">Fan ID Application</h3>
            <div class="grid grid-cols-2 gap-3 text-xs">
                @foreach([
                    ['label'=>'Full Name',       'value'=>$user->fan_id_full_name ?? '—'],
                    ['label'=>'Identity Type',    'value'=>ucfirst(str_replace('_',' ',$user->fan_id_identity_type ?? '—'))],
                    ['label'=>'Nationality',      'value'=>$user->fan_id_nationality ?? '—'],
                    ['label'=>'Date of Birth',    'value'=>$user->fan_id_date_of_birth?->format('d M Y') ?? '—'],
                    ['label'=>'Fan ID Status',    'value'=>ucfirst($user->fan_id_status)],
                    ['label'=>'Fan ID',           'value'=>$user->fan_id ?? 'Not assigned'],
                ] as $f)
                <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] text-slate-400 font-medium mb-0.5">{{ $f['label'] }}</p>
                    <p class="font-semibold text-slate-800">{{ $f['value'] }}</p>
                </div>
                @endforeach
            </div>
            @if($user->fan_id_rejection_reason)
            <div class="mt-3 p-3 bg-red-50 rounded-xl text-xs text-red-700">
                <p class="font-semibold mb-0.5">Rejection Reason</p>
                {{ $user->fan_id_rejection_reason }}
            </div>
            @endif
        </div>
        @endif

        {{-- Bookings --}}
        <div class="bg-white rounded-2xl shadow-card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Bookings</h3>
                <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ $user->bookings->count() }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left px-5 py-2.5 text-slate-500 font-semibold">Ref</th>
                            <th class="text-left px-5 py-2.5 text-slate-500 font-semibold">Match</th>
                            <th class="text-left px-5 py-2.5 text-slate-500 font-semibold">Amount</th>
                            <th class="text-left px-5 py-2.5 text-slate-500 font-semibold">Status</th>
                            <th class="text-left px-5 py-2.5 text-slate-500 font-semibold">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($user->bookings as $booking)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-mono text-[11px]">
                                <a href="{{ route('admin.orders.show', $booking) }}" class="hover:underline" style="color:#008EC0">
                                    {{ $booking->caf_ticket_ref ?? '#' . $booking->id }}
                                </a>
                            </td>
                            <td class="px-5 py-3 text-slate-600 truncate max-w-[160px]">{{ $booking->match_name ?? '—' }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ number_format($booking->amount) }}</td>
                            <td class="px-5 py-3">
                                @php $sc = ['confirmed'=>'text-emerald-700 bg-emerald-50','pending'=>'text-amber-700 bg-amber-50','cancelled'=>'text-red-700 bg-red-50']; @endphp
                                <span class="px-2 py-0.5 rounded-full font-semibold text-[10px] {{ $sc[$booking->booking_status] ?? 'text-slate-600 bg-slate-100' }}">
                                    {{ ucfirst($booking->booking_status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-400">{{ $booking->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No bookings.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
