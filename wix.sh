#!/bin/bash

if [ -z "$1" ]; then
    echo "Falta el nombre de dominio"
    exit 1
fi

DOMINIO=$1

mkdir -p "$DOMINIO"

cat <<EOT > "$DOMINIO/index.php"
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>$DOMINIO</title>
</head>
<body>
    <h1>Bienvenido a la web de $DOMINIO</h1>
</body>
</html>
EOT

chmod -R 777 "$DOMINIO"
