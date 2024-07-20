<?php

include 'components/connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit();
}

$message = []; // Inicializar $message como un array

if (isset($_POST['add_promotion'])) {

    $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
    $discount = filter_var($_POST['discount'], FILTER_SANITIZE_STRING);
    $valid_until = filter_var($_POST['valid_until'], FILTER_SANITIZE_STRING);

    $select_promotions = $conn->prepare("SELECT * FROM `promotions` WHERE product_id = ?");
    $select_promotions->execute([$product_id]);

    if ($select_promotions->rowCount() > 0) {
        $message[] = '¡Ya existe una promoción para este producto!';
    } else {
        $insert_promotion = $conn->prepare("INSERT INTO `promotions` (product_id, discount, valid_until) VALUES (?,?,?)");
        $insert_promotion->execute([$product_id, $discount, $valid_until]);

        // Enviar correo a todos los usuarios sobre la nueva promoción
        $select_users = $conn->prepare("SELECT email FROM `users`");
        $select_users->execute();
        $users = $select_users->fetchAll(PDO::FETCH_ASSOC);

        $product_name = $conn->prepare("SELECT name FROM `products` WHERE id = ?");
        $product_name->execute([$product_id]);
        $product = $product_name->fetch(PDO::FETCH_ASSOC);

        $mail = new PHPMailer(true);

        try {
            // Configuraciones del servidor SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'saborestradicion24@gmail.com'; // Tu correo SMTP
            $mail->Password = 'ifibfmozvxylvelz'; // Tu contraseña de aplicación SMTP
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            // Remitente
            $mail->setFrom('saborestradicion24@gmail.com', 'Sabores Tradicion');

            foreach ($users as $user) {
                $mail->addAddress($user['email']);
            }

            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = 'Nueva promoción disponible';
            $mail->Body = "
                <html>
                <head>
                <title>Promoción</title>
                </head>
                <body>
                <h1>¡Nueva promoción en Sabores Tradición!</h1>
                <p>Se ha aplicado un descuento del <strong>$discount%</strong> sobre el producto <strong>" . htmlspecialchars($product['name']) . "</strong>.</p>
                <p>La promoción es válida hasta el <strong>" . htmlspecialchars($valid_until) . "</strong>.</p>
                <p>&#127881; ¡No te lo pierdas!</p>
                </body>
                </html>
            ";

            $mail->send();
        } catch (Exception $e) {
            $message[] = 'Error al enviar el correo de notificación.';
        }

        $message[] = '¡Nueva promoción añadida!';
    }
}

if (isset($_POST['update_promotion'])) {

    $promotion_id = filter_var($_POST['promotion_id'], FILTER_SANITIZE_NUMBER_INT);
    $discount = filter_var($_POST['discount'], FILTER_SANITIZE_STRING);
    $valid_until = filter_var($_POST['valid_until'], FILTER_SANITIZE_STRING);

    // Obtener la promoción antes de actualizar
    $select_promotion = $conn->prepare("SELECT product_id FROM `promotions` WHERE id = ?");
    $select_promotion->execute([$promotion_id]);
    $promotion = $select_promotion->fetch(PDO::FETCH_ASSOC);

    $update_promotion = $conn->prepare("UPDATE `promotions` SET discount = ?, valid_until = ? WHERE id = ?");
    $update_promotion->execute([$discount, $valid_until, $promotion_id]);

    // Obtener el nombre del producto
    $product_name = $conn->prepare("SELECT name FROM `products` WHERE id = ?");
    $product_name->execute([$promotion['product_id']]);
    $product = $product_name->fetch(PDO::FETCH_ASSOC);

    // Enviar correo a todos los usuarios sobre la actualización de la promoción
    $select_users = $conn->prepare("SELECT email FROM `users`");
    $select_users->execute();
    $users = $select_users->fetchAll(PDO::FETCH_ASSOC);

    $mail = new PHPMailer(true);

    try {
        // Configuraciones del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'saborestradicion24@gmail.com'; // Tu correo SMTP
        $mail->Password = 'ifibfmozvxylvelz'; // Tu contraseña de aplicación SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Remitente
        $mail->setFrom('saborestradicion24@gmail.com', 'Sabores Tradicion');

        foreach ($users as $user) {
            $mail->addAddress($user['email']);
        }

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Actualización de promoción';
        $mail->Body = "
            <html>
            <head>
            <title>Actualización de Promoción</title>
            </head>
            <body>
            <h1>¡Promoción actualizada en Sabores Tradición!</h1>
            <p>La promoción sobre el producto <strong>" . htmlspecialchars($product['name']) . "</strong> ha sido actualizada.</p>
            <p>Descuento actualizado: <strong>$discount%</strong>.</p>
            <p>La promoción es válida hasta el <strong>" . htmlspecialchars($valid_until) . "</strong>.</p>
            <p>&#127881; ¡Aprovecha la nueva oferta!</p>
            </body>
            </html>
        ";

        $mail->send();
    } catch (Exception $e) {
        $message[] = 'Error al enviar el correo de notificación.';
    }

    $message[] = '¡Promoción actualizada!';
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    if (!empty($delete_id)) {
        $delete_promotion = $conn->prepare("DELETE FROM `promotions` WHERE id = ?");
        $delete_promotion->execute([$delete_id]);

        $message[] = '¡Promoción eliminada!';
    } else {
        $message[] = '¡ID de promoción no válido!';
    }

    header('location:promotions.php');
    exit();
}

