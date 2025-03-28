<?php
include "../functions/conexion.php";
try {
    $query = ("SELECT * FROM `ciudades` ORDER BY `ciudades`.`nombre_ciudad` ASC");
    $stmt = $connect->prepare($query);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id_ciudad = $row["id_ciudad"];
        $nombre = $row["nombre_ciudad"];
        echo "<option value='$id_ciudad'>$nombre</option>";
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
