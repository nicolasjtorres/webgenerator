<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['idUsuario'])) {
    header("Location: panel.php");
    exit();
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $pass1 = $_POST['password'];
    $pass2 = $_POST['password_repeat'];

    if ($pass1 !== $pass2) {
        $mensaje = "Las contrasenas no coinciden.";
    } else {
        $stmt = $conn->prepare("SELECT idUsuario FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $mensaje = "El email ya se encuentra registrado.";
        } else {
            $stmtInsert = $conn->prepare("INSERT INTO usuarios (email, password) VALUES (?, ?)");
            $stmtInsert->bind_param("ss", $email, $pass1);
            if ($stmtInsert->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $mensaje = "Error al registrar el usuario.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrarte es simple.</title>
</head>
<body>
    <h2>Registrarte es simple.</h2>

    <?php if ($mensaje): ?>
        <p style="color:red;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Contrasena:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Repetir Contrasena:</label><br>
        <input type="password" name="password_repeat" required><br><br>

        <button type="submit">Registrarme</button>
    </form>
    
    <br>
    <a href="login.php">Volver al login</a>
</body>
</html>