// Obtener los productos que no tienen promociones
$select_products = $conn->prepare("
    SELECT p.id, p.name 
    FROM `products` p
    LEFT JOIN `promotions` pr ON p.id = pr.product_id
    WHERE pr.product_id IS NULL
");
$select_products->execute();
$products = $select_products->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Gestión de promociones</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
      .modal {
         display: none;
         position: fixed;
         z-index: 1;
         padding-top: 100px;
         left: 0;
         top: 0;
         width: 100%;
         height: 100%;
         overflow: auto;
         background-color: rgb(0,0,0);
         background-color: rgba(0,0,0,0.4);
      }

      .modal-content {
         background-color: #fefefe;
         margin: auto;
         padding: 20px;
         border: 1px solid #888;
         width: 50%;
         box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);
         text-align: center;
      }

      .close {
         color: #aaa;
         float: right;
         font-size: 28px;
         font-weight: bold;
      }

      .close:hover,
      .close:focus {
         color: black;
         text-decoration: none;
         cursor: pointer;
      }

      .modal h3 {
         font-size: 24px;
      }

      .modal .box {
         font-size: 18px;
         margin-bottom: 15px;
      }

      .promotion-box {
         margin-bottom: 30px;
         padding: 15px;
         background-color: #f9f9f9;
         margin-bottom: 20px;
         border: 1px solid #ddd;
         border-radius: 5px;
      }

      .promotion-box h {
         font-size: 22px;
         margin-bottom: 10px;
      }

      .promotion-box p {
         font-size: 18px;
         margin-bottom: 5px;
      }

      .promotion-box .btn-group a {
         font-size: 18px;
         padding: 10px 20px;
         margin: 5px;
         text-decoration: none;
         border-radius: 5px;
      }

      .promotion-box .update-btn {
         background-color: #4CAF50;
         color: white;
      }

      .promotion-box .delete-btn {
         background-color: #f44336;
         color: white;
      }

      .promotion-box .update-btn:hover,
      .promotion-box .delete-btn:hover {
         opacity: 0.8;
      }

      form select {
         margin-bottom: 15px; /* Espacio entre la lista desplegable y los demás campos */
      }
   </style>

</head>
<body>
   
<?php include 'components/admin_header.php'; ?>

<section class="promotion-container">

   <h1 class="heading">Gestión de promociones</h1>

   <?php
   if (!empty($message)) {
      foreach ($message as $msg) {
         echo '<div class="message">' . $msg . '</div>';
      }
   }
   ?>

   <div class="box-container">

      <!-- Formulario para añadir promoción -->
      <form action="" method="POST" class="promotion-box">
         <h1>Añadir nueva promoción</h1>
         <select name="product_id" required>
            <option value="" disabled selected>Seleccione un producto</option>
            <?php
            foreach ($products as $product) {
               echo '<option value="' . htmlspecialchars($product['id']) . '">' . htmlspecialchars($product['name']) . '</option>';
            }
            ?>
         </select>
         <input type="number" name="discount" placeholder="Descuento (%)" step="0.01" required>
         <input type="date" name="valid_until" required>
         <input type="submit" name="add_promotion" value="Añadir promoción" class="btn">
      </form>

      <!-- Listado de promociones -->
      <?php
      $select_promotions = $conn->prepare("SELECT * FROM `promotions`");
      $select_promotions->execute();
      $promotions = $select_promotions->fetchAll(PDO::FETCH_ASSOC);

      foreach ($promotions as $promotion) {
         $product_id = $promotion['product_id'];
         $product_name = $conn->prepare("SELECT name FROM `products` WHERE id = ?");
         $product_name->execute([$product_id]);
         $product = $product_name->fetch(PDO::FETCH_ASSOC);
      ?>

      <div class="promotion-box">
         <h><?php echo htmlspecialchars($product['name']); ?></h>
         <p>Descuento: <?php echo htmlspecialchars($promotion['discount']); ?>%</p>
         <p>Válida hasta: <?php echo htmlspecialchars($promotion['valid_until']); ?></p>
         <div class="btn-group">
            <a href="promotions.php?delete=<?php echo $promotion['id']; ?>" class="btn delete-btn" onclick="return confirm('¿Está seguro de que desea eliminar esta promoción?');">Eliminar</a>
            <a href="update_promotion.php?id=<?php echo $promotion['id']; ?>" class="btn update-btn">Actualizar</a>
         </div>
      </div>

      <?php
      }
      ?>

   </div>

</section>

<script>
   // Mostrar modal en caso de mensaje
   document.addEventListener("DOMContentLoaded", function() {
      var messages = document.querySelectorAll(".message");
      if (messages.length > 0) {
         var modal = document.createElement("div");
         modal.className = "modal";
         var modalContent = document.createElement("div");
         modalContent.className = "modal-content";
         var close = document.createElement("span");
         close.className = "close";
         close.innerHTML = "&times;";
         modalContent.appendChild(close);

         messages.forEach(function(message) {
            var messageBox = document.createElement("div");
            messageBox.className = "box";
            messageBox.innerHTML = message.innerHTML;
            modalContent.appendChild(messageBox);
         });

         document.body.appendChild(modal);
         modal.style.display = "block";

         close.onclick = function() {
            modal.style.display = "none";
         };

         setTimeout(function() {
            modal.style.display = "none";
         }, 2000);
      }
   });
</script>

</body>
</html>
