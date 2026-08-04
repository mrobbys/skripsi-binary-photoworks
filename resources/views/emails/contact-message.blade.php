<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
  >
  <title>Pesan Baru - {{ $appName }}</title>
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
                Pesan Baru dari Formulir Kontak
              </h1>

              <p style="margin: 0 0 20px 0; font-size: 15px; line-height: 1.7; color: #57534E;">
                Anda menerima pesan baru melalui formulir kontak di situs <strong
                  style="color: #1C1917;">{{ $appName }}</strong>.
              </p>

              {{-- sender info table start --}}
              <table
                role="presentation"
                border="0"
                cellpadding="0"
                cellspacing="0"
                width="100%"
                style="margin: 24px 0; background-color: #EFEDEB; border-left: 3px solid #78716C; border-radius: 0px;"
              >
                <tr>
                  <td style="padding: 16px 20px; font-size: 14px; color: #57534E; line-height: 1.8;">
                    <div style="margin-bottom: 6px;">
                      <span
                        style="display: inline-block; width: 120px; color: #78716C; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;"
                      >Nama</span>
                      <strong style="color: #1C1917;">{{ $senderName }}</strong>
                    </div>
                    <div style="margin-bottom: 6px;">
                      <span
                        style="display: inline-block; width: 120px; color: #78716C; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;"
                      >Email</span>
                      <a
                        href="mailto:{{ $senderEmail }}"
                        style="color: #1C1917; text-decoration: underline;"
                      >{{ $senderEmail }}</a>
                    </div>
                    <div>
                      <span
                        style="display: inline-block; width: 120px; color: #78716C; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;"
                      >Subjek</span>
                      <strong style="color: #1C1917;">{{ $subject }}</strong>
                    </div>
                  </td>
                </tr>
              </table>
              {{-- sender info table end --}}

              {{-- message content start --}}
              <div style="margin-top: 28px;">
                <span
                  style="display: block; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #78716C; margin-bottom: 8px;"
                >
                  Isi Pesan:
                </span>
                <div
                  style="background-color: #FAFAF9; border: 1px solid #E7E5E4; border-radius: 0px; padding: 20px; font-size: 14px; line-height: 1.7; color: #1C1917; white-space: pre-wrap; word-break: break-word;"
                >{{ $pesan }}</div>
              </div>
              {{-- message content end --}}

              {{-- reply hint start --}}
              <div style="border-top: 1px solid #E7E5E4; padding-top: 20px; margin-top: 28px;">
                <p style="margin: 0; font-size: 12px; color: #A8A29E; line-height: 1.5;">
                  Catatan: Anda dapat langsung mengklik &ldquo;Balas&rdquo; (Reply) pada email ini untuk merespons
                  pengirim secara langsung ke <strong style="color: #78716C;">{{ $senderEmail }}</strong>.
                </p>
              </div>
              {{-- reply hint end --}}

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
