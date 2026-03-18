<?php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reserva de Hotel</title>
    <link rel="stylesheet" href="bootstrap-3.3.7-dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Realizar Reserva</h2>
        <form action="server.php" method="POST">
            <div class="form-group">
                <label for="ciudad">Ciudad:</label>
                <input type="text" class="form-control" id="ciudad" name="ciudad" required>
            </div>
            <div class="form-group">
                <label for="hotel">Hotel:</label>
                <input type="text" class="form-control" id="hotel" name="hotel" required>
            </div>
            <div class="form-group">
                <label for="entrada">Fecha de entrada:</label>
                <input type="date" class="form-control" id="entrada" name="entrada" required>
            </div>
            <div class="form-group">
                <label for="salida">Fecha de salida:</label>
                <input type="date" class="form-control" id="salida" name="salida" required>
            </div>
            <div class="form-group">
                <label for="personas">Personas:</label>
                <input type="number" class="form-control" id="personas" name="personas" min="1" max="20" value="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Reservar</button>
        </form>
    </div>
</body>
</html>
?>