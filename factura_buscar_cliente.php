<?php
session_start();
require_once "conexion.php";
$conn = $conexion;

$mensaje = "";
$cliente = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ruc = trim($_POST['ruc']);

    $sql = "
        SELECT 
            c.RUC_Cliente,
            cn.Nombre_Cliente,
            cd.Direccion_del_Cliente
        FROM Cliente c
        LEFT JOIN Cliente_Nombre cn ON c.ID_NombreCliente = cn.ID_Nombre_Cliente
        LEFT JOIN Cliente_Direccion cd ON c.ID_DireccionCliente = cd.ID_DireccionCliente
        WHERE c.RUC_Cliente = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ruc);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $cliente = $res->fetch_assoc();
    } else {
        $mensaje = "No se encontró el cliente con ese RUC.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Cliente</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<header>
    <h1>Buscar Cliente</h1>
</header>

<main class="contenedor">
    <h2>Buscar Cliente por RUC</h2>

    <form method="POST" class="formulario">
        <label>RUC del Cliente:</label>
        <input type="text" name="ruc" required>
        <button type="submit" class="btn btn-crear">Buscar</button>
    </form>

    <?php if ($mensaje != ""): ?>
        <p style="color:red;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <?php if ($cliente !== null): ?>
        <div class="tarjeta">
            <h3>Datos del Cliente</h3>
            <p><strong>RUC:</strong> <?= htmlspecialchars($cliente['RUC_Cliente']) ?></p>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($cliente['Nombre_Cliente']) ?></p>
            <p><strong>Dirección:</strong> <?= htmlspecialchars($cliente['Direccion_del_Cliente']) ?></p>

            <a class="btn btn-crear" href="factura_crear.php?ruc=<?= urlencode($cliente['RUC_Cliente']) ?>">Continuar</a>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
