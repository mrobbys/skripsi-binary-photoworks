@component('mail::message')
  # Halo, {{ $userName }}!

  Kami menerima permintaan untuk mengatur ulang kata sandi akun **{{ $appName }}** Anda.

  Klik tombol di bawah ini untuk membuat kata sandi baru. Tautan ini hanya berlaku selama **{{ $expiresIn }} menit**.

  @component('mail::button', ['url' => $resetUrl, 'color' => 'primary'])
    Atur Ulang Kata Sandi
  @endcomponent

  Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini dan kata sandi Anda tidak akan berubah.

  Salam,
  **{{ $appName }}**
@endcomponent
