<?php
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

      <a href="dashboard.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
   <a href="dashboard.php"><i class="fa fa-home"></i> INICIO</a>
   <a href="products.php"><i class="fas fa-box"></i> PRODUCTOS</a>
   <a href="recipes.php"><i class="fa-solid fa-clipboard-list"></i> RECETAS</a>
   <a href="users_accounts.php"><i class="fas fa-users"></i> Usuarios</a>
   <a href="admin_reservas.php"><i class="fas fa-envelope"></i> Reservas</a>
   <a href="encuesta_admin.php"><i class="fas fa-star"></i> Encuestas</a>

   
</nav>


      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `admin` WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= $fetch_profile['name']; ?></p>
         <a href="admin_update_profile.php" class="btn">Actualización del perfil</a>
         <div class="flex-btn">
            <!-- <a href="admin_login.php" class="option-boton">Login</a> -->
            <a href="register_admin.php" class="option-boton">Registrar Admin</a>
            <div class="custom-container">
  <a href="../components/admin_logout.php" onclick="return confirm('¿Cerrar sesión en este sitio web?');" class="logout-boton">Cerrar Sesión</a>
</div>
   </section>

</header>