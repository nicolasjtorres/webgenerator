<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['idUsuario'])) {
    header("Location: panel.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT idUsuario, password FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($password === $row['password']) {
            $_SESSION['idUsuario'] = $row['idUsuario'];
            $_SESSION['email'] = $email;
            header("Location: panel.php");
            exit();
        } else {
            $error = "Contrasena incorrecta.";
        }
    } else {
        $error = "El usuario no existe.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>webgenerator Nicolas Torres</title>
</head>
<body>
    <h2>webgenerator Nicolas Torres</h2>
    
    <?php if ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        
        <label>Contrasena:</label><br>
        <input type="password" name="password" required><br><br>
        
        <button type="submit">Ingresar</button>
    </form>
    
    <br>
    <a href="register.php">Registrarte es simple</a>
</body>
</html>
