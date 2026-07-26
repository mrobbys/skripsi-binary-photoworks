<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
  >
  <title>Permintaan Atur Ulang Kata Sandi - {{ $appName }}</title>
</head>

<body
  style="margin: 0; padding: 0; background-color: #FAFAF9; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1C1917; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%;"
>

  <table
    role="presentation"
    border="0"
    cellpadding="0"
    cellspacing="0"
    width="100%"
    style="background-color: #FAFAF9; padding: 40px 16px;"
  >
    <tr>
      <td align="center">
        {{-- main card container start --}}
        <table
          role="presentation"
          border="0"
          cellpadding="0"
          cellspacing="0"
          width="100%"
          style="max-width: 580px; background-color: #F5F5F4; border: 1px solid #D6D3D1; border-radius: 0px;"
        >

          {{-- header start --}}
          <tr>
            <td style="padding: 32px 36px 20px 36px; border-bottom: 1px solid #E7E5E4; text-align: left;">
              <span
                style="font-family: 'Libre Baskerville', Georgia, serif; font-size: 18px; font-weight: 700; color: #1C1917; letter-spacing: 0.05em; text-transform: uppercase;"
              >
                {{ $appName }}
              </span>
            </td>
          </tr>
          {{-- header end --}}

          {{-- body content start --}}
          <tr>
            <td style="padding: 36px;">
              <h1
                style="margin: 0 0 16px 0; font-family: 'Libre Baskerville', Georgia, serif; font-size: 22px; font-weight: 700; color: #1C1917; line-height: 1.4;"
              >
                Halo, {{ $userName }}!
              </h1>

              <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 1.7; color: #57534E;">
                Kami menerima permintaan untuk mengatur ulang kata sandi akun <strong
                  style="color: #1C1917;">{{ $appName }}</strong> Anda. Silakan klik tombol di bawah ini untuk
                membuat kata sandi baru.
              </p>

              {{-- expired time start --}}
              <table
                role="presentation"
                border="0"
                cellpadding="0"
                cellspacing="0"
                width="100%"
                style="margin: 24px 0; background-color: #EFEDEB; border-left: 3px solid #78716C; border-radius: 0px;"
              >
                <tr>
                  <td style="padding: 14px 18px; font-size: 14px; color: #57534E; line-height: 1.5;">
                    ⏱️ Tautan ini berlaku selama <strong style="color: #1C1917;">{{ $expiresIn }} menit</strong> demi
                    keamanan akun Anda.
                  </td>
                </tr>
              </table>
              {{-- expired time end --}}

              {{-- action button start --}}
              <table
                role="presentation"
                border="0"
                cellpadding="0"
                cellspacing="0"
                style="margin: 32px 0 28px 0;"
              >
                <tr>
                  <td
                    align="left"
                    style="background-color: #78716C; border-radius: 0px;"
                  >
                    <a
                      href="{{ $resetUrl }}"
                      target="_blank"
                      style="display: inline-block; padding: 14px 28px; background-color: #78716C; color: #FAFAF9; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 0px; border: 1px solid #78716C; text-align: center;"
                    >
                      Atur Ulang Kata Sandi
                    </a>
                  </td>
                </tr>
              </table>
              {{-- action button end --}}

              <p style="margin: 0 0 24px 0; font-size: 14px; line-height: 1.6; color: #78716C;">
                Jika Anda tidak merasa meminta perubahan kata sandi, abaikan email ini dan kata sandi Anda akan tetap
                aman.
              </p>

              {{-- url fallback start --}}
              <div style="border-top: 1px solid #E7E5E4; padding-top: 20px; margin-top: 28px;">
                <p style="margin: 0 0 8px 0; font-size: 12px; color: #A8A29E; line-height: 1.5;">
                  Mengalami masalah saat mengklik tombol? Salin dan tempel tautan berikut ke browser Anda:
                </p>
                <p style="margin: 0; font-size: 12px; word-break: break-all;">
                  <a
                    href="{{ $resetUrl }}"
                    style="color: #78716C; text-decoration: underline;"
                  >{{ $resetUrl }}</a>
                </p>
              </div>
              {{-- url fallback end --}}

            </td>
          </tr>
          {{-- body content end --}}

        </table>
        {{-- main card container end --}}

        {{-- footer start --}}
        <table
          role="presentation"
          border="0"
          cellpadding="0"
          cellspacing="0"
          width="100%"
          style="max-width: 580px; margin-top: 24px;"
        >
          <tr>
            <td style="text-align: center; font-size: 12px; color: #A8A29E; line-height: 1.5;">
              &copy; {{ date('Y') }} {{ $appName }}. Hak Cipta Dilindungi.
            </td>
          </tr>
        </table>
        {{-- footer end --}}

      </td>
    </tr>
  </table>

</body>

</html>
