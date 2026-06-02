<x-layouts.auth>
    <x-slot:content>
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Gas Logout</button>
            </form>
        @endauth
    </x-slot:content>

</x-layouts.auth>
