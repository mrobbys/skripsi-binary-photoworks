@props(['teams' => []])

<section
  aria-labelledby="team-heading"
  class="border-t border-stone-200 py-16"
>

  <h2
    id="team-heading"
    class="mb-12 text-center font-serif text-3xl font-normal tracking-tight text-stone-900"
  >
    Tim Kami
  </h2>

  <div class="flex flex-wrap justify-center gap-6">
    @foreach ($teams as $team)
      <x-frontdoor.about.team-card :team="$team" />
    @endforeach
  </div>

</section>
