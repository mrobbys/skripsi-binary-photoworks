@props([
    'printDate',
    'role' => 'Owner',
    'city' => 'Banjarbaru',
])

<div class="flex justify-end">
  <div class="text-center">
    <p>{{ $city }}, {{ $printDate }}</p>
    <div class="h-20"></div>
    <p class="font-bold uppercase tracking-wider text-stone-900">{{ $role }}</p>
  </div>
</div>
