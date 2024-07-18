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
         <a href="#" class="nav-link"><i class="fas fa-users"></i> Nosotros</a>
         <a href="menu.php" class="nav-link"><i class="fas fa-utensils"></i> Menú</a>
         <a href="reservas.php" class="nav-link login-required"><i class="fas fa-clock"></i> Reservas</a>
      </nav>
      <div class="icons">
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
