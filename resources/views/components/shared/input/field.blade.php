@props([
  'name',
  'label' => null,
  'required' => false,
])

<div class="space-y-2">
  @if($label)
    <x-shared.input.label for="{{ $name }}" :required="$required">
      {{ $label }}
    </x-shared.input.label>
  @endif

  {{ $slot }}

  <x-shared.input.error name="{{ $name }}" />
</div>
