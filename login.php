<?php
include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

if (isset($_POST['submit'])) {
    // Verificar el token anti-CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message[] = '¡Error de seguridad! Intento de CSRF detectado.';
    } else {
        // Continuar con el proceso de inicio de sesión
        $email = $_POST['email'];
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $pass = $_POST['pass'];
        $pass = filter_var($pass, FILTER_SANITIZE_STRING);

        $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
        $select_user->execute([$email]);
        $row = $select_user->fetch(PDO::FETCH_ASSOC);

        if ($select_user->rowCount() > 0 && password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_email'] = $row['email']; // Guardar el correo electrónico en la sesión
            header('location:index.php');
            exit();
        } else {
            $message[] = '¡Nombre de usuario o contraseña incorrecta!';
        }
    }
}

// Generar un nuevo token anti-CSRF y guardarlo en la sesión
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>login</title>

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
      <!-- Agregar campo para el token anti-CSRF -->
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <h3>Inicia Sesión Ahora</h3>
      <input type="email" name="email" required placeholder="Introduce tu correo electrónico" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="pass" required placeholder="Ingresa tu contraseña" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Ingresar" name="submit" class="btn">
      <p>¿No tienes una cuenta? <a href="register.php">Registrate Ahora</a></p>
      <p>¿Olvidaste tu contraseña? <a href="password_restore.php">Haz click aquí</a></p>
   </form>

   <?php
   if (isset($message)) {
      foreach ($message as $msg) {
         echo '<div class="message">'.$msg.'</div>';
      }
   }
   ?>

</section>

<?php include 'components/footer.php'; ?>

<!-- custom js file link -->
<script src="js/script.js"></script>

</body>
</html>
