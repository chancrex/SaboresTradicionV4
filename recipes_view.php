<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Recetas</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <!-- custom modal css -->
   <style>
      .modal {
         display: none;
         position: fixed;
         z-index: 1;
         left: 0;
         top: 0;
         width: 100%;
         height: 100%;
         overflow: auto;
         background-color: rgba(0,0,0,0.6);
      }

      .modal-content {
         background-color: #fff;
         margin: 5% auto;
         padding: 20px;
         border: 1px solid #888;
         width: 80%;
         border-radius: 10px;
         box-shadow: 0 5px 15px rgba(0,0,0,0.3);
         color: #333;
         font-size: 3.6rem; /* Tamaño de letra triplicado */
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

      .modal-content h2 {
         margin-top: 0;
         color: #333;
         text-align: center;
         font-size: 7.5rem; /* Tamaño de letra triplicado */
      }

      .modal-content img {
         width: 50%; /* Tamaño de imagen reducido */
         height: auto;
         border-radius: 10px;
         margin-bottom: 20px;
         display: block;
         margin-left: auto;
         margin-right: auto;
      }

      .modal-content p {
         color: #555;
         line-height: 1.6;
         font-size: 3.6rem; /* Tamaño de letra triplicado */
      }

      .modal-content strong {
         color: #333;
      }

      .modal-content .list {
         list-style-type: none;
         padding: 0;
         color: #333;
         font-size: 3.6rem; /* Tamaño de letra triplicado */
      }

      .modal-content .list li {
         margin: .5rem 0;
         padding: .5rem;
         background: #f9f9f9;
         border-left: 3px solid #007bff;
         border-radius: 5px;
      }

      .products .box-container {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
         gap: 1.5rem;
      }

      .products .box {
         padding: 2rem;
         border: var(--border);
         box-shadow: var(--box-shadow);
         border-radius: .5rem;
         text-align: center;
         background-color: var(--white);
      }

      .products .box img {
         height: 25rem;
         border-radius: 10px;
      }

      .products .box .name {
         font-size: 2.5rem;
         color: var(--black);
         margin-top: 1rem;
         overflow: hidden;
         text-overflow: ellipsis;
         white-space: nowrap;
      }

      .products .box .details {
         text-align: left;
         margin-top: 1rem;
         font-size: 1.5rem;
         color: #333;
      }

      .products .box .details p {
         margin: .5rem 0;
         line-height: 1.8;
      }

   </style>

</head>
<body>

<!-- header section starts  -->
<?php include 'components/user_header.php'; ?>
<!-- header section ends -->

<div class="heading">
   <h3>Nuestras Recetas</h3>
</div>

<!-- recipes section starts  -->

<section class="products">

   <h1 class="title">RECETAS DE NUESTROS PLATOS</h1>

   <div class="box-container">

      <?php
         $select_recipes = $conn->prepare("SELECT r.*, p.name AS product_name, p.image AS product_image, p.category AS product_category FROM `recipes` r JOIN `products` p ON r.product_id = p.id");
         $select_recipes->execute();
         if($select_recipes->rowCount() > 0){
            while($fetch_recipes = $select_recipes->fetch(PDO::FETCH_ASSOC)){
               $product_name = json_encode($fetch_recipes['product_name']);
               $ingredients = json_encode($fetch_recipes['ingredients']);
               $steps = json_encode($fetch_recipes['steps']);
               $tips = json_encode($fetch_recipes['tips']);
               $image = json_encode('uploaded_img/' . $fetch_recipes['product_image']);
      ?>
      <div class="box">
         <input type="hidden" name="rid" value="<?= $fetch_recipes['id']; ?>">
         <input type="hidden" name="product_name" value="<?= $fetch_recipes['product_name']; ?>">
         <input type="hidden" name="ingredients" value="<?= $fetch_recipes['ingredients']; ?>">
         <input type="hidden" name="steps" value="<?= $fetch_recipes['steps']; ?>">
         <input type="hidden" name="tips" value="<?= $fetch_recipes['tips']; ?>">
         <a href="javascript:void(0);" class="fas fa-list" onclick='openModal(<?= $product_name; ?>, <?= $ingredients; ?>, <?= $steps; ?>, <?= $tips; ?>, <?= $image; ?>)'></a>
         <img src="uploaded_img/<?= $fetch_recipes['product_image']; ?>" alt="">
         <div class="name"><?= $fetch_recipes['product_name']; ?></div>
         <div class="details">
            <p><strong>Ingredientes:</strong> <?= nl2br(str_replace(',', '<br>• ', '• ' . $fetch_recipes['ingredients'])); ?></p>
            <p><strong>Pasos:</strong> <?= nl2br('1. ' . str_replace(PHP_EOL, PHP_EOL . '2. ', $fetch_recipes['steps'])); ?></p>
            <p><strong>Tips:</strong> <?= nl2br('• ' . str_replace(PHP_EOL, PHP_EOL . '• ', $fetch_recipes['tips'])); ?></p>
         </div>
      </div>
      <?php
            }
         }else{
            echo '<p class="empty">¡Aún no se han añadido recetas!</p>';
         }
      ?>

   </div>

</section>

<!-- modal structure -->
<div id="myModal" class="modal">
   <div class="modal-content">
      <span class="close">&times;</span>
      <div id="modalDetails"></div>
   </div>
</div>

<!-- footer section starts  -->
<?php include 'components/footer.php'; ?>
<!-- footer section ends -->

<!-- custom js file link  -->
<script src="js/script.js"></script>

<!-- modal script -->
<script>
   // Get the modal
   var modal = document.getElementById("myModal");

   // Get the <span> element that closes the modal
   var span = document.getElementsByClassName("close")[0];

   // Function to open the modal with recipe details
   function openModal(name, ingredients, steps, tips, image) {
      console.log('Modal opened with:', { name, ingredients, steps, tips, image }); // Debug log
      var modalDetails = document.getElementById("modalDetails");

      // Split steps and tips into arrays
      var stepsArray = steps.split(/\r?\n/); // Split by carriage return and/or newline

      var stepsHtml = '';
      for (var i = 0; i < stepsArray.length; i++) {
         stepsHtml += `<li>Paso N°${i + 1}: ${stepsArray[i]}</li>`;
      }

      var tipsHtml = '';
      if (tips) {
         var tipsArray = tips.split(/\r?\n/); // Split tips by carriage return and/or newline
         for (var j = 0; j < tipsArray.length; j++) {
            tipsHtml += `<li>• ${tipsArray[j]}</li>`;
         }
      }

      modalDetails.innerHTML = `
         <h2>${name}</h2>
         <img src=${image} alt="${name}">
         <p><strong>Ingredientes:</strong></p>
         <ul class="list">${ingredients.replace(/,/g, '</li><li>• ').replace(/^/, '<li>• ').replace(/$/, '</li>')}</ul>
         <p><strong>Pasos:</strong></p>
         <ul class="list">${stepsHtml}</ul>
         ${tipsHtml ? `<p><strong>Tips:</strong></p><ul class="list">${tipsHtml}</ul>` : ''}
      `;

      modal.style.display = "block";
   }

   // When the user clicks on <span> (x), close the modal
   span.onclick = function() {
      modal.style.display = "none";
   }

   // When the user clicks anywhere outside of the modal, close it
   window.onclick = function(event) {
      if (event.target == modal) {
         modal.style.display = "none";
      }
   }
</script>

</body>
</html>
