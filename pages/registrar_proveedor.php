<?php
include "../functions/conexion.php";

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php
    include "../include/head.php";
    ?>
    <title>Registrar Proveedor</title>
</head>

<body class="contentdash">
    <header class="ctnheader">
        <?php
        include "../include/header.php";
        ?>
    </header>
    <div class="ctnpage">
        <div class="ctnform" id="targetcenter">
            <div class="targetform">
                <form action="" method="post" class="row g-3" id="form_insert">
                    <div class="col-12">
                        <div class="col-md-6 d-grid mx-auto">
                            <h1 class="col-12 display-6 d-flex justify-content-center">
                                Registrar Proveedor
                            </h1>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="inputname" class="form-label required">Nombre Proveedor</label>
                        <input type="text" class="form-control" id="inputname" name="inputname" autocomplete="off">
                        <div class="valid-feedback">
                            Correcto!
                        </div>
                        <div class="invalid-feedback">
                            El nombre es obligatorio!
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="inputdocumento" class="form-label required">C.I o RUC</label>
                        <input type="text" class="form-control" id="inputdocumento" name="inputdocumento" autocomplete="off">
                        <div class="valid-feedback">
                            Correcto!
                        </div>
                        <div class="invalid-feedback">
                            El RUC es obligatorio, no puede contener "." ni letras, debe ser mayor a 5 digitos
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="inputemail" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="inputemail" name="inputemail" autocomplete="off">
                        <div class="valid-feedback">
                            Correcto!
                        </div>
                        <div class="invalid-feedback">
                            No puede contener espacios y debe incluir un "@"
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="inputphone" class="form-label">Celular</label>
                        <input type="text" class="form-control" id="inputphone" name="inputphone" autocomplete="off">
                        <div class="valid-feedback">
                            Correcto!
                        </div>
                        <div class="invalid-feedback">
                            Debe de ser de 10 digitos y no puede contener espacios ni puntos "."
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="direccion" class="form-label required">Direccion</label>
                        <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Av. Pratt Gill Ñemby" autocomplete="off">
                        <div class="valid-feedback">
                            Correcto!
                        </div>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="inputCity" class="form-label required">Ciudad</label>
                        <select class="form-select" id="inputCity" name="inputCity" autocomplete="off">
                            <option selected disabled>Selecciones una ciudad</option>
                            <?php
                            include "../backend/selectcontry.php";
                            ?>
                        </select>
                        <div class="valid-feedback">
                            Correcto!
                        </div>
                        <div class="invalid-feedback">
                            Este campo es obligatorio
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="col-md-2 d-grid mx-auto">
                            <button type="button" class="btn btn-dark btn-lg" name="insertclient" id="insertclient">Registrar</button>
                        </div>
                    </div>
                </form>
                <div class="containerta">
                    <table id="myTable" class="table table-hover table-bordered table-striped nowrap" style="width:100%;">
                        <thead>
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col">Ci / Ruc</th>
                                <th scope="col">Email</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include "../backend/viewproveedor.php";
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
    include "../include/editproveemodal.php";
    ?>
    <footer class="ctnfooter">
        <?php
        include "../include/footer.php"; ?>
    </footer>
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="../assets/js/datatables/datatables.min.js"></script>
    <script src="../scripts/proveedor.js"></script>
    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                "language": idioma_espanol,
                layout: {
                    bottomEnd: {
                        paging: {
                            firstLast: false
                        }
                    }
                }
            });
        });
        var idioma_espanol = {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "search": "Buscar:",
            "loadingRecords": "Cargando...",
            "decimal": ".",
            "emptyTable": "No hay datos disponibles en la tabla",
            "zeroRecords": "No se encontraron coincidencias",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
            "infoFiltered": "(Filtrado de _MAX_ total de entradas)",
            "lengthMenu": "Mostrar _MENU_ entradas",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }
    </script>
</body>

</html>