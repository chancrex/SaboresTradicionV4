<?php

include 'components/connect.php';


session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
   header('location:index.php');
};

?>


<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  // Agregar mensajes de depuración
  error_log('Datos recibidos: ' . print_r($data, true));

  if (isset($data['action']) && $data['action'] === 'delete_cart') {
    try {
      
      $user_id = $data['user_id'];
      $reserva_id = $data['reserva_id'];

      // Eliminar datos de la tabla cart y actualización del estado de reservas
      $placeholders = rtrim(str_repeat('?,', count($reserva_id)), ',');
      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ? and reserva_id IN ($placeholders)");
      $params = array_merge([$user_id], $reserva_id);
      $delete_cart->execute($params);
            
      $update_reservas = $conn->prepare("UPDATE `reservas` SET estado = 'confirmada' WHERE user_id = ? AND id IN ($placeholders)");
      $update_reservas->execute($params);
    
      // Comprobación adicional
      $rowCount = $delete_cart->rowCount();
      error_log('Filas eliminadas: ' . $rowCount);

      if ($rowCount > 0) {
        echo json_encode(['success' => true]);
      } else {
        echo json_encode(['success' => false, 'message' => 'No se eliminaron filas']);
      }
    } catch (Exception $e) {
      error_log('Error de la base de datos: ' . $e->getMessage());
      echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
  } else {
    error_log('Acción no válida o no especificada');
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Verificar</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <script src="https://www.paypal.com/sdk/js?client-id=AUEf8PKS-w32CVKx4KHR0pX9HvvziydRA09x6RXxr50Ks7KNOdMvY7xCF3GBcfd3a_fECGFToHBr6pZe&currency=USD"></script>

</head>
<body>

<!-- header section starts  -->
<?php include 'components/user_header.php'; ?>
<!-- header section ends -->

<div class="heading">
   <h3>Verificar</h3>
   <p><a href="index.php">INICIO</a> <span> / Verificar</span></p>
</div>

<section class="checkout">

   <h1 class="title">Resumen de la reserva</h1>
   
<form action="" method="post">

   <div class="cart-items">
      <h3>Precio de la reserva</h3>
      <?php
         $grand_total = 0;         
         $reserva_id = [];
         $cart_items[] = '';
         $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $select_cart->execute([$user_id]);

         if($select_cart->rowCount() > 0){
            while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){
               $cart_items[] = $fetch_cart['price'];
               $total_products = implode(',', $cart_items);
               $reserva_id[] = $fetch_cart['reserva_id'];
               $grand_total += ($fetch_cart['price']);
      ?>

                  <style>
                  .items .price {
                     font-size: 1.5em; /* Ajusta el tamaño de la fuente según tus necesidades */
                     display: flex;
                     justify-content: space-between; /* Distribuye el espacio entre el precio y los detalles */                     
                     padding: 10px; /* Opcional: añade relleno alrededor del contenedor */
                  }
                  .items .price span {
                     color: var(--yellow); /* Color del texto amarillo */
                     font-weight: bold; /* Hace que el precio sea negrita */
                  }
                  .items .details {
                     text-align: right; /* Alinea el texto a la derecha */
                  }
                  </style>

               <div class="items">
                  <p class="price">
                  <span>S/. <?= $fetch_cart['price']; ?></span>
                  <span class="details">
                     Fecha: <?= $fetch_cart['fecha']; ?><br>
                     Hora: <?= $fetch_cart['hora']; ?>
                  </span>
                  </p>
               </div>

      <?php
            }
         }else{
            echo '<p class="empty">Tu carrito esta vacío</p>';
         }
      ?>
      <p class="grand-total"><span class="name">Total :</span><span class="price">S/. <?= $grand_total; ?></span></p>
      <a href="cart.php" class="btn">ver Reserva</a>
   </div>

   <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
   <input type="hidden" name="name" value="<?= $fetch_profile['name'] ?>">
   <input type="hidden" name="number" value="<?= $fetch_profile['number'] ?>">
   <input type="hidden" name="email" value="<?= $fetch_profile['email'] ?>">
   <input type="hidden" name="address" value="<?= $fetch_profile['address'] ?>">

   <div class="user-info">
      <h3>Tu información</h3>
      <p><i class="fas fa-user"></i><span><?= $fetch_profile['name'] ?></span></p>
      <p><i class="fas fa-phone"></i><span><?= $fetch_profile['number'] ?></span></p>
      <p><i class="fas fa-envelope"></i><span><?= $fetch_profile['email'] ?></span></p>
      <a href="update_profile.php" class="btn">Actualizar información</a>
 
      <br></br>

      <label>
      <input type="submit" name="submit" value="paypal" checked>
      
      </label>
      
      <div id="paypal-button-container"></div>
      
      <script>
         paypal.Buttons({
            
            style:{
               
               
               shape:   'pill',
               label:   'pay'
            },
            createOrder: function(data, actions){
               return actions.order.create({
                  purchase_units: [{
                     amount:  {
                        value:  <?php echo $grand_total; ?>
                     }
                  }]
               });
            },

            onApprove:  function(data, actions){
               return actions.order.capture().then(function(detalles) {
        console.log('Detalles de la transacción:', detalles);
                  
        // Verificar el estado de la transacción
        if (detalles.status === 'COMPLETED') {

          // Realizar solicitud al servidor para eliminar datos de la tabla cart
          fetch('', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              action: 'delete_cart',
              user_id: <?php echo json_encode($user_id); ?>,
              reserva_id: <?php echo json_encode($reserva_id); ?>
            })
          })
          .then(response => {
            console.log('Respuesta del servidor:', response);
            if (!response.ok) {
              throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
          })
          .then(data => {
            console.log('Datos del servidor:', data);
            if (data.success) {
               
               alert('¡Pago realizado con éxito por ' + detalles.payer.name.given_name);
               return new Promise(resolve => setTimeout(resolve, 4000));

            } else {
              alert('Error al eliminar el carrito. Inténtalo de nuevo.');
              console.error('Error del servidor:', data.message);
            }
          })
         .then(() => {
            
            window.location.href = 'historial_reservas.php';
         })

          .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar el pago. Inténtalo de nuevo.');
          });
        } else {
          alert('El pago no se completó correctamente.');
        }
      });
               
            },

            onCancel:   function(data){
               alert("Pago Cancelado");
               console.log(data);
            }
         }).render('#paypal-button-container');
                  
         
      </script>


   </div>

</form>
   
</section>


<!-- footer section starts  -->
<?php include 'components/footer.php'; ?>
<!-- footer section ends -->

</body>
</html>