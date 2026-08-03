<div class="hidden sm:block">
  @auth
    <x-shared.button
      as="a"
      href="{{ route('frontdoor.dashboard.index') }}"
      :variant="request()->routeIs('frontdoor.dashboard.*') ? 'dark' : 'outline'"
      value="Dashboard"
    />
  @else
    <x-shared.button
      as="a"
      href="{{ route('login') }}"
      variant="outline"
      value="Masuk"
    />
  @endauth
</div>
