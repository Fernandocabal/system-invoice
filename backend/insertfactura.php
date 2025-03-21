<?php
include "../functions/conexion.php";
date_default_timezone_set('America/Asuncion');
session_start();
require_once '../functions/funciones.php';
$nombre = $_SESSION['nombre'];
$apellido = $_SESSION["apellido"];
$sucursal_activa = $_SESSION['id_sucursal_activa'];
$caja_activa = $_SESSION['id_caja_activa'];
$id_empresa = $_SESSION['id_empresa_activa'];
$anulado = "N";
// var_dump($anulado);
try {
    $connect->beginTransaction();
    if (!estalogueado()) {
        throw new Exception("No haz iniciado sesión");
        exit();
    }
    // Obtener el timbrado vigente
    $sqltimbrado = "SELECT * FROM `timbrado` WHERE id_empresa= :id_empresa AND id_sucursal= :id_sucursal AND id_caja= :id_caja";
    $stmtimbrado = $connect->prepare($sqltimbrado);
    $stmtimbrado->bindParam(':id_empresa', $id_empresa, PDO::PARAM_INT);
    $stmtimbrado->bindParam(':id_sucursal', $sucursal_activa, PDO::PARAM_INT);
    $stmtimbrado->bindParam(':id_caja', $caja_activa, PDO::PARAM_INT);
    $stmtimbrado->execute();
    $timbrado = $stmtimbrado->fetch(PDO::FETCH_ASSOC);

    if (!$timbrado) {
        throw new Exception("No se encontró un timbrado válido para la empresa.");
    }

    $id_timbrado = $timbrado["id_timbrado"];
    $fecha_vencimiento = $timbrado["fecha_vencimiento"];

    $fecha = date('d/m/Y H:i');
    $precio = $_POST["precio"];
    $descripcion = $_POST["descripcion"];
    $cantidad = $_POST["cantidad"];
    $exentas = $_POST['exenta'];
    $gravada5 = $_POST['gravada5'];
    $gravada10 = $_POST['gravada10'];
    $cant_check = count($descripcion);
    if (count($precio) !== $cant_check || count($cantidad) !== $cant_check) {
        throw new Exception("El número de ítems no coincide en descripción, precio y cantidad.");
    }
    $nombres = $_POST["nombres"];
    $sqlcondicion = "SELECT * FROM `condicion` WHERE id_condicion = 1";
    $stmt = $connect->query($sqlcondicion);
    $condicion = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($condicion) {
        $id_condicion = $condicion["id_condicion"];
    }
    function siguientenumero($id_sucursal, $id_caja, $id_empresa)
    {
        global $connect;
        $sqlnumeracion = "SELECT ultimo_numero FROM numeracion_factura 
                          WHERE id_empresa = :id_empresa 
                          AND id_sucursal = :id_sucursal 
                          AND id_caja = :id_caja";
        $stmt = $connect->prepare($sqlnumeracion);
        $stmt->bindParam(':id_empresa', $id_empresa, PDO::PARAM_INT);
        $stmt->bindParam(':id_sucursal', $id_sucursal, PDO::PARAM_INT);
        $stmt->bindParam(':id_caja', $id_caja, PDO::PARAM_INT);
        $stmt->execute();
        $numeracion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$numeracion) {
            $query = "INSERT INTO numeracion_factura (id_empresa, `id_sucursal`, `id_caja`,  ultimo_numero)
            VALUES (:id_empresa, :id_sucursal, :id_caja, 0)";
            $stmt = $connect->prepare($query);
            $stmt->bindParam(':id_empresa', $id_empresa, PDO::PARAM_INT);
            $stmt->bindParam(':id_sucursal', $id_sucursal, PDO::PARAM_INT);
            $stmt->bindParam(':id_caja', $id_caja, PDO::PARAM_INT);
            $stmt->execute();
            $ultimo_numero = 0;
        } else {

            $ultimo_numero = $numeracion['ultimo_numero'];
        }
        $proximonumero = $ultimo_numero + 1;
        $parte3 = str_pad($proximonumero, 7, '0', STR_PAD_LEFT);  // 7 dígitos, rellena con ceros a la izquierda

        $sqlnrosucursal = "SELECT s.nro_sucursal, c.nro_caja 
        FROM sucursales s 
        JOIN cajas c ON s.id_sucursal = c.id_sucursal 
        WHERE s.id_sucursal = :id_sucursal AND c.id_caja = :id_caja";
        $stmt = $connect->prepare($sqlnrosucursal);
        $stmt->bindParam(':id_sucursal', $id_sucursal, PDO::PARAM_INT);
        $stmt->bindParam(':id_caja', $id_caja, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$resultado) {
            throw new Exception("No se encontró la sucursal o caja seleccionada.");
        }

        $nrosucursal = $resultado['nro_sucursal'];
        $nrocaja = $resultado['nro_caja'];

        // Formatear el número de factura
        $formatproximonumero = $nrosucursal . '-' . $nrocaja . '-' . $parte3;

        // Actualizar el último número de factura en la tabla
        $stmt = $connect->prepare("UPDATE numeracion_factura SET ultimo_numero = :ultimo_numero 
        WHERE id_empresa = :id_empresa AND id_sucursal = :id_sucursal AND id_caja = :id_caja");
        $stmt->bindParam(':ultimo_numero', $proximonumero, PDO::PARAM_INT);
        $stmt->bindParam(':id_empresa', $id_empresa, PDO::PARAM_INT);
        $stmt->bindParam(':id_sucursal', $id_sucursal, PDO::PARAM_INT);
        $stmt->bindParam(':id_caja', $id_caja, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $formatproximonumero;
        } else {
            throw new Exception("Error al actualizar el último número de factura.");
        }
    }
    $numeroFactura = siguientenumero($sucursal_activa, $caja_activa, $id_empresa);
    function totalexentas($exentas)
    {
        $total = 0;
        if (is_array($exentas)) {
            for ($i = 0; $i < count($exentas); $i++) {
                if (is_numeric($exentas[$i])) {
                    $total += floatval($exentas[$i]);
                }
            }
        }
        return $total;
    }
    function totalgravada5($gravada5)
    {
        $total = 0;
        if (is_array($gravada5)) {
            for ($i = 0; $i < count($gravada5); $i++) {
                if (is_numeric($gravada5[$i])) {
                    $total += floatval($gravada5[$i]);
                }
            }
        }
        return $total / 21;
    }
    function totalgravada10($gravada10)
    {
        $total = 0;
        if (is_array($gravada10)) {
            for ($i = 0; $i < count($gravada10); $i++) {
                if (is_numeric($gravada10[$i])) {
                    $total += floatval($gravada10[$i]);
                }
            }
        }
        return $total / 11;
    }
    function totaliva($totalgravada5, $totalgravada10)
    {
        return ($totalgravada5 + $totalgravada10);
    }
    function totalfactura($totalexentas, $gravada5, $gravada10)
    {
        $totalgr10 = 0;
        $totalgr5 = 0;
        if (is_array($gravada10)) {
            for ($i = 0; $i < count($gravada10); $i++) {
                if (is_numeric($gravada10[$i])) {
                    $totalgr10 += floatval($gravada10[$i]);
                }
            }
        }
        if (is_array($gravada5)) {
            for ($i = 0; $i < count($gravada5); $i++) {
                if (is_numeric($gravada5[$i])) {
                    $totalgr5 += floatval($gravada5[$i]);
                }
            }
        }
        return ($totalexentas + $totalgr5 + $totalgr10);
    };
    $totalexentas = totalexentas($exentas);
    $totalgravada5 = totalgravada5($gravada5);
    $totalgravada10 = totalgravada10($gravada10);
    $totaliva = totaliva($totalgravada5, $totalgravada10);
    $totalfactura = totalfactura($totalexentas, $gravada5, $gravada10);
    // var_dump($totalfactura);

    //funtion para crear el número de registro de acuerdo a la empresa del usuario
    function crearnroregistro()
    {
        global $connect;

        $slqnroregistro = "SELECT MAX(registro) AS max_registro FROM header_factura WHERE id_empresa = :id_empresa AND id_sucursal = :id_sucursal AND id_caja= :id_caja";
        $stmt = $connect->prepare($slqnroregistro);
        $stmt->bindParam('id_empresa', $_SESSION['id_empresa_activa'], PDO::PARAM_INT);
        $stmt->bindParam('id_sucursal', $_SESSION['id_sucursal_activa'], PDO::PARAM_INT);
        $stmt->bindParam('id_caja', $_SESSION['id_caja_activa'], PDO::PARAM_INT);
        $stmt->execute();
        $nroregistro = $stmt->fetch(PDO::FETCH_ASSOC);
        $ultimoreg = $nroregistro['max_registro'] ? $nroregistro['max_registro'] : 0;
        $proximoreg = $ultimoreg + 1;
        return $proximoreg;
    };
    $proximoreg = crearnroregistro();

    $insertfacturas = "INSERT INTO `header_factura`(`registro`, `nro_factura`, `anulado`, `id_timbrado`, `fecha_horas`, `id_cliente`, `cajero`, `id_condicion`, `exentas`, `gravada5`, `gravada10`, `totaliva`, `totalfactura`, `id_empresa`, `id_sucursal`, `id_caja`, `id_usuario`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connect->prepare($insertfacturas);
    $stmt->execute([$proximoreg, $numeroFactura, $anulado, $id_timbrado, $fecha, $nombres, $_SESSION["nombre"], $id_condicion, $totalexentas, $totalgravada5, $totalgravada10, $totaliva, $totalfactura, $_SESSION["id_empresa_activa"], $_SESSION['id_sucursal_activa'], $_SESSION['id_caja_activa'], $_SESSION['id_usuario']]);
    $idinsertado = $connect->lastInsertId();

    $insertdetalle = "INSERT INTO `detalle_factura`(`id_header`, `detalle`, `cantidad`, `precio`, `exenta`, `gravada5`, `gravada10`) VALUES (?, ?, ?, ?,?, ?, ?)";
    $stmt = $connect->prepare($insertdetalle);
    for ($i = 0; $i < $cant_check; $i++) {
        $descripcion_items = $descripcion[$i];
        $precio_items = $precio[$i];
        $cantidad_items = $cantidad[$i];
        $exenta_item = $exentas[$i];
        $gravada5_item = floatval($gravada5[$i]) / 1.05;
        $gravada10_item = floatval($gravada10[$i]) / 11;

        if (!is_numeric($cantidad_items) || !is_numeric($precio_items)) {
            throw new Exception("Cantidad y precio deben ser numéricos.");
        }

        $stmt->execute([$idinsertado, $descripcion_items, $cantidad_items, $precio_items, $exenta_item, $gravada5_item, $gravada10_item]);
    }
    $connect->commit();

    echo json_encode([
        'success' => true,
        'id' => $idinsertado,
        'message' => 'La factura se ha generado!'
    ]);
} catch (PDOException $e) {
    $connect->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error al insertar datos: ' . $e->getMessage(),
        'sql' => $insertfacturas,
        'data' => [
            $proximoreg,
            $numeroFactura,
            $id_timbrado,
            $fecha,
            $nombres,
            $_SESSION["nombre"],
            $id_condicion,
            $totalexentas,
            $totalgravada5,
            $totalgravada10,
            $totaliva,
            $totalfactura,
            $_SESSION["id_empresa_activa"],
            $_SESSION['id_usuario']
        ]
    ]);
}
$connect = null;
