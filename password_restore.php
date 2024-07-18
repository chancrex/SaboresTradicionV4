<?php
include 'components/connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
      // Continuar con el proceso de recuperación de contraseña
      $email = $_POST['email'];
      $email = filter_var($email, FILTER_SANITIZE_EMAIL);

      $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
      $select_user->execute([$email]);
      $row = $select_user->fetch(PDO::FETCH_ASSOC);

      if ($select_user->rowCount() > 0) {
         $password_hash = $row['password'];
         
         // Aquí no podemos desencriptar la contraseña, así que enviaremos una nueva contraseña generada
         $new_password = bin2hex(random_bytes(4)); // Nueva contraseña de 8 caracteres
         $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

         // Actualizar la contraseña en la base de datos
         $update_password = $conn->prepare("UPDATE `users` SET password = ? WHERE email = ?");
         $update_password->execute([$new_password_hash, $email]);

         // Enviar correo de recuperación de contraseña con PHPMailer
         $mail = new PHPMailer(true);

         try {
            // Configuraciones del servidor SMTP
            $mail->isSMTP();
            $mail->SMTPDebug = 0; // Desactivar depuración detallada
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'saborestradicion24@gmail.com'; // Tu correo SMTP
            $mail->Password = 'ptantwfvmqrhuamz'; // Tu contraseña de aplicación SMTP
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Remitente y destinatario
            $mail->setFrom('saborestradicion24@gmail.com', 'Sabores Tradicion');
            $mail->addAddress($email, $row['name']);

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de Contraseña';
            $mail->Body = "
               <html>
               <head>
               <title>Recuperación de Contraseña</title>
               </head>
               <body>
               <h1>Hola, " . $row['name'] . "</h1>
               <p>Recibimos una solicitud para recuperar tu contraseña.</p>
               <p>Tu nueva contraseña es la siguiente:</p>
               <p><strong>$new_password</strong></p>
               <p>Te recomendamos cambiar esta contraseña después de iniciar sesión.</p>
               <p>Si no solicitaste este correo, por favor ignóralo.</p>
               </body>
               </html>
               ";
            $mail->send();
            $message[] = '¡Correo enviado! Revisa tu bandeja de entrada.';
         } catch (Exception $e) {
            $message[] = "¡Error al enviar el correo! Error: {$mail->ErrorInfo}";
         }
      } else {
         $message[] = '¡Correo no registrado!';
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
   <title>Recuperar Contraseña</title>

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
      <h3>Recuperar Contraseña</h3>
      <input type="email" name="email" required placeholder="Introduce tu correo electrónico" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Recuperar" name="submit" class="btn">
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
