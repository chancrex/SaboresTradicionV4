<?php
include 'components/connect.php';

session_start();

if (isset($_POST['submit'])) {
   $token = $_POST['token'];
   $new_password = sha1($_POST['new_password']);
   $new_password = filter_var($new_password, FILTER_SANITIZE_STRING);
   $confirm_password = sha1($_POST['confirm_password']);
   $confirm_password = filter_var($confirm_password, FILTER_SANITIZE_STRING);

   if ($new_password != $confirm_password) {
      $message[] = '¡Confirme la contraseña, no coincide!';
   } else {
      $select_user = $conn->prepare("SELECT * FROM `users` WHERE reset_token = ? AND reset_token_expiry > NOW()");
      $select_user->execute([$token]);
      $row = $select_user->fetch(PDO::FETCH_ASSOC);

      if ($select_user->rowCount() > 0) {
         $update_password = $conn->prepare("UPDATE `users` SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
         $update_password->execute([$new_password, $token]);
         $message[] = '¡Contraseña restablecida correctamente!';
      } else {
         $message[] = '¡Token inválido o expirado!';
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
   <title>Restablecer Contraseña</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="css/style.css">
</head>

<body>

<!-- header section starts -->
<?php include 'components/user_header.php'; ?>
<!-- header section ends -->

<section class="form-container">
   <form action="" method="post">
      <h3>Restablecer Contraseña</h3>
      <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
      <input type="password" name="new_password" required placeholder="Nueva Contraseña" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="confirm_password" required placeholder="Confirmar Nueva Contraseña" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Restablecer" name="submit" class="btn">
      <p>¿No tienes una cuenta? <a href="register.php">Regístrate Ahora</a></p>
      <p>¿Ya tienes una cuenta? <a href="login.php">Inicia Sesión</a></p>
   </form>
   <?php
   if (isset($message)) {
      foreach ($message as $msg) {
         echo '<p class="message">' . $msg . '</p>';
      }
   }
   ?>
</section>

<?php include 'components/footer.php'; ?>

<!-- custom js file link -->
<script src="js/script.js"></script>

</body>

</html>
