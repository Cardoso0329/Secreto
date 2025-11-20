<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f3f4f6; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 40px auto; background-color: #fff; padding: 30px; border-radius: 12px; text-align: center; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        h1 { color: #1d4ed8; }
        .label { font-weight: bold; color: #374151; }
        .value { color: #111827; }
        .button { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #2563eb; color: white; border-radius: 6px; text-decoration: none; }
        .footer { margin-top: 30px; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="email-container">
        <h1>📩 Novo Recado Criado</h1>

        <p><span class="label">ID:</span> <span class="value">{{ $recado->id }}</span></p>
        <p><span class="label">Nome:</span> <span class="value">{{ $recado->name }}</span></p>
        <p><span class="label">Contato:</span> <span class="value">{{ $recado->contact_client }}</span></p>

        @if(!empty($recado->email))
            <p><span class="label">Email:</span> <span class="value">{{ $recado->email }}</span></p>
        @endif

        <p><span class="label">Assunto:</span><br><span class="value">{{ $recado->assunto }}</span></p>

        @if(isset($recado->setor))
            <p><span class="label">Setor:</span> <span class="value">{{ $recado->setor->name }}</span></p>
        @endif

        @if(isset($recado->sla))
            <p><span class="label">SLA:</span> <span class="value">{{ $recado->sla->name }} </span></p>
        @endif

        <p><span class="label">Criado por:</span> <span class="value">{{ $recado->user->name ?? 'Sistema' }}</span></p>
        <p><span class="label">Data:</span> <span class="value">{{ $recado->created_at->format('d/m/Y H:i') }}</span></p>

        @if($guestUrl)
            <a href="{{ $guestUrl }}" class="button">🔍 Ver Recado (Acesso Convidado)</a>
        @else
            <a href="{{ url('/painel?url=' . route('recados.show', $recado->id)) }}" class="button">🔍 Ver Recado</a>
        @endif

        <div class="footer">
            Este é um email automático enviado por <strong>Sistema Secreto</strong>.<br>
            Por favor, não responda a este email.
        </div>
    </div>
</body>
</html>
