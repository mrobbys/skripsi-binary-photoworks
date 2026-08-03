@props([
  'text' => 'Belum punya akun?',
  'linkText' => 'Daftar',
  'href' => route('register'),
])

<div class="mt-8 text-center text-xs text-stone-600">
  <span>{{ $text }} </span>
  <a
    href="{{ $href }}"
    class="font-bold text-stone-900 underline hover:text-stone-700"
  >
    {{ $linkText }}
  </a>
</div>
