@props(['text' => 'Tambah'])

<x-shared.button
  variant="charcoal"
  size="md"
  :value="$text"
  {{ $attributes }}
>
  <x-slot:iconLeft>
    <i class="ri-add-line leading-none" aria-hidden="true"></i>
  </x-slot:iconLeft>
</x-shared.button>
