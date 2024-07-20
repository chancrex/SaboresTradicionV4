<?php
include 'components/connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

$error_message = '';

// Obtener el correo electrónico del usuario desde la sesión
$email = isset($_SESSION['user_email']) ? trim($_SESSION['user_email']) : '';

function verificarAforo($conn, $fecha, $hora, $num_personas) {
    try {
        $hora_inicio = date('H:00:00', strtotime($hora));
        $hora_fin = date('H:59:59', strtotime($hora));

        $query = $conn->prepare("SELECT SUM(num_personas) as total_personas FROM reservas WHERE fecha = :fecha AND hora BETWEEN :hora_inicio AND :hora_fin AND estado = 'pendiente'");
        $query->bindParam(':fecha', $fecha);
        $query->bindParam(':hora_inicio', $hora_inicio);
        $query->bindParam(':hora_fin', $hora_fin);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $total_personas = $row['total_personas'] ?? 0;

        return ($total_personas + $num_personas) > 6;
    } catch (PDOException $e) {
        error_log("Error en verificarAforo: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $num_personas = $_POST['num_personas'];
    $comentarios = $_POST['comentarios'];
    $estado = 'pendiente';

    // Validar el teléfono
    if (!preg_match('/^[0-9]{1,9}$/', $telefono)) {
        $error_message = "Error: El teléfono debe contener solo números y hasta 9 dígitos.";
    }

    // Validar la fecha
    $fecha_actual = date('Y-m-d');
    if (strtotime($fecha) <= strtotime($fecha_actual)) {
        $error_message = "Error: La fecha de reserva debe ser posterior a hoy.";
    }

    // Validar la hora
    $hora_minima = strtotime("11:00");
    $hora_maxima = strtotime("16:00");
    $hora_reserva = strtotime($hora);
    if ($hora_reserva < $hora_minima || $hora_reserva > $hora_maxima) {
        $error_message = "Error: La hora de reserva debe ser entre las 11:00 y las 16:00.";
    }

    // Validar el aforo
    if (verificarAforo($conn, $fecha, $hora, $num_personas)) {
        $error_message = "La cantidad de personas supera el aforo permitido para esa hora. 😢";
    }

    // Validar el correo electrónico
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Error: El correo electrónico es inválido o está vacío.";
    }

    if (empty($error_message)) {
        try {
            $sql = "SELECT COUNT(*) FROM reservas WHERE user_id = :user_id AND estado = 'pendiente'";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $num_pendientes = $stmt->fetchColumn();

            if ($num_pendientes >= 2) {
                $error_message = "Tienes ya dos reservas!! Reserva tu mesa en las próximas fechas :)";
            }

            if (empty($error_message)) {
                $sql = "INSERT INTO reservas (nombre, telefono, fecha, hora, num_personas, comentarios, estado, user_id) VALUES (:nombre, :telefono, :fecha, :hora, :num_personas, :comentarios, :estado, :user_id)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':fecha', $fecha);
                $stmt->bindParam(':hora', $hora);
                $stmt->bindParam(':num_personas', $num_personas, PDO::PARAM_INT);
                $stmt->bindParam(':comentarios', $comentarios);
                $stmt->bindParam(':estado', $estado);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    // Enviar correo de confirmación con PHPMailer
                    $mail = new PHPMailer(true);

                    try {
                        // Configuraciones del servidor SMTP
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'saborestradicion24@gmail.com'; // Tu correo SMTP
                        $mail->Password = 'ifibfmozvxylvelz'; // Tu contraseña de aplicación SMTP
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;
                        $mail->CharSet = 'UTF-8';

                        // Remitente y destinatario
                        $mail->setFrom('saborestradicion24@gmail.com', 'Sabores Tradición');
                        $mail->addAddress($email, $nombre); // Usa el correo del usuario aquí

                        // Contenido del correo
                        $mail->isHTML(true);
                        $mail->Subject = 'Confirmación de Reserva en Sabores Tradición';
                        $mail->Body = "
                           <html>
                           <head>
                           <title>Confirmación de Reserva</title>
                           </head>
                           <body>
                           <h1>¡Tu reserva ya esta casi lista! 🎉</h1>
                           <p>Hola $nombre,</p>
                           <p>Tu reserva para el día $fecha a las $hora ha sido registrada, solo falta confirmar por un único pago de 20 nuevos soles.</p>
                           <p>Gracias por elegir nuestro restaurante. Esperamos verte pronto.</p>
                           </body>
                           </html>
                        ";

                        $mail->send();
                        echo '
                            <div style="display: block; background-color: #28a745; color: white; padding: 20px;">
                                ¡Gracias por reservar en Sabores Tradición! Te notificaremos en tu correo tu reserva, ahora falta confirmar la reserva mediante el pago.
                            </div>
                            <div style="text-align: center; margin-top: 20px;">
                                <a href="index.php" style="text-decoration: none; background-color: #007bff; color: white; padding: 10px 20px; border-radius: 5px; font-size: 16px; margin: 5px;">Regresar al Inicio</a>
                                <a href="historial_reservas.php" style="text-decoration: none; background-color: #28a745; color: white; padding: 10px 20px; border-radius: 5px; font-size: 16px; margin: 5px;">Proceder a Pagar</a>
                            </div>';
                    } catch (Exception $e) {
                        $error_message = "Error al enviar el correo: " . $mail->ErrorInfo;
                    }
                } else {
                    $error_message = "Error al guardar la reserva.";
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Reserva</title>
</head>
<body>
    <?php if (!empty($error_message)): ?>
        <div style="display: block; background-color: #dc3545; color: white; padding: 20px;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
</body>
</html>
