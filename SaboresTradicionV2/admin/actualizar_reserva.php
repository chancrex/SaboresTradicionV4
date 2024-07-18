<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica si se envió una solicitud POST

    $reserva_id = $_POST['reserva_id'];
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $mesa = $_POST['mesa'];
    $num_personas = $_POST['num_personas'];
    $comentario = $_POST['comentario'];

    // Validación adicional y medidas de seguridad se deben agregar aquí

    // Actualiza la reserva en la base de datos
    $actualizar_reserva = $conn->prepare("UPDATE `reservas` SET nombre = ?, telefono = ?, fecha = ?, hora = ?, mesa = ?, num_personas = ?, comentario = ? WHERE id = ?");
    $actualizar_reserva->execute([$nombre, $telefono, $fecha, $hora, $mesa, $num_personas, $comentario, $reserva_id]);

    // Redirige de nuevo a la página de administración de reservas
    header('location:admin_reservas.php');
} else {
    // Maneja el caso en el que no se envió una solicitud POST
    // Puedes redirigir o mostrar un mensaje de error
}
?>
