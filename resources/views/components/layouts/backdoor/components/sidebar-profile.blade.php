{{--
   * COMPONENT SIDEBAR PROFILE
   * Menampilkan informasi profil pengguna di bagian bawah sidebar
--}}

@php
  $user = Auth::user();
  $name = $user->name ?? 'Guest';
  $role = $user ? ($user->getRoleNames()->first() ?? '-') : '-';
@endphp

<div class="mt-auto px-2 pt-4 border-t border-stone-600">
  <div class="flex items-center justify-between gap-3">
    
    {{-- avatar & info group start --}}
    <div class="flex items-center gap-3 min-w-0">
      {{-- avatar start --}}
      <div class="flex size-12 shrink-0 items-center justify-center border border-stone-500 bg-stone-800 text-stone-300">
        <i class="ri-user-line text-2xl"></i>
      </div>
      {{-- avatar end --}}

      {{-- user info start --}}
      <div class="flex flex-col min-w-0">
        <span class="truncate text-sm font-semibold text-stone-100">
          {{ $name }}
        </span>
        <span class="truncate text-[10px] font-bold tracking-wider text-stone-400 uppercase">
          {{ $role }}
        </span>
      </div>
      {{-- user info end --}}
    </div>
    {{-- avatar & info group end --}}

    {{-- logout button start --}}
    @auth
      <form method="POST" action="{{ route('logout') }}" class="shrink-0">
        @csrf
        <button
          type="submit"
          class="flex size-8 items-center justify-center text-stone-400 hover:text-stone-100 focus-visible:outline-2 focus-visible:outline-stone-300 transition-colors cursor-pointer"
          aria-label="Logout"
          title="Logout"
        >
          <i class="ri-logout-box-r-line text-xl"></i>
        </button>
      </form>
    @endauth
    {{-- logout button end --}}

  </div>
</div>
