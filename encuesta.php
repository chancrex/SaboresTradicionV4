<?php

include 'components/connect.php';

session_start();

// Verificar si la sesión del usuario está establecida
if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
}

$user_nombre = '';

if($user_id != ''){
   try {
      // Preparar la consulta para obtener el nombre del usuario
      $query = $conn->prepare("SELECT name FROM id22374849_food_db.users WHERE id = :id");
      $query->bindParam(':id', $user_id, PDO::PARAM_INT);
      $query->execute();
      
      if($query->rowCount() > 0){
         $row = $query->fetch(PDO::FETCH_ASSOC);
         $user_nombre = $row['name'];
      } else {
         echo "No user found with id: $user_id <br>";
      }
   } catch (PDOException $e) {
      // echo "Error: " . $e->getMessage() . "<br>";
   }
} else {
   // echo "User ID is empty. <br>";
}

$fecha_actual = date('Y-m-d');

// Variable para manejar el mensaje de error
$error_message = '';
$success_message = '';

// Procesar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Aquí procesarías los datos del formulario de reserva y también las respuestas del cuestionario de satisfacción
    // Por ejemplo:
    $satisfaccion_servicio = $_POST['satisfaccion_servicio'];
    $satisfaccion_menu = $_POST['satisfaccion_menu'];
    $satisfaccion_precios = $_POST['satisfaccion_precios'];
    $satisfaccion_servicio_restaurante = $_POST['satisfaccion_servicio_restaurante'];
    $recomendacion_restaurante = $_POST['recomendacion_restaurante'];
    $comentarios_adicionales = $_POST['comentarios_adicionales'];

    // Guardar los datos en la base de datos
    try {
        // Preparar la consulta para insertar la encuesta
        $insert_query = $conn->prepare("INSERT INTO encuestas (user_id, satisfaccion_servicio, satisfaccion_menu, satisfaccion_precios, satisfaccion_servicio_restaurante, recomendacion_restaurante, comentarios_adicionales) VALUES (:user_id, :satisfaccion_servicio, :satisfaccion_menu, :satisfaccion_precios, :satisfaccion_servicio_restaurante, :recomendacion_restaurante, :comentarios_adicionales)");
        $insert_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $insert_query->bindParam(':satisfaccion_servicio', $satisfaccion_servicio, PDO::PARAM_INT);
        $insert_query->bindParam(':satisfaccion_menu', $satisfaccion_menu, PDO::PARAM_INT);
        $insert_query->bindParam(':satisfaccion_precios', $satisfaccion_precios, PDO::PARAM_INT);
        $insert_query->bindParam(':satisfaccion_servicio_restaurante', $satisfaccion_servicio_restaurante, PDO::PARAM_INT);
        $insert_query->bindParam(':recomendacion_restaurante', $recomendacion_restaurante, PDO::PARAM_INT);
        $insert_query->bindParam(':comentarios_adicionales', $comentarios_adicionales, PDO::PARAM_STR);
        
        // Ejecutar la consulta
        $insert_query->execute();

        // Mensaje de éxito después de enviar la encuesta
        $success_message = "¡Gracias por dejarnos tu opinión!";

        // Redirigir después de 2 segundos
        echo '<script>
                setTimeout(function() {
                    window.location.href = "index.php";
                }, 2000);
              </script>';
    } catch (PDOException $e) {
        $error_message = "Error al guardar la encuesta: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Encuesta de Satisfacción</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   
   <style>
      /* Estilos adicionales para centrar y mejorar el formulario */

      body {
         font-size: 18px;
         background-color: #1e1e1e;
         color: #fff;
      }

      .reserva-encuesta {
         display: flex;
         justify-content: center; /* Centra el contenido horizontalmente */
         align-items: flex-start; /* Alinea los elementos verticalmente al inicio */
      }

      .image-container {
         flex: 0 0 auto; /* Evita que la imagen afecte al tamaño del formulario */
         margin-right: 20px; /* Ajusta el margen derecho para separar la imagen del formulario */
         width: 200px; /* Ajusta el ancho de la imagen según tus necesidades */
      }

      .form-container {
         flex: 1; /* Hace que el formulario ocupe el espacio restante */
         max-width: 600px; /* Máximo ancho del formulario */
         background-color: #333;
         padding: 30px;
         box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
         border: 6px solid #00ffff;
         border-radius: 20px;
         text-align: center;
         color: #fff;
      }

      .form-container label {
         font-weight: bold;
         font-size: 1.5rem;
      }

      .form-container h2 {
         font-size: 4rem;
         margin-bottom: 20px;
         color: #00ffff;
      }

      .form-container h3 {
         font-size: 2rem;
         margin-bottom: 20px;
         color: #00ffff;
      }

      .radio-group {
         display: flex;
         justify-content: center;
         gap: 20px;
         margin-top: 10px;
      }

      .radio-group label {
         font-size: 2rem;
      }

      .radio-group input[type="radio"] {
         margin-right: 5px;
      }

      .form-container textarea {
         width: 100%;
         height: 100px;
         margin-top: 20px;
         padding: 10px;
         border-radius: 10px;
         border: 1px solid #ccc;
         font-size: 1.2rem;
      }

      .form-container input[type="submit"] {
         font-size: 1.8rem;
         padding: 15px 30px;
         background-color: #00ffff;
         border: none;
         border-radius: 10px;
         cursor: pointer;
         transition: background-color 0.3s, transform 0.3s;
      }

      .form-container input[type="submit"]:hover {
         background-color: #00cccc;
         transform: scale(1.05);
      }

      .form-container input[type="submit"]:active {
         transform: scale(0.95);
      }
   </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="heading">
   <h3><i class="fa-regular fa-face-grin-stars"></i> Encuesta de satisfacción</h3>
</div>

<section class="reserva-encuesta">
   <div class="form-container">
      <?php if (!empty($error_message)): ?>
         <div id="errorModal" class="modal" style="display: block;">
            <div class="modal-content">
               <span class="close" onclick="document.getElementById('errorModal').style.display='none'">&times;</span>
               <p id="errorMessage"><?php echo $error_message; ?></p>
            </div>
         </div>
      <?php elseif (!empty($success_message)): ?>
         <div id="successModal" class="modal" style="display: block;">
            <div class="modal-content" style="background-color: green; color: white;">
               <p id="successMessage"><?php echo $success_message; ?></p>
            </div>
         </div>
         <script>
            setTimeout(function() {
               window.location.href = "index.php";
            }, 2000); // Redirige después de 2 segundos
         </script>
      <?php endif; ?>
      <div class="image-container">
         <img src="images/encuesta.png" alt="Imagen de restaurante" class="image">
      </div>
      <div class="form-content">
         <h2>¡Califícanos!</h2>
         <h3>Teniendo en cuenta que 1 es para nada satisfecho, y 5 muy satisfecho. Responda las siguientes preguntas:</h3>
         <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <label><i class="fas fa-star"></i> ¿Qué tan satisfecho se encuentra con el servicio en general? 🤔</label><br>
            <div class="radio-group">
               <input type="radio" id="1" name="satisfaccion_servicio" value="1">
               <label for="1">1</label>
               <input type="radio" id="2" name="satisfaccion_servicio" value="2">
               <label for="2">2</label>
               <input type="radio" id="3" name="satisfaccion_servicio" value="3">
               <label for="3">3</label>
               <input type="radio" id="4" name="satisfaccion_servicio" value="4">
               <label for="4">4</label>
               <input type="radio" id="5" name="satisfaccion_servicio" value="5">
               <label for="5">5</label>
            </div><br><br>

            <label><i class="fas fa-star"></i> ¿Qué tan satisfecho se encuentra con el menú ofrecido? 🤔</label><br>
            <div class="radio-group">
               <input type="radio" id="1" name="satisfaccion_menu" value="1">
               <label for="1">1</label>
               <input type="radio" id="2" name="satisfaccion_menu" value="2">
               <label for="2">2</label>
               <input type="radio" id="3" name="satisfaccion_menu" value="3">
               <label for="3">3</label>
               <input type="radio" id="4" name="satisfaccion_menu" value="4">
               <label for="4">4</label>
               <input type="radio" id="5" name="satisfaccion_menu" value="5">
               <label for="5">5</label>
            </div><br><br>

            <label><i class="fas fa-star"></i> ¿Qué tan satisfecho se encuentra con los precios ofrecidos? 🤔</label><br>
            <div class="radio-group">
               <input type="radio" id="1" name="satisfaccion_precios" value="1">
               <label for="1">1</label>
               <input type="radio" id="2" name="satisfaccion_precios" value="2">
               <label for="2">2</label>
               <input type="radio" id="3" name="satisfaccion_precios" value="3">
               <label for="3">3</label>
               <input type="radio" id="4" name="satisfaccion_precios" value="4">
               <label for="4">4</label>
               <input type="radio" id="5" name="satisfaccion_precios" value="5">
               <label for="5">5</label>
            </div><br><br>

            <label><i class="fas fa-star"></i> ¿Qué tan satisfecho se encuentra con el servicio brindado por el restaurante? 🤔</label><br>
            <div class="radio-group">
               <input type="radio" id="1" name="satisfaccion_servicio_restaurante" value="1">
               <label for="1">1</label>
               <input type="radio" id="2" name="satisfaccion_servicio_restaurante" value="2">
               <label for="2">2</label>
               <input type="radio" id="3" name="satisfaccion_servicio_restaurante" value="3">
               <label for="3">3</label>
               <input type="radio" id="4" name="satisfaccion_servicio_restaurante" value="4">
               <label for="4">4</label>
               <input type="radio" id="5" name="satisfaccion_servicio_restaurante" value="5">
               <label for="5">5</label>
            </div><br><br>

            <label><i class="fas fa-heart"></i> ¿Recomendaría nuestro restaurante a sus amigos? 🤔</label><br>
            <div class="radio-group">
               <input type="radio" id="1" name="recomendacion_restaurante" value="1">
               <label for="1">1</label>
               <input type="radio" id="2" name="recomendacion_restaurante" value="2">
               <label for="2">2</label>
               <input type="radio" id="3" name="recomendacion_restaurante" value="3">
               <label for="3">3</label>
               <input type="radio" id="4" name="recomendacion_restaurante" value="4">
               <label for="4">4</label>
               <input type="radio" id="5" name="recomendacion_restaurante" value="5">
               <label for="5">5</label>
            </div><br><br>

            <label for="comentarios_adicionales"><i class="fa-regular fa-comment-dots"></i> Comentarios adicionales (opcional):</label><br>
            <textarea id="comentarios_adicionales" name="comentarios_adicionales" placeholder="Déjanos tus comentarios aquí..."></textarea><br><br>

            <input type="submit" value="Enviar">
         </form>
      </div>
   </div>
</section>

<?php include 'components/footer.php'; ?>

</body>
</html>
