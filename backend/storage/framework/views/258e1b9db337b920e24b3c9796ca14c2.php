<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background-color: #f1f5f9;
            color: #334155;
            line-height: 1.6;
        }

        .box {
            max-width: 550px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        h2 {
            color: #0f172a;
            margin-top: 0;
            font-size: 22px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 15px;
            text-align: center;
        }

        .alert {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: 800;
            color: #1d4ed8;
            margin-bottom: 25px;
            font-size: 18px;
            letter-spacing: 1px;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            padding: 12px 0;
            border-bottom: 1px solid #f8fafc;
            font-size: 15px;
        }

        li:last-child {
            border-bottom: none;
        }

        strong {
            color: #0f172a;
            display: inline-block;
            width: 110px;
        }

        .footer-text {
            margin-top: 30px;
            font-size: 13px;
            color: #64748b;
            text-align: center;
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
        }
    </style>
</head>

<body>
    <div class="box">
        <h2>Nueva Orden de Reparación</h2>

        <div class="alert">
            CÓDIGO: <?php echo e($pedido->codigo_seguimiento); ?>

        </div>

        <p style="color: #475569; font-size: 15px; font-weight: bold; margin-bottom: 10px;">Detalles de la solicitud:</p>

        <ul>
            <li>
                <strong>Cliente:</strong>
                <span style="color: #334155;"><?php echo e($pedido->usuario->nombre); ?></span> <br>
                <span style="color: #94a3b8; font-size: 13px; margin-left: 115px;"><?php echo e($pedido->usuario->email); ?></span>
            </li>
            <li>
                <strong>Equipo:</strong>
                <span style="color: #d96a1a; font-weight: bold;"><?php echo e($pedido->dispositivo->marca); ?> <?php echo e($pedido->dispositivo->modelo); ?></span>
            </li>
            <li>
                <strong>Averías:</strong>
                <span style="color: #334155;"><?php echo e($pedido->tipo_reparacion); ?></span>
            </li>
            <li>
                <strong>Presupuesto:</strong>
                <span style="color: #10b981; font-weight: bold; font-size: 16px;"><?php echo e($pedido->precio_estimado); ?></span>
            </li>
            <li>
                <strong>Notas:</strong>
                <span style="color: #64748b; font-style: italic;">"<?php echo e($pedido->descripcion); ?>"</span>
            </li>
        </ul>

        <div class="footer-text">
            Accede al <strong>Panel de Administración</strong> de Josetech para gestionar esta orden y cambiar su estado a "En Taller" cuando recepciones el equipo.
        </div>
    </div>
</body>

</html><?php /**PATH C:\Users\joseb\Documents\GIT\proyectoJosetech\backend\resources\views/emails/pedido_admin.blade.php ENDPATH**/ ?>