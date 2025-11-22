<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial; font-size: 13px; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #000; padding: 5px; font-size: 12px; }
th { background: #eee; }
</style>
</head>
<body>

<h2 style="text-align:center;">Factura Nº <?= $numeroFactura ?></h2>

<p><strong>Cliente:</strong> <?= $cliente['Nombre_Cliente'] ?></p>
<p><strong>RUC:</strong> <?= $cliente['RUC_Cliente'] ?></p>
<p><strong>Dirección:</strong> <?= $cliente['Direccion_del_Cliente'] ?></p>

<table>
<thead>
<tr>
<th>ID</th>
<th>Cantidad</th>
<th>Precio Unit.</th>
<th>Total</th>
</tr>
</thead>
<tbody>
<?php foreach($carrito as $item): ?>
<tr>
<td><?= $item['id'] ?></td>
<td><?= $item['cantidad'] ?></td>
<td><?= number_format($item['precio'],2) ?></td>
<td><?= number_format($item['precio']*$item['cantidad'],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h3>Total: S/ <?= number_format($total,2) ?></h3>

</body>
</html>
