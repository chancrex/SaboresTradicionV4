<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_reserva = $conn->prepare("DELETE FROM `reservas` WHERE id = ?");
    $delete_reserva->execute([$delete_id]);
    header('location:admin_reservas.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE, chrome=1">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Reservas</title>

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
      }

     /* Estilo para la clase .heading */
.heading {
   font-size: 20px; /* Aumenté el tamaño de la fuente para mayor impacto */
   text-align: center;
   margin-bottom: 20px;
   color: #f0f0f0;
   font-weight: bold; /* Añadí negrita para un aspecto más fuerte */
   text-transform: uppercase; /* Convierte el texto a mayúsculas */
   letter-spacing: 1px; /* Añade un pequeño espacio entre las letras */
   text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2); /* Sutil sombra para resaltar el texto */
   transition: color 0.3s ease; /* Animación suave del color al pasar el ratón */
}

/* Efecto al pasar el ratón */
.heading:hover {
   color: #0056b3; /* Cambia el color al pasar el ratón para un efecto visual */
}

      .box-container {
         display: flex;
         flex-direction: column;
         gap: 20px;
      }

      .box {
         border: 6px solid #00ffff;
          border-radius: 10px;
         padding: 35px;
         border-radius: 5px;
         box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
         position: relative;
      }

     /* Estilo para párrafos dentro de la clase box */
.box p {
   display: flex;
   align-items: center; /* Centra verticalmente el contenido */
   margin: 0;
   font-size: 20px; /* Tamaño de fuente aumentado */
   color: #f0f0f0; /* Color de texto oscuro */
   border-bottom: 2px solid transparent; /* Línea inferior transparente para la animación */
   transition: border-bottom 0.3s ease; /* Animación suave de la línea inferior */
}

/* Pseudo-clase para resaltar el párrafo al pasar el ratón */
.box p:hover {
   
   border-bottom: 6px solid #3498db; /* Línea inferior con color al pasar el ratón */
}

      .box a {
         display: inline-block;
         padding: 10px 20px;
         margin-top: 10px;
         border: 6px solid #00ffff;
         border-radius: 10px;
         text-decoration: none;
         font-weight: bold;
         transition: background-color 0.3s ease;
      }

      .update-btn {
         
         background-color: #007BFF;
         color: #fff;
      }

      .update-btn:hover {
         background-color: #0056b3;
      }

      .update-btn {
    background-color: #007BFF;
    color: #fff;
    padding: 15px 30px; /* Aumenta el tamaño del botón aquí */
    border-radius: 10px;
    cursor: pointer;
    font-size: 24px;
    transition: background-color 0.3s, transform 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.update-btn:hover {
    background-color: #00ffff;
    transform: scale(1.05);
}


      .icon {
         margin-right: 5px;
      }

      .empty {
         text-align: center;
         color: #888;
         font-size: 18px;
         margin-top: 20px;
      }
   </style>
</head>
<body>

<?php include '../components/admin_header.php' ?>

<!-- reservas section starts  -->

<section class="reservas">
   <h1 class="heading">Reservas Pendientes</h1>
   <div class="box-container">

   <?php
      $select_reservas = $conn->prepare("SELECT * FROM `reservas`");
      $select_reservas->execute();
      if ($select_reservas->rowCount() > 0) {
         while ($fetch_reservas = $select_reservas->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <div class="box">
      <p> Cliente : <span><?= $fetch_reservas['nombre']; ?></span> </p>
      <p> Teléfono : <span><?= $fetch_reservas['telefono']; ?></span> </p>
      <p> Fecha : <span><?= $fetch_reservas['fecha']; ?></span> </p>
      <p> Hora : <span><?= $fetch_reservas['hora']; ?></span> </p>
      <p> Mesa : <span><?= $fetch_reservas['mesa']; ?></span> </p>
      <p> Número de personas : <span><?= $fetch_reservas['num_personas']; ?></span> </p>
      <p> Comentario : <span><?= $fetch_reservas['comentario']; ?></span> </p>
      <a href="editar_reserva.php?id=<?= $fetch_reservas['id']; ?>" class="update-btn">
         <i class="icon fas fa-pencil-alt"></i> Editar Reserva
      </a>
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
