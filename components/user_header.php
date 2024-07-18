<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'components/connect.php';

// Verificación de sesión
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

// Verificar si existe una encuesta relacionada con el usuario
$encuesta_existente = false;
if ($user_id != '') {
   try {
      $check_encuesta = $conn->prepare("SELECT COUNT(*) AS count_encuestas FROM encuestas WHERE user_id = ?");
      $check_encuesta->execute([$user_id]);
      $result = $check_encuesta->fetch(PDO::FETCH_ASSOC);
      if ($result['count_encuestas'] > 0) {
         $encuesta_existente = true;
      }
   } catch (PDOException $e) {
      // Manejo de errores si es necesario
   }
}

if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">
   <section class="flex">
      <a href="index.php" class="logo" style="display: flex; align-items: center; justify-content: center;">
         <div style="text-align: center;">
             Sabores<br>Tradición
         </div>
         <i class="fa-solid fa-heart black-heart" style="margin-left: 5px;"></i>
      </a>
      <nav class="navbar">
         <a href="index.php" class="nav-link"><i class="fas fa-home"></i> INICIO</a>
         <a href="menu.php" class="nav-link"><i class="fas fa-utensils"></i> Menú</a>
         <a href="recipes_view.php" class="nav-link"><i class="fa-solid fa-clipboard-list"></i> Recetas</a>
         <a href="reservas.php" class="nav-link login-required"><i class="fas fa-clock"></i> Reservas</a>
         <?php if ($user_id != '' && !$encuesta_existente): ?>
            <a href="encuesta.php" class="nav-link"><i class="fas fa-star"></i> Encuesta</a>
         <?php endif; ?>
      </nav>
      <div class="icons">
      <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_items = $count_cart_items->rowCount();
         ?>
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $total_cart_items; ?>)</span></a>
         <div id="user-btn" class="fas fa-user"></div>
         <div id="menu-btn" class="fas fa-bars"></div>
      </div>
      <div class="profile" id="profile-menu">
         <?php
            if($user_id != ''){
               $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
               $select_profile->execute([$user_id]);
               if ($select_profile->rowCount() > 0) {
                  $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p class="name"><?= htmlspecialchars($fetch_profile['name']); ?></p>
         <div class="flex">
            <a href="profile.php" class="btn">Tu perfil</a>
            <a href="components/user_logout.php" onclick="return confirm('¿Cerrar sesión en este sitio web?');" class="delete-btn">Cerrar sesión</a>
         </div>
         <?php
               } else {
                  echo '<p class="name">Error: No se encontró el perfil del usuario.</p>';
               }
            } else {
         ?>
         <p class="name">¡Por favor ingresa primero!</p>
         <a href="login.php" class="btn">Login</a>
         <?php
            }
         ?>
      </div>
   </section>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginRequiredLinks = document.querySelectorAll('.login-required');

    loginRequiredLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            <?php if ($user_id == ''): ?>
                event.preventDefault();
                alert('Para realizar una reserva primero debe registrarse');
                window.location.href = 'login.php';
            <?php else: ?>
                window.location.href = 'reservas.php';
            <?php endif; ?>
        });
    });
});
</script>
