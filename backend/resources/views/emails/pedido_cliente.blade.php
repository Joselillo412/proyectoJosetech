<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            padding: 20px;
        }

        .card {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        }

        .header {
            background: #d96a1a;
            padding: 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
        }

        .body {
            padding: 30px;
            line-height: 1.6;
        }

        .code-box {
            background: #f1f5f9;
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }

        .code {
            font-size: 28px;
            font-weight: 900;
            color: #d96a1a;
            letter-spacing: 2px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="header">
            <h1>¡Orden Recibida con Éxito!</h1>
        </div>
        <div class="body">
            <p>Hola <strong>{{ $pedido->usuario->nombre }}</strong>,</p>
            <p>Hemos registrado correctamente tu solicitud de reparación en nuestro sistema. Un mensajero pasará a recoger tu equipo lo antes posible.</p>

            <div class="code-box">
                <span style="font-size: 13px; color: #64748b; display: block; margin-bottom: 5px;">Tu Código de Seguimiento en tiempo real:</span>
                <div class="code">{{ $pedido->codigo_seguimiento }}</div>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #64748b;">Puedes usarlo en la sección "Consulta" de nuestra web.</p>
            </div>

            <p><strong>Detalles del Presupuesto Estimado:</strong></p>
            <ul>
                <li><strong>Dispositivo:</strong> {{ $pedido->dispositivo->marca }} {{ $pedido->dispositivo->modelo }}</li>
                <li><strong>Intervenciones:</strong> {{ $pedido->tipo_reparacion }}</li>
                <li><strong>Precio Estimado:</strong> {{ $pedido->precio_estimado }}</li>
            </ul>

            <p>Si tienes alguna duda o quieres aportar más detalles, puedes responder directamente a este correo.</p>
            <p>Atentamente,<br><strong>El equipo de Josetech</strong></p>
        </div>
        <div class="footer">
            Este es un correo automático, por favor no respondas si no es necesario. © {{ date('Y') }} Josetech.
        </div>
    </div>
</body>

</html>