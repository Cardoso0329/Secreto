<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Novo Recado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            text-align: left;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        h1 {
            color: #1d4ed8;
            margin-bottom: 25px;
            text-align: center;
        }
        p {
            margin: 8px 0;
        }
        .label {
            font-weight: bold;
            color: #374151;
        }
        .value {
            color: #111827;
        }
        .button {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 22px;
            background-color: #2563eb;
            color: #ffffff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        .footer {
            margin-top: 35px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">

        <h1>📩 Novo Recado Criado</h1>

        <p>
            <span class="label">ID:</span>
            <span class="value">{{ $recado->id }}</span>
        </p>

        <p>
            <span class="label">Nome:</span>
            <span class="value">{{ $recado->name }}</span>
        </p>

        <p>
            <span class="label">Contacto:</span>
            <span class="value">{{ $recado->contact_client }}</span>
        </p>

        @if(!empty($recado->matricula))
            <p>
                <span class="label">Matrícula:</span>
                <span class="value">{{ $recado->matricula }}</span>
            </p>
        @endif

        <p>
            <span class="label">Assunto:</span><br>
            <span class="value">{{ $recado->assunto }}</span>
        </p>

        <p>
            <span class="label">Tipo:</span><br>
            <span class="value">{{ $recado->tipo }}</span>
        </p>


        @if(isset($recado->sla))
            <p>
                <span class="label">SLA:</span>
                <span class="value">{{ $recado->sla->name }}</span>
            </p>
        @endif

        <p>
            <span class="label">Data:</span>
            <span class="value">{{ $recado->created_at->format('d/m/Y H:i') }}</span>
        </p>

        @if($guestUrl)
            <div style="text-align:center;">
                <a href="{{ $guestUrl }}" class="button">
                    🔍 Ver Recado
                </a>
            </div>
        @else
            <div style="text-align:center;">
                <a href="{{ route('recados.show', $recado->id) }}" class="button">
                    🔍 Ver Recado
                </a>
            </div>
        @endif

        <div class="footer">
            Este é um email automático enviado pelo <strong>Sistema Secreto</strong>.<br>
            Por favor, não responda a este email.
        </div>

    </div>
</body>
</html>
