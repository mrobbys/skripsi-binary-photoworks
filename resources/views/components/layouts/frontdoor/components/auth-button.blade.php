@auth
  <x-shared.button as="a" href="{{ route('frontdoor.dashboard.index') }}" 
    class="{{ request()->routeIs('frontdoor.dashboard.*') 
      ? 'bg-stone-800 text-white hover:bg-stone-700' 
      : 'border border-stone-300 bg-transparent text-stone-800 hover:bg-stone-50' }}"
    {{ $attributes }}>
    Dashboard
  </x-shared.button>
@else
  <x-shared.button as="a" href="{{ route('login') }}" 
    class="border border-stone-300 bg-transparent text-stone-800 hover:bg-stone-50"
    {{ $attributes }}>
    Masuk
  </x-shared.button>
@endauth
