@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">Notifications</h1>
        <p class="mt-1 text-sm text-slate-600">In-app ticket updates. Notification summaries do not contain ticket subjects, replies, or internal-note text.</p>
    </div>

    <ul class="space-y-3">
        @forelse ($notifications as $notification)
            <li class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-medium">{{ $notification->data['message'] ?? 'A ticket update is available.' }}</p>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ $notification->created_at?->timezone(config('app.timezone'))->toDayDateTimeString() }}
                        @if ($notification->read_at)
                            <span>· Read</span>
                        @else
                            <span class="font-medium text-blue-800">· Unread</span>
                        @endif
                    </p>
                </div>
                @if ($notification->ticket_is_viewable)
                    <form method="POST" action="{{ route('notifications.open', $notification->id) }}">
                        @csrf
                        <button type="submit" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50">Open ticket</button>
                    </form>
                @else
                    <p class="text-sm text-slate-600">This ticket is not available under your current access.</p>
                @endif
            </li>
        @empty
            <li class="rounded-xl border border-slate-200 bg-white px-4 py-8 text-center text-slate-600">You have no notifications.</li>
        @endforelse
    </ul>

    {{ $notifications->links() }}
</div>
@endsection
