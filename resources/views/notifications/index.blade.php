@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Notifications</h1>
        <form action="{{ route('notifications.readAll') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center rounded-md bg-3hcig-blue px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-3hcig-blue-light focus:outline-none focus:ring-2 focus:ring-3hcig-blue focus:ring-offset-2">
                Marquer tout comme lu
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <ul role="list" class="divide-y divide-gray-200">
            @forelse($notifications as $notification)
                <li class="px-4 py-5 sm:px-6 {{ $notification->read_at ? 'bg-gray-50' : 'bg-white' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                @if(isset($notification->data['type']) && $notification->data['type'] === 'absence')
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                @elseif(isset($notification->data['type']) && $notification->data['type'] === 'retard')
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-yellow-100">
                                        <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-3hcig-blue-light/20">
                                        <svg class="h-6 w-6 text-3hcig-blue" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">{{ isset($notification->data['message']) ? $notification->data['message'] : 'Notification' }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($notification->created_at)->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @if(!$notification->read_at)
                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-md bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    Marquer comme lu
                                </button>
                            </form>
                        @else
                            <span class="inline-flex items-center rounded-md bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-500">
                                Lu
                            </span>
                        @endif
                    </div>
                </li>
            @empty
                <li class="px-4 py-10 sm:px-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune notification</h3>
                    <p class="mt-1 text-sm text-gray-500">Vous n'avez aucune notification pour le moment.</p>
                </li>
            @endforelse
        </ul>

        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
