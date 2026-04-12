@extends('layouts.app')

@section('title', __('rooms_qr.title'))

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('rooms_qr.title') }}</h1>
    <span class="text-sm text-gray-500">{{ $rooms->count() }} {{ __('rooms_qr.rooms') }}</span>
</div>

<p class="text-gray-500 text-sm mb-6">{{ __('rooms_qr.description') }}</p>

@if($rooms->isEmpty())
    <div class="bg-white rounded-xl shadow p-8 text-center text-gray-400">
        {{ __('rooms_qr.empty') }}
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($rooms as $room)
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="font-bold text-lg text-gray-800">{{ __('rooms_qr.room') }} {{ $room->room_no }}</div>
                <div class="text-xs text-gray-500 mb-2 h-4">{{ $room->room_desc }}</div>
                <div class="flex justify-center mb-3">
                    {!! $qrCodes[$room->room_no] !!}
                </div>
                <a href="{{ route('admin.rooms-qr.print', $room->room_no) }}" target="_blank"
                   class="inline-block text-indigo-600 hover:underline text-sm">
                    {{ __('rooms_qr.print') }}
                </a>
            </div>
        @endforeach
    </div>
@endif
@endsection
