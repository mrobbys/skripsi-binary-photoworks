{{-- 
  * COMPONENT TABLE HEADER
  * Header untuk table dengan 2 slot (left dan right)
  
  * Slot :
      * left : elemen tambahan pada bagian pojok kiri (misal judul atau search bar)
      * right : elemen tambahan pada bagian pojok kanan (button tambah)
--}}

<div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
  {{-- Bagian Kiri --}}
  <div class="w-full sm:w-auto flex-1">
    {{ $left ?? '' }}
  </div>

  {{-- Bagian Kanan --}}
  <div class="w-full sm:w-auto flex justify-end">
    {{ $right ?? '' }}
  </div>
</div>
