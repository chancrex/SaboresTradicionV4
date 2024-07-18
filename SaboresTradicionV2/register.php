<?php

include 'components/connect.php';
require 'vendor/autoload.php'; // Asegúrate de que esta ruta es correcta

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

if(isset($_POST['submit'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $pass = sha1($_POST['pass']);
   $pass = filter_var($pass, FILTER_SANITIZE_STRING);
   $cpass = sha1($_POST['cpass']);
   $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

   $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? OR number = ?");
   $select_user->execute([$email, $number]);
   $row = $select_user->fetch(PDO::FETCH_ASSOC);

   if($select_user->rowCount() > 0){
      $message[] = '¡El correo electrónico o el número ya existe!';
   }else{
      if($pass != $cpass){
         $message[] = '¡Confirme  la contraseña, no coincide!';
      }else{
         $insert_user = $conn->prepare("INSERT INTO `users`(name, email, number, password) VALUES(?,?,?,?)");
         $insert_user->execute([$name, $email, $number, $cpass]);
         $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
         $select_user->execute([$email, $pass]);
         $row = $select_user->fetch(PDO::FETCH_ASSOC);
         if($select_user->rowCount() > 0){
            $_SESSION['user_id'] = $row['id'];

            // Enviar correo de bienvenida con PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Configuraciones del servidor SMTP
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'saborestradicion@gmail.com'; // Tu correo SMTP
                $mail->Password = 'tucontraseña'; // Tu contraseña SMTP
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Remitente y destinatario
                $mail->setFrom('saborestradicion@gmail.com', 'Sabores Tradicion');
                $mail->addAddress($email, $name);

                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = 'Bienvenido a nuestro sitio';
                $mail->Body    = "
                <html>
                <head>
                <title>Bienvenido</title>
                </head>
                <body>
                <h1>Gracias por registrarte, $name!</h1>
                <p>Tu nombre de usuario es: $name</p>
                <p>Tu contraseña es: " . $_POST['pass'] . "</p>
                </body>
                </html>
                ";

                $mail->send();
                echo 'Registro exitoso y correo enviado.';
            } catch (Exception $e) {
                echo "Registro exitoso pero fallo en el envío del correo. Error: {$mail->ErrorInfo}";
            }

            header('location:index.php');
         }
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
   <title>Registro</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
<script src="js/chatbot.js"></script>
<!-- header section starts  -->
<?php include 'components/user_header.php'; ?>
<!-- header section ends -->

<section class="form-container">

   <form action="" method="post">
      <h3>Regístrate ahora</h3>
      <input type="text" name="name" required placeholder="introduzca su nombre" class="box" maxlength="50">
      <input type="email" name="email" required placeholder="Introduce tu correo electrónico" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="number" name="number" required placeholder="Introduce tu número de contacto" class="box" min="0" max="9999999999" maxlength="10">
      <input type="password" name="pass" required placeholder="Ingresa tu contraseña" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="cpass" required placeholder="confirmar la contraseña" class="box" maxlength="50" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="Registrarse " name="submit" class="btn">
      <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión ahora</a></p>
   </form>

</section>

<?php include 'components/footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>
