<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

$error_message = '';

function verificarAforo($conn, $fecha, $hora, $num_personas) {
    try {
        // Obtener la hora sin los minutos
        $hora_inicio = date('H:00:00', strtotime($hora));
        $hora_fin = date('H:59:59', strtotime($hora));

        // Añadir log
        error_log("Fecha: $fecha, Hora Inicio: $hora_inicio, Hora Fin: $hora_fin");

        $query = $conn->prepare("SELECT SUM(num_personas) as total_personas FROM reservas WHERE fecha = :fecha AND hora BETWEEN :hora_inicio AND :hora_fin AND estado = 'pendiente'");
        $query->bindParam(':fecha', $fecha);
        $query->bindParam(':hora_inicio', $hora_inicio);
        $query->bindParam(':hora_fin', $hora_fin);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $total_personas = $row['total_personas'] ?? 0; // Si es null, asignar 0

        // Añadir log
        error_log("Fecha: $fecha, Hora: $hora, Total Personas Existentes: $total_personas, Nuevas Personas: $num_personas");

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
   $estado = 'pendiente'; // Puedes cambiar esto según la lógica de tu negocio

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

   if (empty($error_message)) {
       try {
           // Comprobar el número de reservas pendientes para el usuario
           $sql = "SELECT COUNT(*) FROM reservas WHERE user_id = :user_id AND estado = 'pendiente'";
           $stmt = $conn->prepare($sql);
           $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
           $stmt->execute();
           $num_pendientes = $stmt->fetchColumn();

           if ($num_pendientes >= 2) {
               $error_message = "Tienes ya dos reservas!! Reserva tu mesa en las próximas fechas :)";
           }

           if (empty($error_message)) {
               // Prepara la consulta SQL para insertar los datos en la tabla usando declaraciones preparadas
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
                echo '<div style="text-align: center; background-color: #4CAF50; color: #fff; padding: 20px; border-radius: 10px;">';
                echo '<img src="images/moso.png" alt="Reserva exitosa" style="max-width: 100%; height: auto; margin-bottom: 20px;">';
                echo '<h2 style="font-size: 28px;">¡Reserva exitosa!</h2>';
                echo '<p style="font-size: 18px; margin-bottom: 20px;">Ahora falta confirmar su asistencia mediante el pago. Gracias por elegir nuestro restaurante. Esperamos verte pronto.</p>';
                echo '<a href="historial_reservas.php" style="background-color: #007BFF; color: #fff; text-decoration: none; padding: 15px 30px; border-radius: 5px; font-size: 20px; margin-right: 20px;">Pagar Reserva</a>';
                echo '<a href="index.php" style="background-color: #007BFF; color: #fff; text-decoration: none; padding: 15px 30px; border-radius: 5px; font-size: 20px;">Volver al inicio</a>';
                echo '</div>';
               } else {
                   $error_message = "Error: No se pudo completar la reserva.";
               }
           }
       } catch (PDOException $e) {
           $error_message = "Error: " . $e->getMessage();
       }
   }
}

if (!empty($error_message)) {
    echo '<div id="errorModal" class="modal">';
    echo '<div class="modal-content">';
    echo '<span class="close" onclick="document.getElementById(\'errorModal\').style.display=\'none\'">&times;</span>';
    echo '<p style="font-size: 24px; text-align: center;">' . $error_message . '</p>';
    echo '<div style="text-align: center; margin-top: 20px;">';
    echo '<a href="index.php" style="background-color: #28a745; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; font-size: 18px;">Volver</a>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Reservas</title>
   <link rel="stylesheet" href="css/style.css">
   <style>
      .modal {
          display: block;
          position: fixed;
          z-index: 1;
          left: 0;
          top: 0;
          width: 100%;
          height: 100%;
          overflow: auto;
          background-color: rgb(0,0,0);
          background-color: rgba(0,0,0,0.4);
          padding-top: 60px;
      }

      .modal-content {
          background-color: #f44336;
          margin: 5% auto;
          padding: 20px;
          border: 1px solid #888;
          width: 80%;
          color: white;
          border-radius: 10px;
      }

      .close {
          color: white;
          float: right;
          font-size: 28px;
          font-weight: bold;
      }

      .close:hover,
      .close:focus {
          color: #000;
          text-decoration: none;
          cursor: pointer;
      }
   </style>
</head>
<body>
   <!-- El contenido de la página aquí -->
   <script src="js/script.js"></script>
</body>
</html>
