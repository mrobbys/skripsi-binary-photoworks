<div {{ $attributes->merge(['class' => 'mb-6 flex justify-between']) }}>
  <div>
    @if (isset($left))
      <table class="text-sm">
        {{ $left }}
      </table>
    @endif
  </div>

  <div>
    @if (isset($right))
      <table class="ml-auto text-sm">
        {{ $right }}
      </table>
    @endif
  </div>
</div>
