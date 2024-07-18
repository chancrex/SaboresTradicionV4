<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Reservas</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<script src="js/chatbot.js"></script>
<?php include 'components/user_header.php'; ?>

<div class="heading">
   <h3><i class="fas fa-clock"></i> Reservas</h3>
   <p><a href="index.php">INICIO</a> <span> / Reservas</span></p>
</div>

<!-- sección de opciones de reservas -->
<section class="reserva-options">
   <div class="options-container">
      <div class="option-box">
         <h3>Historial de Reservas</h3>
         <a href="historial_reservas.php" class="btn">Ver Mis Reservas</a>
      </div>
      <!-- <div class="option-box">
         <h3>Reservas Pendientes</h3>
         <a href="reservas_pendientes.php" class="btn">Ver Pendientes</a>
      </div> -->
      <div class="option-box">
         <h3>Hacer una Reserva</h3>
         <a href="registrar_reserva.php" class="btn">Reservar Ahora</a>
      </div>
   </div>
</section>

<style>
/* Estilos para la sección de opciones de reservas */
.reserva-options {
   display: flex;
   justify-content: center;
   align-items: center;
   padding: 20px;
}

.options-container {
   display: flex;
   gap: 20px;
}

.option-box {
   background-color: #fff;
   border: 2px solid #00ffff;
   border-radius: 10px;
   padding: 20px;
   text-align: center;
   box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
   transition: transform 0.3s, box-shadow 0.3s;
}

.option-box h3 {
   font-size: 24px;
   margin-bottom: 10px;
}

.option-box .btn {
   background: linear-gradient(135deg, #ffcc00, #ff6600);
   color: #fff;
   padding: 10px 20px;
   text-decoration: none;
   border-radius: 5px;
   transition: background 0.3s, transform 0.3s;
}

.option-box .btn:hover {
   background: #ff6600;
   transform: scale(1.05);
}

.option-box:hover {
   transform: translateY(-5px);
   box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}
</style>
<script src="js/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
