<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:index.php');
};

if(isset($_POST['delete'])){
   $cart_id = $_POST['cart_id'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$cart_id]);
   $message[] = '¡Reserva eliminada!';
}

if(isset($_POST['delete_all'])){
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   // header('location:cart.php');
   $message[] = '¡Eliminado todo del carrito!';
}

$grand_total = 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Carrito</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>

<!-- header section starts  -->
<?php include 'components/user_header.php'; ?>
<!-- header section ends -->

<div class="heading">
   <h3>Pago de Reserva</h3>
   <p><a href="index.php">INICIO</a> <span> / Carrito</span></p>
</div>

<!-- shopping cart section starts  -->

<section class="products">

   <h1 class="title">Tu reserva</h1>

   <div class="box-container">

      <?php
         $grand_total = 0;
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id=?");
         $select_cart->execute([$user_id]);
         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
      ?>
      <form action="" method="post" class="box">
         <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
         <!--<input type="hidden" name="reserva_id" value="<?= $fetch_cart['reserva_id']; ?>">-->
         <!--<a href="quick_view.php?pid=<?= $fetch_cart['pid']; ?>" class="fas fa-eye"></a>-->
         <button type="submit" class="fas fa-times" name="delete" onclick="return confirm('¿Eliminar este elemento?');"></button>
         <!--<img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">-->
         <img src="uploaded_img/logo_reserva.png" alt="">
         <!--<div class="name"><?= $fetch_cart['name']; ?></div>-->
         <div class="flex">
            <div class="price"><span>S/. </span><?= $fetch_cart['price']; ?></div>
            <div class="text"><span>Fecha: </span><?= $fetch_cart['fecha']; ?></div>
        
         </div>

         <style>
            .text {
               font-size: 1.5em; /* Ajusta el tamaño de la fuente según tus necesidades */
               display: block; /* Asegura que cada div esté en su propia línea */
               margin-bottom: 10px; /* Opcional: Añade un margen inferior para espacio extra */
               text-align: right; /* Alinea el texto a la derecha */
            }
         </style>

         <div class="text"><span>Hora: </span><?= $fetch_cart['hora']; ?></div>


         <div class="sub-total"> sub total : <span>S/. <?= $sub_total = ($fetch_cart['price']); ?></span> </div>
      </form>
      <?php
               $grand_total += $sub_total;
            }
         }else{
            echo '<p class="empty">No tienes reservas por pagar</p>';
         }
      ?>

   </div>

   <div class="cart-total">
      <p>Total de la reserva : <span>S/. <?= $grand_total; ?></span></p>
      <a href="checkout.php" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>">Pasar por la caja</a>
   </div>

   <div class="more-btn">
      <form action="" method="post">
         <button type="submit" class="delete-btn <?= ($grand_total > 1)?'':'disabled'; ?>" name="delete_all" onclick="return confirm('¿Eliminar todo del carrito?');">Eliminar todo</button>
      </form>
      <a href="historial_reservas.php" class="btn">Actualizar Reserva</a>
   </div>

</section>

<!-- shopping cart section ends -->

<!-- footer section starts  -->
<?php include 'components/footer.php'; ?>
<!-- footer section ends -->

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>