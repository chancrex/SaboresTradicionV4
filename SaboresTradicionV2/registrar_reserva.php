<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

$fecha_actual = date('Y-m-d');

?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Reservas</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<script src="js/chatbot.js"></script>
<?php include 'components/user_header.php'; ?>

<div class="heading">
<h3><i class="fas fa-clock"></i> Registrar Reserva</h3>
   <!-- <p><a href="index.php">INICIO</a> <span> / Reservas</span></p> -->
</div>



<!-- sección reservas  -->
<style>
   /* Estilos para el contenedor principal */
/* Estilos para el contenedor principal */
.container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: linear-gradient(to right, #ff6f61, #fb9843);
}

/* Estilos para la imagen */
.image {
    border: 6px solid #00ffff;
    border-radius: 15px;
    max-width: 400px;
    margin-right: 20px;
 
}

/* Estilos para el contenedor del formulario */
.form-container {
    width: 100%;
    max-width: 600px;
    background-color: #fff;
    padding: 30px;
 
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    border: 6px solid #00ffff;
    border-radius: 20px;
}

/* Estilos para el formulario */
form {
    display: left;
    flex-direction: column;
}

/* Estilos para las etiquetas */
label {
    font-weight: bold;
    color: #f6f6f6;
    font-size: 20px;
    margin-bottom: 10px;
}

/* Estilos para los campos de entrada */
input[type="text"],
input[type="tel"],
input[type="date"],
input[type="time"],
select,

textarea {
    
    width: 100%;
    padding: 15px;
    margin-bottom: 20px;
    border: 2px solid #ccc;
    border-radius: 15px;
    font-size: 18px;
    background-color: #f6f6f6;
    color: black;
    transition: border-color 0.3s, box-shadow 0.3s;
}
input[type="number" i] {

    width: 100%;
    padding: 15px;
    margin-bottom: 20px;
    border: 2px solid #ccc;
    border-radius: 15px;
    font-size: 18px;
    background-color: #f6f6f6;
    color: #333;
    transition: border-color 0.3s, box-shadow 0.3s; 
}

/* Estilos para los campos de entrada cuando están en foco */
input[type="text"]:focus,
input[type="tel"]:focus,
input[type="date"]:focus,
input[type="time"]:focus,
select:focus,
textarea:focus {
    border-color: #FF5722;
    box-shadow: 0 0 12px rgba(255, 87, 34, 0.5);
}

/* Estilos para el botón de envío */
input[type="submit"] {
    background: linear-gradient(135deg, #ffcc00, #ff6600);
   color: #fff;
   border: none;
   padding: 15px 30px;
   font-size: 1.8rem;
   border-radius: 5px;
   cursor: pointer;
   transition: background 0.5s, color 0.5s, transform 0.3s, letter-spacing 0.3s, box-shadow 0.3s;
   text-transform: uppercase;
   text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
   box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}


input[type="submit"] {
   background: linear-gradient(135deg, #56a836, #912991);
   color: #fff;
   transform: scale(1.05) rotate(10deg);
   letter-spacing: 0.3rem;
   box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
   animation: shake 0.5s ease infinite;
}

@keyframes shake {
   0% { transform: translateX(0); }
   20% { transform: translateX(-5px) rotate(-5deg); }
   40% { transform: translateX(5px) rotate(5deg); }
   60% { transform: translateX(-5px) rotate(-5deg); }
   80% { transform: translateX(5px) rotate(5deg); }
   100% { transform: translateX(0); }
}

/* Estilos para el botón de envío al pasar el mouse */
input[type="submit"]:hover {
    background-color: #333;
    
    transform: scale(1.05);
}
/* Estilos para el formulario */
.form-container {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Estilos para el formulario cuando se pasa el mouse */
.form-container:hover {
    transform: translateY(-5px); /* Mover hacia arriba 5px */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Sombra sutil */
}

/* Estilos para el modal */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 10px;
    text-align: center;
    z-index: 10000;
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

/* Asegurar que el formulario se vea detrás del modal */
.form-container {
    position: relative;
    z-index: 1;
}


</style>

<section class="reserva">
<div class="container">
        <img src="images/reserva.png" alt="Imagen de restaurante" class="image">
        <div class="form-container">
            
        <form action="procesar_reserva.php" method="POST">
    <label for="nombre"><i class="fas fa-user"></i> Nombre:</label>
    <input type="text" id="nombre" name="nombre" required>

    <label for="telefono">Teléfono:</label>
         <input type="tel" id="telefono" name="telefono" pattern="[0-9]{1,9}" maxlength="9" required oninput="validateNumber(this)">

    <label for="fecha"><i class="fas fa-calendar"></i> Fecha de Reserva:</label>
    <input type="date" id="fecha" name="fecha" required min="<?php echo $fecha_actual; ?>">

    <label for="hora"><i class="fas fa-clock"></i> Hora de la reserva:</label>
    <input type="time" id="hora" name="hora" min="11:00" max="16:00" required>

    <label for="mesa"><i class="fas fa-chair"></i> Elegir Mesa:</label>
    <select id="mesa" name="mesa">
        <option value="Mesa 1">Mesa 1</option>
        <option value="Mesa 2">Mesa 2</option>
        <option value="Mesa 3">Mesa 3</option>
        <option value="Mesa 4">Mesa 4</option>
        <option value="Mesa 5">Mesa 5</option>
        <option value="Mesa 6">Mesa 6</option>
        <option value="Mesa 7">Mesa 7</option>
        <option value="Mesa 8">Mesa 8</option>
        <option value="Mesa 9">Mesa 9</option>
        <!-- Agrega más opciones de mesa si es necesario -->
    </select>

    <label for="num_personas"><i class="fas fa-users"></i> Número de Personas:</label>
    <input type="number" id="num_personas" name="num_personas" required value="1">

    <label for="comentarios"><i class="fas fa-comment"></i> Comentario:</label>
    <textarea id="comentarios" name="comentarios" maxlength="150" rows="4" cols="50"></textarea>

    <input type="submit" value="Enviar Reserva">
</form>

            <div class="mensaje">
      
   </div>
        </div>
</div>
<div id="errorModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('errorModal').style.display='none'">&times;</span>
        <p id="errorMessage"><?php echo $error_message; ?></p>
    </div>
</div>

</section>
<script>
function validateNumber(input) {
    input.value = input.value.replace(/[^0-9]/g, '').slice(0, 9);
}

// Mostrar el modal de error si hay un mensaje de error
<?php if (!empty($error_message)) : ?>
    document.getElementById('errorModal').style.display = 'block';
<?php endif; ?>
</script>


</body>
</html>