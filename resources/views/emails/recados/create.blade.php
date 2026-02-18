<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Novo Recado</title>
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
                Novo Recado Criado
              </div>
              <div style="font-size:12px;opacity:.9;margin-top:4px;">
                Registo automático do Sistema Secreto
              </div>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:18px 22px;">

              <!-- Badge -->
              <div style="margin-bottom:14px;">

                {{-- ✅ Tipo de Formulário (antes do Recado #) --}}
                @if($recado->tipoFormulario)
                  <span style="display:inline-block;padding:6px 10px;border-radius:999px;background:#e9ecef;color:#212529;border:1px solid #dee2e6;font-size:12px;font-weight:700;">
                    {{ $recado->tipoFormulario->name }}
                  </span>
                @endif

                <span style="display:inline-block;padding:6px 10px;border-radius:999px;background:#e7f1ff;color:#0d6efd;border:1px solid #cfe2ff;font-size:12px;font-weight:700; margin-left:8px;">
                  Recado #{{ $recado->id }}
                </span>

                <span style="display:inline-block;margin-left:8px;color:#6c757d;font-size:12px;">
                  {{ optional($recado->created_at)->format('d/m/Y H:i') }}
                </span>
              </div>

              <!-- Dados -->
              <div style="font-size:12px;font-weight:700;color:#212529;text-transform:uppercase;letter-spacing:.4px;margin:14px 0 8px;">
                Dados do recado
              </div>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <tr>
                  <td style="padding:8px 0;color:#6c757d;font-size:13px;width:160px;font-weight:700;">Nome</td>
                  <td style="padding:8px 0;color:#212529;font-size:13px;">{{ $recado->name }}</td>
                </tr>
                <tr>
                  <td style="padding:8px 0;color:#6c757d;font-size:13px;width:160px;font-weight:700;">Contacto</td>
                  <td style="padding:8px 0;color:#212529;font-size:13px;">{{ $recado->contact_client }}</td>
                </tr>

                @if(!empty($recado->plate))
                  <tr>
                    <td style="padding:8px 0;color:#6c757d;font-size:13px;width:160px;font-weight:700;">Matrícula</td>
                    <td style="padding:8px 0;color:#212529;font-size:13px;">{{ $recado->plate }}</td>
                  </tr>
                @endif

                @if(!empty($recado->operator_email))
                  <tr>
                    <td style="padding:8px 0;color:#6c757d;font-size:13px;width:160px;font-weight:700;">Email operador</td>
                    <td style="padding:8px 0;color:#212529;font-size:13px;">{{ $recado->operator_email }}</td>
                  </tr>
                @endif
              </table>

              <!-- Classificação -->
              <div style="height:1px;background:#e9ecef;margin:14px 0;"></div>
              <div style="font-size:12px;font-weight:700;color:#212529;text-transform:uppercase;letter-spacing:.4px;margin:0 0 8px;">
                Classificação
              </div>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                @if($recado->tipo)
                  <tr>
                    <td style="padding:8px 0;color:#6c757d;font-size:13px;width:160px;font-weight:700;">Tipo</td>
                    <td style="padding:8px 0;color:#212529;font-size:13px;">{{ $recado->tipo->name }}</td>
                  </tr>
                @endif
                @if($recado->sla)
                  <tr>
                    <td style="padding:8px 0;color:#6c757d;font-size:13px;width:160px;font-weight:700;">SLA</td>
                    <td style="padding:8px 0;color:#212529;font-size:13px;">{{ $recado->sla->name }}</td>
                  </tr>
                @endif
              </table>

              <!-- Assunto -->
              @if(!empty($recado->assunto))
                <div style="height:1px;background:#e9ecef;margin:14px 0;"></div>
                <div style="font-size:12px;font-weight:700;color:#212529;text-transform:uppercase;letter-spacing:.4px;margin:0 0 8px;">
                  Assunto
                </div>
                <div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:10px;padding:12px 14px;color:#212529;font-size:13px;line-height:1.5;">
                  {{ $recado->assunto }}
                </div>
              @endif

              <!-- Destinatários -->
              @php
                $destinatarios = collect();

                if ($recado->departamento) {
                  $destinatarios->push('Para: ' . $recado->departamento->name);
                }

                if ($recado->destinatarios && $recado->destinatarios->count()) {
                  foreach ($recado->destinatarios as $user) {
                    $destinatarios->push('Utilizador: ' . $user->name);
                  }
                }

                if ($recado->chefia) {
                  $destinatarios->push('Cc: ' . $recado->chefia->name);
                }
              @endphp

              @if($destinatarios->count())
                <div style="height:1px;background:#e9ecef;margin:14px 0;"></div>
                <div style="font-size:12px;font-weight:700;color:#212529;text-transform:uppercase;letter-spacing:.4px;margin:0 0 8px;">
                  Destinatário(s)
                </div>
                <div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:10px;padding:12px 14px;color:#212529;font-size:13px;line-height:1.6;">
                  {!! implode('<br>', $destinatarios->toArray()) !!}
                </div>
              @endif

              <!-- ✅ Mensagem (agora DEPOIS dos destinatários) -->
              @if(!empty($recado->mensagem))
                <div style="height:1px;background:#e9ecef;margin:14px 0;"></div>
                <div style="font-size:12px;font-weight:700;color:#212529;text-transform:uppercase;letter-spacing:.4px;margin:0 0 8px;">
                  Mensagem
                </div>
                <div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:10px;padding:12px 14px;color:#212529;font-size:13px;line-height:1.6;">
                  {!! nl2br(e($recado->mensagem)) !!}
                </div>
              @endif

              <!-- Botão -->
              <div style="text-align:center;margin:18px 0 6px;">
                @if(!empty($guestUrl))
                  <a href="{{ $guestUrl }}"
                     style="display:inline-block;background:#0d6efd;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:10px;font-weight:700;font-size:14px;">
                    Ver Recado
                  </a>
                @else
                  <a href="{{ route('recados.show', $recado->id) }}"
                     style="display:inline-block;background:#0d6efd;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:10px;font-weight:700;font-size:14px;">
                    Ver Recado
                  </a>
                @endif
              </div>

              <div style="color:#6c757d;font-size:12px;text-align:center;margin-top:12px;">
                Se não reconhece este pedido, ignore esta mensagem.
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
