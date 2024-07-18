<?php

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      header('location:login.php');
   }else{

      $reserva_id = $_POST['id'];
      $reserva_id = filter_var($reserva_id, FILTER_SANITIZE_STRING);

      $fecha = $_POST['fecha'];
      $fecha = filter_var($fecha, FILTER_SANITIZE_STRING);
      $hora = $_POST['hora'];
      $hora = filter_var($hora, FILTER_SANITIZE_STRING);


      header('location:cart.php');
      $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? and reserva_id=?");
      $check_cart_numbers->execute([$user_id, $reserva_id]);

      if($check_cart_numbers->rowCount() > 0){
         $message[] = '¡Ya agregado al carrito!';
      }else{
         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, reserva_id, price, fecha, hora ) VALUES(?,?,20,?,?)");
         $insert_cart->execute([$user_id, $reserva_id, $fecha, $hora]);
         $message[] = '¡Añadido al carrito!';
         
      }

   }

}

?>