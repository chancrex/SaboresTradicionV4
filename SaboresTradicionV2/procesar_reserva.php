<?php
include 'components/connect.php';
session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $nombre = $_POST['nombre'];
   $telefono = $_POST['telefono'];
   $fecha = $_POST['fecha'];
   $hora = $_POST['hora'];
   $mesa = $_POST['mesa'];
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
               $sql = "INSERT INTO reservas (nombre, telefono, fecha, hora, mesa, num_personas, comentarios, estado, user_id) VALUES (:nombre, :telefono, :fecha, :hora, :mesa, :num_personas, :comentarios, :estado, :user_id)";
               $stmt = $conn->prepare($sql);
               $stmt->bindParam(':nombre', $nombre);
               $stmt->bindParam(':telefono', $telefono);
               $stmt->bindParam(':fecha', $fecha);
               $stmt->bindParam(':hora', $hora);
               $stmt->bindParam(':mesa', $mesa);
               $stmt->bindParam(':num_personas', $num_personas, PDO::PARAM_INT);
               $stmt->bindParam(':comentarios', $comentarios);
               $stmt->bindParam(':estado', $estado);
               $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

               if ($stmt->execute()) {
                   echo '<div style="text-align: center; background-color: #4CAF50; color: #fff; padding: 20px; border-radius: 10px;">';
                   echo '<img src="images/moso.png" alt="Reserva exitosa" style="max-width: 100%; height: auto; margin-bottom: 20px;">';
                   echo '<h2 style="font-size: 28px;">¡Reserva exitosa!</h2>';
                   echo '<p style="font-size: 18px; margin-bottom: 20px;">Gracias por elegir nuestro restaurante. Esperamos verte pronto.</p>';
                   echo '<a href="index.php" style="background-color: #007BFF; color: #fff; text-decoration: none; padding: 15px 30px; border-radius: 5px; font-size: 20px;">Regresar </a>';
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
