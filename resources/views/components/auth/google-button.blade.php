@props([
  'text' => 'Masuk dengan Google',
  'href' => route('auth.google'),
])

<x-shared.button
  as="a"
  :href="$href"
  variant="outline"
  class="w-full py-3"
>
  <x-slot:iconLeft>
    <i class="ri-google-fill text-lg"></i>
  </x-slot:iconLeft>
  {{ $text }}
</x-shared.button>
