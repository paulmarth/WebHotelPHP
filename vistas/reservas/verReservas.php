<?php
session_start();
if ($_SESSION['tipouser'] != 'TU1') {
    header('Location: login.php');
    exit;   
} else if(isset($_SESSION['iduser'])) {
}
else {
    header("Location: login.php");
    exit;
}

include '../../database/Conexion.php';

$con = new Conexion();
$db = $con->getCon();


// Obtener los datos del formulario
$iduser = $_SESSION['iduser'];
$tipouser = $_SESSION['tipouser'];

if ($tipouser == 'TU1') { // admin
    $stmt = $db->prepare("SELECT nom_emp, ape_emp FROM tb_empleado WHERE iduser = :iduser");
    $stmt->bindParam(':iduser', $iduser);
    $stmt->execute();
    $row = $stmt->fetch();
    $nombre = $row['nom_emp'];
    $apellido = $row['ape_emp'];
}

// Incluir la clase ModeloReserva
require_once '../../modelo/ModeloReserva.php';

// Instanciar la clase ModeloReserva
$modeloReserva = new ModeloReserva();

// Obtener los datos de las reservas
$datos = $modeloReserva->getReservas();
?>
<!DOCTYPE html>
<!--
Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHPWebPage.php to edit this template
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Panel Admin</title>
        <link href="../../css/Estilo.css" rel="stylesheet" type="text/css"/>
        <link rel="icon" href="../../img/logo_1.png">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Philosopher:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="../../css/panel_admin.css" rel="stylesheet" type="text/css"/>
        <script src="../../js/navbar.js" type="text/javascript"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
       
    </head>
    <body>
        <header class="cabecera">
        <nav class="navbar">
            <div class="logo"><a href='../../index.php'><img src='../../img/logo.png'/>Hotel Green Ghost</a></div>
            <ul class="links_nav">
                <li><a href="../../index.php#acerca">Nosotros</a></li>
                <li><a href="../../index.php#habitaciones">Habitaciones</a></li>
                <li><a href="../../index.php#servicios">Servicios</a></li>
                <li><a href="../../index.php#contacto">Contacto</a></li>
            </ul>
            <a href="../reserva.php" class="action_btn btn">Reservar</a>
            <?php
            if(isset($_SESSION['iduser'])) {
                // Si el usuario está autenticado, mostrar el nombre de usuario y el enlace para cerrar sesión
                switch ($_SESSION['tipouser']) {
                    case 'TU1': // Administrador
                        echo "<div class='admin_btn'><a href='#' class='action_btn btn'>Admin</a></div>";
                        break;
                    case 'TU2': // Empleado
                        echo "<div class='employee_btn'><a href='vistas/supervisor/panel_supervisor.php' class='action_btn btn'>{$_SESSION['username']}</a></div>";
                        break;
                    case 'TU3': // Supervisor
                        echo "<div class='supervisor_btn'><a href='vistas/supervisor/panel_empleado.php' class='action_btn btn'>{$_SESSION['username']}</a></div>";
                        break;
                    case 'TU4': // Cliente
                        echo "<div class='client_btn'><a href='vistas/cliente/panel_cliente.php' class='action_btn btn'>Cliente{$_SESSION['username']}</a></div>";
                        break;
                    default:
                        echo "<div><a href='logout.php'>Logout</a></div>";
                        break;
                }
            } else {
                // Si el usuario no está autenticado, mostrar el botón de inicio de sesión
                echo "<a href='vistas/login.php' class='action_btn btn'>Login</a>";
            }
            ?>
            <div class="toggle_btn">
                <i class="fa fa-bars menu-icon"></i>
            </div>    
        </nav>
        <div class="dropdown_menu">
            <li><a href="../../index.php#acerca">Nosotros</a></li>
            <li><a href="../../index.php#habitaciones">Habitaciones</a></li>
            <li><a href="../../index.php#servicios">Servicios</a></li>
            <li><a href="../../index.php#contacto">Contacto</a></li>
            <li><a href="../reserva.php" class="action_btn btn">Reservar</a></li>
            
            <?php
        if(isset($_SESSION['iduser'])) {
            switch ($_SESSION['tipouser']) {
                case 'TU1':
                    echo "<li><a href='#' class='action_btn btn'>ADMIN</a></li>";
                    break;
                case 'TU2':
                    echo "<li><a href='vistas/supervisor/panel_supervisor.php' class='action_btn btn'>Supervisor{$_SESSION['username']}</a></li>";
                    break;
                case 'TU3':
                    echo "<li><a href='vistas/supervisor/panel_empleado.php' class='action_btn btn'>Empleado{$_SESSION['username']}</a></li>";
                    break;
                case 'TU4':
                    echo "<li><a href='vistas/cliente/panel_cliente.php' class='action_btn'>CLIENTE</a></li>";
                    break;
                default:
                    echo "<li><a href='vistas/login.php' class='action_btn btn'>Login</a></li>";
                    break;
            }
        } else {
            echo "<li><a href='vistas/login.php' class='action_btn btn'>Login</a></li>";
        }
        ?>
        </div>  
    </header>
        <?php  include "eliminarReserva.php";  ?>
    <div class="fondo">
        <section class="hero">
    <main>
        <div class="dashboard">
            <?php echo '<h1>Bienvenido, ' . $nombre . ' ' . $apellido . '</h1>'; 
            echo "<a style='color:blue' href='../cerrarSesion.php'>CERRAR SESIÓN</a>";?>
            <div class="stats">
                <?php
                $stmt = $db->prepare("SELECT COUNT(*) AS num_reservas FROM tb_reserva");
                $stmt->execute();
                $result = $stmt->fetch();
                $num_reservas = $result['num_reservas'];

                $stmt = $db->prepare("SELECT COUNT(*) AS num_pagos FROM tb_pago");
                $stmt->execute();
                $result = $stmt->fetch();
                $num_pagos = $result['num_pagos'];

                $stmt = $db->prepare("SELECT COUNT(*) AS num_user FROM tb_usuario");
                $stmt->execute();
                $result = $stmt->fetch();
                $num_user = $result['num_user'];

                $stmt = $db->prepare("SELECT COUNT(*) AS num_clie FROM tb_cliente");
                $stmt->execute();
                $result = $stmt->fetch();
                $num_clie = $result['num_clie'];

                $stmt = $db->prepare("SELECT COUNT(*) AS num_emp FROM tb_empleado");
                $stmt->execute();
                $result = $stmt->fetch();
                $num_emp = $result['num_emp'];
                ?>
                <button>VER RESERVAS<br>TOTAL Reservas: <?php echo $num_reservas; ?></button>
                <button onclick="location.href='../admin/panel_admin.php'">VER GRÁFICO<br>Reservas por Mes</button>
                <button onclick="location.href='verPagos.php'">VER PAGOS<br>TOTAL Pagos: <?php echo $num_pagos; ?></button>
                <button onclick="location.href='../admin/verUsuarios.php'">VER USUARIOS<br>TOTAL Usuarios: <?php echo $num_user; ?></button>
                <button onclick="location.href='../admin/verClientes.php'">VER CLIENTES<br>TOTAL Clientes: <?php echo $num_clie; ?></button>
                <button onclick="location.href='../admin/verEmpleados.php'">VER EMPLEADOS<br>TOTAL Empleados: <?php echo $num_emp; ?></button>
            </div>
            <table class='table table-bordered'>
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Cuarto</th>
                                <th>Fecha</th>
                                <th>Llegada</th>
                                <th>Salida</th>
                                <th>Días</th>
                                <th>Personas</th>
                                <th>Costo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($datos)) {
                                foreach ($datos as $dato) {
                                    echo "<tr>";
                                    echo "<td>" . $dato['N°'] . "</td>";
                                    echo "<td>" . $dato['Usuario'] . "</td>";
                                    echo "<td>" . $dato['Nombre'] . "</td>";
                                    echo "<td>" . $dato['Cuarto'] . "</td>";
                                    echo "<td>" . $dato['Fecha'] . "</td>";
                                    echo "<td>" . $dato['Llegada'] . "</td>";
                                    echo "<td>" . $dato['Salida'] . "</td>";
                                    echo "<td>" . $dato['Días'] . "</td>";
                                    echo "<td>" . $dato['Personas'] . "</td>";
                                    echo "<td>" . $dato['Costo'] . "</td>";
                                    echo "<td>";
                                    /*echo "<a href='editarReserva.php?cod=" . $dato['N°'] . "' class='edit-icon'><i class='fas fa-pencil-alt'></i></a> ";*/
                                    echo "<a href='#' onclick='borrarReserva(" . $dato['N°'] . ")' class='delete-icon'><i class='fas fa-trash'></i></a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='11'>No se encontraron resultados</td></tr>";
                            }
                            ?>
                        </tbody>
                </table> 
            </div>
    </main>
    </div>
    <script>
        function borrarReserva(id) {
        if (confirm("¿Estás seguro de eliminar esta reserva?")) {
            // Crear una solicitud XMLHttpRequest
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "eliminarReserva.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            // Configurar la función de callback
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    // Aquí puedes manejar la respuesta del servidor
                    var response = xhr.responseText;
                    if (response === "success") {
                        alert("Reserva eliminada correctamente.");
                        // Recargar la página o eliminar la fila de la tabla
                        location.reload();
                    } else {
                        alert("Error al eliminar la reserva.");
                    }
                }
            };

            // Enviar la solicitud con el id de la reserva
            xhr.send("cod=" + id);
        }
    }
    </script>
    <footer>
        <div class="container">
            <p>&copy; 2024 Hotel Green Ghost. Todos los derechos reservados.</p>
            <div class="social-icons">
                <a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </footer>
        
</body>
</html>
