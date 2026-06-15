@php
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '#'],
];
@endphp


<x-layouts.backdoor.index title="Dashboard" :breadcrumbs="$breadcrumbs">
    <x-slot:content>
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Gas Logout</button>
            </form>
        @endauth
    </x-slot:content>

</x-layouts.backdoor.index>
