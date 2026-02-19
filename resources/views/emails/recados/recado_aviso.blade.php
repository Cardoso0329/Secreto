<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Aviso de Recado</title>
</head>
<body style="margin:0;padding:0;background:#f8f9fa;font-family:Arial,Helvetica,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;padding:24px 0;">
    <tr>
      <td align="center">

        <!-- Container -->
        <table role="presentation" width="600" cellpadding="0" cellspacing="0"
               style="width:600px;max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);">

          <!-- Header -->
          <tr>
            <td style="padding:18px 22px;background:#0d6efd;color:#ffffff;">
              <div style="font-size:18px;font-weight:700;line-height:1.2;">
                Aviso de Recado
              </div>
              <div style="font-size:12px;opacity:.9;margin-top:4px;">
                Notificação automática do Sistema Secreto
              </div>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:18px 22px;">

              <!-- Badge -->
              <div style="margin-bottom:14px;">
                <span style="display:inline-block;padding:6px 10px;border-radius:999px;background:#fff3cd;color:#664d03;border:1px solid #ffecb5;font-size:12px;font-weight:700;">
                  Aviso · {{ $aviso->name }}
                </span>
                <span style="display:inline-block;margin-left:8px;color:#6c757d;font-size:12px;">
                  Recado #{{ $recado->id }} · {{ $recado->created_at->format('d/m/Y H:i') }}
                </span>
              </div>

              <div style="font-size:13px;color:#212529;margin-bottom:14px;">
                Recebeste um aviso associado ao seguinte recado:
              </div>

              <!-- Título secção -->
              <div style="font-size:12px;font-weight:700;color:#212529;text-transform:uppercase;letter-spacing:.4px;margin:14px 0 8px;">
                Dados do recado
              </div>

              @php
                $destinatarios = collect();

                if ($recado->departamento) {
                  $destinatarios->push('Para: ' . $recado->departamento->name);
                }

                if ($recado->chefia) {
                  $destinatarios->push('Cc: ' . $recado->chefia->name);
                }

                if ($recado->destinatarios && $recado->destinatarios->count()) {
                  foreach ($recado->destinatarios as $user) {
                    $destinatarios->push('Utilizador: ' . $user->name);
                  }
                }
              @endphp

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                  <td style="padding:8px 0;color:#6c757d;font-size:13px;width:160px;font-weight:700;">Assunto</td>
                  <td style="padding:8px 0;color:#212529;font-size:13px;">
                    {{ $recado->assunto }}
                  </td>
                </tr>

                @if(!empty($recado->mensagem))
                  <tr>
                    <td style="padding:8px 0;color:#6c757d;font-size:13px;width:160px;font-weight:700;vertical-align:top;">
                      Mensagem
                    </td>
                    <td style="padding:8px 0;color:#212529;font-size:13px;line-height:1.5;">
                      {{ $recado->mensagem }}
                    </td>
                  </tr>
                @endif

                @if(isset($recado->setor))
                  <tr>
                    <td style="padding:8px 0;color:#6c757d;font-size:13px;width:160px;font-weight:700;">Setor</td>
                    <td style="padding:8px 0;color:#212529;font-size:13px;">
                      {{ $recado->setor->name }}
                    </td>
                  </tr>
                @endif

                @if(isset($recado->sla))
                  <tr>
                    <td style="padding:8px 0;color:#6c757d;font-size:13px;width:160px;font-weight:700;">SLA</td>
                    <td style="padding:8px 0;color:#212529;font-size:13px;">
                      {{ $recado->sla->name }}
                    </td>
                  </tr>
                @endif

                <tr>
                  <td style="padding:8px 0;color:#6c757d;font-size:13px;width:160px;font-weight:700;">
                    Criado por
                  </td>
                  <td style="padding:8px 0;color:#212529;font-size:13px;">
                    {{ $recado->user->name ?? 'Sistema' }}
                  </td>
                </tr>

                {{-- ✅ Destinatários (dentro da tabela, em TR) --}}
                @if($destinatarios->count())
                  <tr>
                    <td colspan="2" style="padding:0;">
                      <div style="height:1px;background:#e9ecef;margin:14px 0;"></div>

                      <div style="font-size:12px;font-weight:700;color:#212529;text-transform:uppercase;letter-spacing:.4px;margin:0 0 8px;">
                        Destinatário(s)
                      </div>

                      <div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:10px;padding:12px 14px;color:#212529;font-size:13px;line-height:1.6;">
                        {!! implode('<br>', $destinatarios->toArray()) !!}
                      </div>
                    </td>
                  </tr>
                @endif
              </table>

              <!-- Botão -->
              <div style="text-align:center;margin:18px 0 6px;">
                @if($guestUrl ?? false)
                  <a href="{{ $guestUrl }}"
                     style="display:inline-block;background:#0d6efd;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:10px;font-weight:700;font-size:14px;">
                    🔍 Ver Recado (Acesso Convidado)
                  </a>
                @else
                  <a href="{{ url('/painel?url=' . route('recados.show', $recado->id)) }}"
                     style="display:inline-block;background:#0d6efd;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:10px;font-weight:700;font-size:14px;">
                    🔍 Ver Recado
                  </a>
                @endif
              </div>

              <div style="color:#6c757d;font-size:12px;text-align:center;margin-top:12px;">
                Este aviso foi enviado automaticamente.
              </div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:14px 22px;background:#ffffff;border-top:1px solid #e9ecef;">
              <div style="font-size:12px;color:#6c757d;text-align:center;line-height:1.4;">
                Este é um email automático enviado pelo <strong>Sistema Secreto</strong>.<br>
                Por favor, não responda a este email.
              </div>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>
</body>
</html>
