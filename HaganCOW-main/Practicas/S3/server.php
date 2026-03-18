<?php
$ciudad = $_POST['ciudad'] ?? '';
$hotel = $_POST['hotel'] ?? '';
$entrada = $_POST['entrada'] ?? '';
$salida = $_POST['salida'] ?? '';
$personas = $_POST['personas'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado de la Reserva</title>
    <link rel="stylesheet" href="bootstrap-3.3.7-dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Reserva realizada</h2>
        <p><strong>Ciudad:</strong> <?php echo htmlspecialchars($ciudad); ?></p>
        <p><strong>Hotel:</strong> <?php echo htmlspecialchars($hotel); ?></p>
        <p><strong>Fecha de entrada:</strong> <?php echo htmlspecialchars($entrada); ?></p>
        <p><strong>Fecha de salida:</strong> <?php echo htmlspecialchars($salida); ?></p>
        <p><strong>Personas:</strong> <?php echo htmlspecialchars($personas); ?></p>
        <a href="client.php" class="btn btn-default">Volver</a>
    </div>
</body>
</html>