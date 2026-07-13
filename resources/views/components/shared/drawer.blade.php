{{--
  * COMPONENT: SHARED DRAWER
  * Slide-over panel generik dari kanan layar.
  *
  * Props:
  *   - openState      : Alpine expression — kondisi buka/tutup
  *   - closeAction    : Alpine expression — fungsi menutup drawer
  *   - title          : string — judul header
  *   - maxWidth       : string — Tailwind class lebar max panel (default: max-w-lg)
  *   - ariaLabelledBy : string — ID untuk aria-labelledby
  *   - formAction     : Submit Form
  *
  * Slots:
  *   - default  : isi body (scrollable)
  *   - footer   : tombol aksi (di sticky footer)
--}}

@props([
    'openState' => 'false',
    'closeAction' => '',
    'title' => 'Drawer',
    'maxWidth' => 'max-w-lg',
    'ariaLabelledBy' => 'drawer-title',
    'formAction' => null,
])

<div
  x-show="{{ $openState }}"
  x-on:keydown.escape.window="{{ $closeAction }}"
  class="relative z-50"
  x-init="$watch('{{ $openState }}', val => document.body.style.overflow = val ? 'hidden' : '');
  $cleanup(() => document.body.style.overflow = '');"
  x-cloak>

  {{-- backdrop start --}}
  <div
    x-show="{{ $openState }}"
    x-transition.opacity.duration.600ms
    x-on:click="{{ $closeAction }}"
    class="fixed inset-0 bg-stone-900/50"
    aria-hidden="true">
  </div>
  {{-- backdrop end --}}

  <div class="overflow-hidden fixed inset-0 pointer-events-none">
    <div class="overflow-hidden absolute inset-0">
      <div class="flex fixed inset-y-0 right-0 pl-10 max-w-full">

        {{-- sliding panel start --}}
        <div
          x-show="{{ $openState }}"
          role="dialog"
          aria-modal="true"
          aria-labelledby="{{ $ariaLabelledBy }}"
          x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:enter-start="translate-x-full"
          x-transition:enter-end="translate-x-0"
          x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
          x-transition:leave-start="translate-x-0"
          x-transition:leave-end="translate-x-full"
          class="w-screen {{ $maxWidth }} pointer-events-auto">

          <{{ $formAction ? 'form' : 'div' }}
            @if ($formAction) x-on:submit.prevent="{{ $formAction }}" @endif
            class="flex flex-col h-full bg-stone-50 border-l border-stone-200 overflow-hidden">

            {{-- header start --}}
            <div class="p-4 border-b-2 border-stone-300 bg-stone-200 flex justify-between items-center shrink-0">
              <h2
                class="text-xl font-bold text-stone-900"
                id="{{ $ariaLabelledBy }}">
                {{ $title }}
              </h2>
              <button
                x-on:click="{{ $closeAction }}"
                type="button"
                aria-label="Tutup drawer"
                class="flex items-center px-3 py-1.5 text-stone-600 transition active:scale-[0.97] cursor-pointer hover:text-stone-900">
                <i class="ri-close-line text-2xl" aria-hidden="true"></i>
              </button>
            </div>
            {{-- header end --}}

            {{-- body start --}}
            <div class="flex-1 overflow-y-auto overscroll-contain pt-6 pb-20 px-6">
              {{ $slot }}
            </div>
            {{-- body end --}}

            {{-- footer start --}}
            @if (isset($footer))
              <div class="p-4 border-t border-stone-200 bg-stone-100 flex justify-end items-center gap-6 shrink-0">
                {{ $footer }}
              </div>
            @endif
            {{-- footer end --}}

            </{{ $formAction ? 'form' : 'div' }}>
        </div>
        {{-- sliding panel end --}}

      </div>
    </div>
  </div>
</div>
