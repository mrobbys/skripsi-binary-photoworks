<div>
  <h2 class="mb-8 font-serif text-3xl font-normal tracking-tight text-stone-900">
    Form Kontak
  </h2>

  <form
    x-on:submit.prevent="submit"
    class="space-y-5"
  >
    @csrf

    {{-- honeypot anti-bot start --}}
    <div
      class="absolute -z-10 opacity-0"
      aria-hidden="true"
    >
      <input
        type="text"
        name="website_url"
        tabindex="-1"
        autocomplete="off"
        x-model="state.form.website_url"
      >
    </div>
    {{-- honeypot anti-bot end --}}

    {{-- nama lengkap start --}}
    <x-shared.input.field
      name="nama"
      label="Nama Lengkap"
      :required="true"
    >
      <x-shared.input.text
        name="nama"
        placeholder="John Doe"
        :required="true"
        value="{{ old('nama') }}"
        x-model="state.form.nama"
        x-on:input="validateField('nama')"
        x-on:blur="validateField('nama')"
      />
    </x-shared.input.field>
    {{-- nama lengkap end --}}

    {{-- alamat email start --}}
    <x-shared.input.field
      name="email"
      label="Alamat Email"
      :required="true"
    >
      <x-shared.input.text
        name="email"
        type="email"
        placeholder="name@email.com"
        :required="true"
        value="{{ old('email') }}"
        x-model="state.form.email"
        x-on:input="validateField('email')"
        x-on:blur="validateField('email')"
      />
    </x-shared.input.field>
    {{-- alamat email end --}}

    {{-- subjek start --}}
    <x-shared.input.field
      name="subjek"
      label="Subjek"
      :required="true"
    >
      <x-shared.input.text
        name="subjek"
        placeholder="Kerjasama Photography"
        :required="true"
        value="{{ old('subjek') }}"
        x-model="state.form.subjek"
        x-on:input="validateField('subjek')"
        x-on:blur="validateField('subjek')"
      />
    </x-shared.input.field>
    {{-- subjek end --}}

    {{-- pesan start --}}
    <x-shared.input.field
      name="pesan"
      label="Pesan Anda"
      :required="true"
    >
      <x-shared.input.textarea
        name="pesan"
        rows="5"
        placeholder="Tuliskan pesan Anda di sini..."
        :required="true"
        x-model="state.form.pesan"
        x-on:input="validateField('pesan')"
        x-on:blur="validateField('pesan')"
      >{{ old('pesan') }}</x-shared.input.textarea>
    </x-shared.input.field>
    {{-- pesan end --}}

    {{-- submit btn start --}}
    <div class="pt-2">
      <x-shared.button
        type="submit"
        variant="primary"
        size="lg"
        class="w-full"
        loadingText="Mengirim..."
        xLoading="state.isLoading"
        x-bind:disabled="state.isLoading || !state.isFormValid"
      >
        Kirim Pesan
      </x-shared.button>
    </div>
    {{-- submit btn end --}}
  </form>
</div>
