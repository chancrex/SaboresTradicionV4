<?php
include 'components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_reserva = $conn->prepare("DELETE FROM `reservas` WHERE id = ?");
    $delete_reserva->execute([$delete_id]);
    header('location:admin_reservas.php');
    exit();
}

$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$new_order = $order === 'DESC' ? 'ASC' : 'DESC';

function formatDate($date) {
    $dateObj = new DateTime($date);
    return $dateObj->format('d/m/Y');
}

function formatTime($time) {
    $timeObj = new DateTime($time);
    return $timeObj->format('H:i');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>GESTIÓN DE RESERVAS</title>

   <!-- Font Awesome CDN link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">
   <style>
      body {
         font-family: Arial, sans-serif;
         background-color: #f0f0f0;
         margin: 0;
         padding: 0;
         display: flex;
         flex-direction: column;
         align-items: center;
      }

      .reservas {
         max-width: 800px;
         padding: 20px;
         box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
         border-radius: 5px;
         background-color: #ffffff;
      }

      .heading {
         font-size: 24px;
         text-align: center;
         margin-bottom: 20px;
         color: #333;
         font-weight: bold;
         text-transform: uppercase;
         letter-spacing: 1px;
         text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
         transition: color 0.3s ease;
      }

      .heading:hover {
         color: #0056b3;
      }

      .box-container {
         display: flex;
         flex-direction: column;
         gap: 20px;
         width: 100%;
      }

      .box {
         border: 1px solid #ccc;
         border-radius: 10px;
         padding: 20px;
         background-color: #fafafa;
         box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
         position: relative;
         width: 100%;
      }

      .box p {
         display: flex;
         align-items: center;
         margin: 0;
         font-size: 18px;
         color: #555;
      }

      .box p span {
         margin-left: 10px; /* Espaciado de dos espacios después del ":" */
         color: #000; /* Color del dato */
      }

      .box p strong {
         font-weight: bold;
         color: #000; /* Color negro para las cabeceras */
      }

      .box .estado {
         font-weight: bold;
      }

      .estado.pendiente {
         color: blue;
      }

      .estado.pagada {
         color: green;
      }

      .estado.pasada {
         color: red;
      }

      .box a {
         display: inline-block;
         padding: 10px 20px;
         margin-top: 10px;
         border: 2px solid #007BFF;
         border-radius: 5px;
         text-decoration: none;
         font-weight: bold;
         transition: background-color 0.3s ease;
         font-size: 18px;
      }

      .update-btn {
         background-color: #007BFF;
         color: #fff;
      }

      .update-btn:hover {
         background-color: #0056b3;
      }

      .delete-btn {
         background-color: #FF0000;
         color: #fff;
      }

      .delete-btn:hover {
         background-color: #CC0000;
      }

      .icon {
         margin-right: 5px;
      }

      .empty {
         text-align: center;
         color: #888;
         font-size: 20px;
         margin-top: 20px;
      }

      .order-bar {
         text-align: center;
         margin-bottom: 20px;
      }

      .order-bar a {
         text-decoration: none;
         color: #007BFF;
         font-weight: bold;
         font-size: 18px;
      }
   </style>
</head>
<body>

<?php include 'components/admin_header.php' ?>

<!-- reservas section starts  -->

<section class="reservas">
   <h1 class="heading">GESTIÓN DE RESERVAS</h1>
   <div class="order-bar">
      <a href="?order=<?= $new_order ?>">Ordenar por fecha <?= $order === 'DESC' ? 'ascendente' : 'descendente' ?></a>
   </div>
   <div class="box-container">

   <?php
      $select_reservas = $conn->prepare("SELECT * FROM `reservas` ORDER BY fecha $order, hora $order");
      $select_reservas->execute();
      if ($select_reservas->rowCount() > 0) {
         while ($fetch_reservas = $select_reservas->fetch(PDO::FETCH_ASSOC)) {
            $estado_class = strtolower($fetch_reservas['estado']);
            $fecha_formateada = formatDate($fetch_reservas['fecha']);
            $hora_formateada = formatTime($fetch_reservas['hora']);
   ?>
   <div class="box">
      <p> <strong>Cliente:</strong> <span><?= $fetch_reservas['nombre']; ?></span> </p>
      <p> <strong>Teléfono:</strong> <span><?= $fetch_reservas['telefono']; ?></span> </p>
      <p> <strong>Fecha:</strong> <span><?= $fecha_formateada; ?></span> </p>
      <p> <strong>Hora:</strong> <span><?= $hora_formateada; ?></span> </p>
      <p> <strong>Estado:</strong> <span class="estado <?= $estado_class ?>"><?= $fetch_reservas['estado']; ?></span> </p>
      <p> <strong>Número de personas:</strong> <span><?= $fetch_reservas['num_personas']; ?></span> </p>
      <p> <strong>Comentario:</strong> <span><?= $fetch_reservas['comentarios']; ?></span> </p>
      <?php if (in_array(strtolower($fetch_reservas['estado']), ['pendiente', 'pagada'])): ?>
      <a href="editar_reserva.php?id=<?= $fetch_reservas['id']; ?>" class="update-btn">
         <i class="icon fas fa-pencil-alt"></i> Editar Reserva
      </a>
      <?php endif; ?>
      <a href="admin_reservas.php?delete=<?= $fetch_reservas['id']; ?>" class="delete-btn" onclick="return confirm('¿Borrar esta reserva?');">
         <i class="icon fas fa-trash"></i> Eliminar Reserva
      </a>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">¡No tienes reservas pendientes!</p>';
      }
   ?>

   </div>
</section>

<!-- reservas section ends -->

<!-- custom js file link  -->
<script src="../js/admin_script.js"></script>

</body>
</html>
