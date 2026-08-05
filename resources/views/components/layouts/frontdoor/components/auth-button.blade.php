<div>
  @auth
    <x-shared.button
      as="a"
      href="{{ route('frontdoor.dashboard.index') }}"
      :variant="request()->routeIs('frontdoor.dashboard.*') ? 'dark' : 'outline'"
      value="Dashboard"
      class="w-full"
    />
  @else
    <x-shared.button
      as="a"
      href="{{ route('login') }}"
      variant="outline"
      value="Masuk"
      class="w-full"
    />
  @endauth
</div>
