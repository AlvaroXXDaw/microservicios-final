<?php
/**
 * ============================================
 * FACTURA - Render HTML desde la base de datos
 * ============================================
 * URL: factura.php?factura_id=FAC-...
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

$facturaId = isset($_GET['factura_id']) ? trim($_GET['factura_id']) : '';

if ($facturaId === '') {
    http_response_code(400);
    echo 'Factura invalida';
    exit();
}

// Obtener factura
$stmtFactura = $conexion->prepare(
    "SELECT factura_id, usuario_id, direccion_envio, subtotal, envio, total, fecha_factura
     FROM facturas
     WHERE factura_id = ?"
);
$stmtFactura->bind_param('s', $facturaId);
$stmtFactura->execute();
$resultFactura = $stmtFactura->get_result();

if ($resultFactura->num_rows === 0) {
    http_response_code(404);
    echo 'Factura no encontrada';
    exit();
}

$factura = $resultFactura->fetch_assoc();

// Si hay sesion, comprobar que la factura es del usuario
if (isset($_SESSION['user_id']) && intval($_SESSION['user_id']) !== intval($factura['usuario_id'])) {
    http_response_code(403);
    echo 'No tienes permisos para ver esta factura';
    exit();
}

// Obtener items
$stmtItems = $conexion->prepare(
    "SELECT c.cantidad, c.precio_unitario, p.nombre AS producto_nombre
     FROM compras c
     INNER JOIN productos p ON c.producto_id = p.id
     WHERE c.factura_id = ?"
);
$stmtItems->bind_param('s', $facturaId);
$stmtItems->execute();
$resultItems = $stmtItems->get_result();

$items = [];
while ($row = $resultItems->fetch_assoc()) {
    $items[] = $row;
}

$fecha = date('d/m/Y H:i', strtotime($factura['fecha_factura']));

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Factura <?php echo htmlspecialchars($facturaId); ?></title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@600;700;800&display=swap");
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Barlow", Arial, sans-serif;
            background: #f5f5f5;
            padding: 40px 20px;
            color: #111111;
        }
        .page {
            max-width: 780px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #111111;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 32px 36px;
            border-bottom: 2px solid #111111;
            background: #111111;
            color: #ffffff;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .brand-mark {
            width: 14px;
            height: 36px;
            background: #e10600;
        }
        .brand-name {
            font-family: "Barlow Condensed", "Barlow", Arial, sans-serif;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .brand-tag {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #f2f2f2;
        }
        .invoice-meta {
            text-align: right;
            font-size: 13px;
        }
        .invoice-meta .label {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 11px;
            color: #cfcfcf;
        }
        .invoice-meta .value {
            font-size: 18px;
            font-weight: 700;
            margin-top: 4px;
        }
        .section {
            padding: 28px 36px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .panel {
            border: 1px solid #e0e0e0;
            padding: 16px;
            background: #fafafa;
        }
        .panel h4 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 8px;
            color: #333333;
        }
        .panel p {
            font-size: 14px;
            color: #4d4d4d;
            line-height: 1.4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #666666;
            background: #f5f5f5;
        }
        td {
            font-size: 14px;
        }
        .text-right { text-align: right; }
        .totals {
            margin-top: 18px;
            border-top: 2px solid #111111;
            padding-top: 16px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }
        .total-final {
            font-size: 20px;
            font-weight: 700;
            color: #e10600;
        }
        .footer {
            padding: 24px 36px 32px;
            border-top: 1px solid #e0e0e0;
            color: #6b6b6b;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .footer strong {
            color: #111111;
        }
        .note {
            margin-top: 6px;
            font-size: 11px;
            color: #8a8a8a;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="brand">
                <div class="brand-mark"></div>
                <div>
                    <div class="brand-name">SanbaTech</div>
                    <div class="brand-tag">Factura simplificada</div>
                </div>
            </div>
            <div class="invoice-meta">
                <div class="label">Factura</div>
                <div class="value"><?php echo htmlspecialchars($facturaId); ?></div>
                <div><?php echo htmlspecialchars($fecha); ?></div>
            </div>
        </div>

        <div class="section">
            <div class="grid">
                <div class="panel">
                    <h4>Direccion de envio</h4>
                    <p><?php echo htmlspecialchars($factura['direccion_envio']); ?></p>
                </div>
                <div class="panel">
                    <h4>Resumen</h4>
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span><?php echo number_format($factura['subtotal'], 2); ?> EUR</span>
                    </div>
                    <div class="total-row">
                        <span>Gastos de envio</span>
                        <span><?php echo $factura['envio'] == 0 ? 'GRATIS' : number_format($factura['envio'], 2) . ' EUR'; ?></span>
                    </div>
                    <div class="total-row total-final">
                        <span>Total</span>
                        <span><?php echo number_format($factura['total'], 2); ?> EUR</span>
                    </div>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($items) === 0): ?>
                        <tr>
                            <td colspan="4">No hay productos en esta factura.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <?php $totalItem = floatval($item['precio_unitario']) * intval($item['cantidad']); ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['producto_nombre']); ?></td>
                                <td class="text-right"><?php echo number_format($item['precio_unitario'], 2); ?> EUR</td>
                                <td class="text-right"><?php echo intval($item['cantidad']); ?></td>
                                <td class="text-right"><?php echo number_format($totalItem, 2); ?> EUR</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="totals">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span><?php echo number_format($factura['subtotal'], 2); ?> EUR</span>
                </div>
                <div class="total-row">
                    <span>Gastos de envio</span>
                    <span><?php echo $factura['envio'] == 0 ? 'GRATIS' : number_format($factura['envio'], 2) . ' EUR'; ?></span>
                </div>
                <div class="total-row total-final">
                    <span>Total</span>
                    <span><?php echo number_format($factura['total'], 2); ?> EUR</span>
                </div>
            </div>
        </div>

        <div class="footer">
            <div>
                <strong>Gracias por tu compra en SanbaTech.</strong>
                <div class="note">Este documento es una factura simplificada.</div>
            </div>
            <div>
                <div>Atencion al cliente: soporte@sanbatech.com</div>
                <div class="note">Horario: 09:00 - 19:00</div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conexion->close();
?>
