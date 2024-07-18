<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_GET['id'])) {
    $reserva_id = $_GET['id'];
    $select_reserva = $conn->prepare("SELECT * FROM `reservas` WHERE id = ?");
    $select_reserva->execute([$reserva_id]);
    $reserva = $select_reserva->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        // Manejar el caso en el que la reserva no existe
    }
} else {
    // Manejar el caso en el que no se proporciona un ID válido
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Editar Reserva</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<style>
  .editar-reserva {
    border: 6px solid #11a3a3;
    border-radius: 8px;
    max-width: 600px;
    margin: 0 auto;
    padding: 40px;
    background: linear-gradient(45deg, #3498db, #2c3e50);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    border-radius: 10px;
}

.heading {
    
    font-size: 32px;
    text-align: center;
    margin-bottom: 20px;
    color: #FF5722; /* Azul para destacar */
}

.box-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
    
}

label {
    
    font-weight: bold;
    color: white; /* Texto más oscuro */
    font-size: 22px; /* Tamaño de fuente más grande */
}

input[type="text"],
input[type="date"],
input[type="time"],
input[type="number"],
textarea {
    width: 100%;
    padding: 15px;
    border: 5px solid #0056b3; /* Bordes de color azul */
    border-radius: 10px;
    font-size: 20px;
    background-color: #f9f9f9; /* Fondo más claro */
    color: #333;
    transition: border-color 0.3s, transform 0.3s;
}

input[type="text"]:focus,
input[type="date"]:focus,
input[type="time"]:focus,
input[type="number"]:focus,
textarea:focus {
    border-color: #FF5722; /* Color naranja al enfocar */
    box-shadow: 0 0 12px rgba(255, 87, 34, 0.5); /* Sombra naranja al enfocar */
}

input[type="submit"] {
    background-color: #333;
    color: #fff;
    border: 6px solid #00ffff;
    border-radius: 10px; 
    padding: 15px 30px;
     
    cursor: pointer;
    font-size: 24px;
    transition: background-color 0.3s, transform 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

input[type="submit"]:hover {
    background-color: #0056b3; /* Cambiado a azul oscuro al pasar el mouse */
    transform: scale(1.05);
}

i.fas.fa-pencil-alt {
    font-size: 28px;
    margin-right: 10px;
}

/* Estilos para el contenedor del formulario */
.form-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    border-radius: 10px;
}
.editar-reserva {
    max-width: 600px;
    margin: 0 auto;
    padding: 40px;
    background-color: #f0f0f0;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    transition: transform 0.3s;
}

.editar-reserva:hover {
    transform: scale(1.02); 
}

   </style>
<?php include '../components/admin_header.php' ?>

<!-- editar reserva section starts  -->

<section class="editar-reserva">
   <h1 class="heading">Editar Reserva</h1>
   
   <div class="box-container">
      <form action="actualizar_reserva.php" method="POST">
          <input type="hidden" name="reserva_id" value="<?= $reserva_id ?>">
          <label for="nombre">Nombre:</label>
          <input type="text" name="nombre" value="<?= $reserva['nombre'] ?>">
          <label for="telefono">Teléfono:</label>
          <input type="text" name="telefono" value="<?= $reserva['telefono'] ?>">
          <label for="fecha">Fecha:</label>
          <input type="date" name="fecha" value="<?= $reserva['fecha'] ?>">
          <label for="hora">Hora:</label>
          <input type="time" name="hora" value="<?= $reserva['hora'] ?>">
          <label for="mesa">Mesa:</label>
          <input type="text" name="mesa" value="<?= $reserva['mesa'] ?>">
          <label for="num_personas">Número de personas:</label>
          <input type="number" name="num_personas" value="<?= $reserva['num_personas'] ?>">
          <label for="comentario">Comentario:</label>
          <textarea name="comentario"><?= $reserva['comentario'] ?></textarea>
          <input type="submit" value="Actualizar Reserva">

      </form>
   </div>
</section>

<!-- editar reserva section ends -->

<!-- custom js file link  -->
<script src="../js/admin_script.js"></script>

</body>
</html>
