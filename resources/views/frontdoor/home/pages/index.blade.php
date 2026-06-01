@auth
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Gas Logout</button>
    </form>
@endauth
