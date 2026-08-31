<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['idUsuario'])) {
    header("Location: login.php");
    exit();
}

$idUsuario = $_SESSION['idUsuario'];
$emailUser = $_SESSION['email'];
$mensaje = "";

// 1. Eliminar Web
if (isset($_GET['eliminar'])) {
    $idWeb = $_GET['eliminar'];

    if ($emailUser === 'admin@server.com') {
        $stmtDel = $conn->prepare("SELECT dominio FROM webs WHERE idWeb = ?");
        $stmtDel->bind_param("i", $idWeb);
    } else {
        $stmtDel = $conn->prepare("SELECT dominio FROM webs WHERE idWeb = ? AND idUsuario = ?");
        $stmtDel->bind_param("ii", $idWeb, $idUsuario);
    }
    
    $stmtDel->execute();
    $resDel = $stmtDel->get_result();

    if ($rowDel = $resDel->fetch_assoc()) {
        $dominioBorrar = $rowDel['dominio'];
        
        $stmtB = $conn->prepare("DELETE FROM webs WHERE idWeb = ?");
        $stmtB->bind_param("i", $idWeb);
        $stmtB->execute();

        shell_exec("rm -rf " . escapeshellarg($dominioBorrar));
        shell_exec("rm -f " . escapeshellarg($dominioBorrar . ".zip"));
        
        $mensaje = "Web eliminada con exito.";
    }
}

// 2. Descargar Web (Comprimir)
if (isset($_GET['descargar'])) {
    $dominioZip = $_GET['descargar'];

    if (is_dir($dominioZip)) {
        $zipFile = $dominioZip . ".zip";
        shell_exec("zip -r " . escapeshellarg($zipFile) . " " . escapeshellarg($dominioZip));

        if (file_exists($zipFile)) {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
            header('Content-Length: ' . filesize($zipFile));
            readfile($zipFile);
            exit();
        }
    }
}

// 3. Crear Web Nueva
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre_web'])) {
    $nombreIngresado = trim($_POST['nombre_web']);
    $dominioConcatenado = $idUsuario . $nombreIngresado;

    $stmtCheck = $conn->prepare("SELECT idWeb FROM webs WHERE dominio = ?");
    $stmtCheck->bind_param("s", $dominioConcatenado);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    if ($resCheck->num_rows > 0) {
        $mensaje = "El dominio " . $dominioConcatenado . " ya existe.";
    } else {
        $stmtIns = $conn->prepare("INSERT INTO webs (idUsuario, dominio) VALUES (?, ?)");
        $stmtIns->bind_param("is", $idUsuario, $dominioConcatenado);
        $stmtIns->execute();

        $cmd = "./wix.sh " . escapeshellarg($dominioConcatenado);
        shell_exec($cmd);

        $mensaje = "Web " . $dominioConcatenado . " creada exitosamente.";
    }
}

// 4. Listar Webs
if ($emailUser === 'admin@server.com') {
    $sqlWebs = "SELECT idWeb, dominio, idUsuario FROM webs";
    $resultWebs = $conn->query($sqlWebs);
} else {
    $stmtList = $conn->prepare("SELECT idWeb, dominio, idUsuario FROM webs WHERE idUsuario = ?");
    $stmtList->bind_param("i", $idUsuario);
    $stmtList->execute();
    $resultWebs = $stmtList->get_result();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bienvenido a tu panel</title>
</head>
<body>
    <h2>Bienvenido a tu panel</h2>
    <p><a href="logout.php">Cerrar sesion de <?php echo $idUsuario; ?></a></p>

    <?php if ($mensaje): ?>
        <p style="color:blue;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <form method="POST" action="panel.php">
        <h3>Generar Web de:</h3>
        <input type="text" name="nombre_web" required placeholder="Nombre de la web">
        <button type="submit">Crear web</button>
    </form>

    <hr>
    <h3>Tus sitios web creados:</h3>
    <ul>
        <?php while ($web = $resultWebs->fetch_assoc()): ?>
            <li>
                <a href="<?php echo $web['dominio']; ?>/index.php" target="_blank"><?php echo $web['dominio']; ?></a>
                | <a href="panel.php?descargar=<?php echo $web['dominio']; ?>">descargar web</a>
                | <a href="panel.php?eliminar=<?php echo $web['idWeb']; ?>" onclick="return confirm('Eliminar web?')">Eliminar</a>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
