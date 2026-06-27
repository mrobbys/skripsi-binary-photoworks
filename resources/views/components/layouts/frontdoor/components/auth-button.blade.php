@auth
  <x-shared.button as="a" href="{{ route('frontdoor.dashboard.index') }}" 
    variant="{{ request()->routeIs('frontdoor.dashboard.*') ? 'primary' : 'outline' }}"
    {{ $attributes }}>
    Dashboard
  </x-shared.button>
@else
  <x-shared.button as="a" href="{{ route('login') }}" variant="outline"
    {{ $attributes }}>
    Masuk
  </x-shared.button>
@endauth
