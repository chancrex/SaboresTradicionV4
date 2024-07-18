<?php

include 'components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
    exit();
}

if (isset($_POST['add_recipe'])) {
    $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
    $ingredients = isset($_POST['ingredients']) ? filter_var(implode(',', $_POST['ingredients']), FILTER_SANITIZE_STRING) : '';
    $steps = isset($_POST['steps']) ? filter_var(implode(PHP_EOL, $_POST['steps']), FILTER_SANITIZE_STRING) : '';
    $tips = isset($_POST['tips']) ? filter_var(implode(PHP_EOL, $_POST['tips']), FILTER_SANITIZE_STRING) : '';

    if (isset($_POST['ingredients']) && count($_POST['ingredients']) < 3 || isset($_POST['steps']) && count($_POST['steps']) < 3) {
        $message[] = '¡La receta debe tener al menos 3 ingredientes y 3 pasos!';
    } else {
        $insert_recipe = $conn->prepare("INSERT INTO `recipes` (product_id, ingredients, steps, tips) VALUES (?, ?, ?, ?)");
        $insert_recipe->execute([$product_id, $ingredients, $steps, $tips]);
        $message[] = '¡Nueva receta añadida!';
    }
}

if (isset($_POST['update_recipe'])) {
    $recipe_id = filter_var($_POST['recipe_id'], FILTER_SANITIZE_NUMBER_INT);
    $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
    $ingredients = isset($_POST['ingredients_update']) ? filter_var(implode(',', $_POST['ingredients_update']), FILTER_SANITIZE_STRING) : '';
    $steps = isset($_POST['steps_update']) ? filter_var(implode(PHP_EOL, $_POST['steps_update']), FILTER_SANITIZE_STRING) : '';
    $tips = isset($_POST['tips_update']) ? filter_var(implode(PHP_EOL, $_POST['tips_update']), FILTER_SANITIZE_STRING) : '';

    if (isset($_POST['ingredients_update']) && count($_POST['ingredients_update']) < 3 || isset($_POST['steps_update']) && count($_POST['steps_update']) < 3) {
        $message[] = '¡La receta debe tener al menos 3 ingredientes y 3 pasos!';
    } else {
        $update_recipe = $conn->prepare("UPDATE `recipes` SET product_id = ?, ingredients = ?, steps = ?, tips = ? WHERE id = ?");
        $update_recipe->execute([$product_id, $ingredients, $steps, $tips, $recipe_id]);
        $message[] = '¡Receta actualizada!';
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_recipe = $conn->prepare("DELETE FROM `recipes` WHERE id = ?");
    $delete_recipe->execute([$delete_id]);
    $message[] = '¡Receta eliminada!';
    header('location:recetas_y_tips.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Gestión de Recetas</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

   <style>
       .form-container {
           display: flex;
           justify-content: center;
           align-items: center;
           min-height: 100vh;
       }

       .add-recipes, .update-recipes {
           background: #fff;
           padding: 20px;
           border-radius: 10px;
           box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
           width: 400px;
       }

       .add-recipes h3, .update-recipes h3 {
           text-align: center;
           font-size: 1.5em;
           margin-bottom: 20px;
       }

       .add-recipes form .box, .update-recipes form .box {
           display: block;
           width: 100%;
           padding: 10px;
           margin-bottom: 10px;
           border: 1px solid #ccc;
           border-radius: 5px;
       }

       .add-recipes form .btn, .update-recipes form .btn {
           display: block;
           width: 100%;
           padding: 10px;
           background: #007bff;
           color: #fff;
           border: none;
           border-radius: 5px;
           cursor: pointer;
           font-size: 1em;
       }

       .add-recipes .add-btn, .update-recipes .add-btn {
           display: inline-block;
           padding: 10px;
           background: #007bff;
           color: #fff;
           border: none;
           border-radius: 5px;
           cursor: pointer;
           font-size: 1em;
           margin-top: 10px;
           margin-right: 5px;
       }

       .add-recipes .remove-btn, .update-recipes .remove-btn {
           display: inline-block;
           padding: 10px;
           background: #dc3545;
           color: #fff;
           border: none;
           border-radius: 5px;
           cursor: pointer;
           font-size: 1em;
           margin-top: 10px;
           margin-right: 5px;
       }

       .show-recipes .box {
           background: #fff;
           padding: 20px;
           border-radius: 10px;
           box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
           margin-bottom: 20px;
       }

       .modal {
           display: none;
           position: fixed;
           z-index: 1;
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
           margin: 15% auto;
           padding: 20px;
           border: 1px solid #888;
           width: 80%;
           max-width: 500px;
           border-radius: 10px;
       }

       .close {
           color: #aaa;
           float: right;
           font-size: 28px;
           font-weight: bold;
       }

       .close:hover, .close:focus {
           color: black;
           text-decoration: none;
           cursor: pointer;
       }
   </style>

</head>
<body>

<?php include 'components/admin_header.php' ?>

<div class="form-container">

<!-- add recipes section starts  -->

<section class="add-recipes">
<h1 class="heading">GESTIÓN DE RECETAS</h1>

   <form action="" method="POST" onsubmit="return validateForm()">
      <h3>Registro de Recetas</h3>
      <select name="product_id" class="box" required>
         <option value="" disabled selected>Selecciona un producto --</option>
         <?php
            $select_products = $conn->prepare("SELECT p.* FROM `products` p LEFT JOIN `recipes` r ON p.id = r.product_id WHERE r.product_id IS NULL");
            $select_products->execute();
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                echo '<option value="'.$fetch_products['id'].'">'.$fetch_products['name'].'</option>';
            }
         ?>
      </select>
      
      <div class="ingredient-box">
         <label>Ingredientes</label>
         <button type="button" class="add-btn" onclick="addIngredient()">Añadir Ingrediente</button>
         <div id="ingredients-container">
            <div class="ingredient-item">
               <input type="text" name="ingredients[]" class="box" placeholder="Ingrediente" required>
               <button type="button" class="remove-btn" onclick="removeIngredient(this)">Quitar Ingrediente</button>
            </div>
         </div>
      </div>

      <div class="step-box">
         <label>Pasos</label>
         <button type="button" class="add-btn" onclick="addStep()">Añadir Paso</button>
         <div id="steps-container">
            <div class="step-item">
               <input type="text" name="steps[]" class="box" placeholder="Paso" required>
               <button type="button" class="remove-btn" onclick="removeStep(this)">Quitar Paso</button>
            </div>
         </div>
      </div>

      <div class="tip-box">
         <label>Tips</label>
         <button type="button" class="add-btn" onclick="addTip()">Añadir Tip</button>
         <div id="tips-container">
            <div class="tip-item">
               <input type="text" name="tips[]" class="box" placeholder="Tip">
               <button type="button" class="remove-btn" onclick="removeTip(this)">Quitar Tip</button>
            </div>
         </div>
      </div>

      <input type="submit" value="Agregar receta" name="add_recipe" class="btn">
   </form>

</section>

</div>

<section class="show-recipes" style="padding-top: 0;">

   <div class="box-container">

   <?php
      $show_recipes = $conn->prepare("SELECT r.*, p.name as product_name FROM `recipes` r JOIN `products` p ON r.product_id = p.id");
      $show_recipes->execute();
      if ($show_recipes->rowCount() > 0) {
         while ($fetch_recipes = $show_recipes->fetch(PDO::FETCH_ASSOC)) {  
   ?>
   <div class="box">
      <h3>Producto: <?= $fetch_recipes['product_name']; ?></h3>
      <p><strong>Ingredientes:</strong> <?= nl2br(str_replace(',', '<br>• ', '• ' . $fetch_recipes['ingredients'])); ?></p>
      <p><strong>Pasos:</strong> <?= nl2br('1. ' . str_replace(PHP_EOL, PHP_EOL . '2. ', $fetch_recipes['steps'])); ?></p>
      <p><strong>Tips:</strong> <?= nl2br('• ' . str_replace(PHP_EOL, PHP_EOL . '• ', $fetch_recipes['tips'])); ?></p>
      <div class="flex-btn">
         <button class="option-btn" onclick="openModal(<?= $fetch_recipes['id']; ?>)">Actualizar</button>
         <a href="recetas_y_tips.php?delete=<?= $fetch_recipes['id']; ?>" class="delete-btn" onclick="return confirm('¿Eliminar esta receta?');">Eliminar</a>
      </div>
   </div>
   <?php
         }
      } else {
         echo '<p class="empty">¡Aún no se han añadido recetas!</p>';
      }
   ?>

   </div>

</section>

<div id="updateModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <form action="" method="POST" onsubmit="return validateFormUpdate()">
        <input type="hidden" name="recipe_id" id="recipe_id">
        <h3>Actualizar Receta</h3>
        <select name="product_id" id="update_product_id" class="box" required>
           <option value="" disabled selected>Selecciona un producto --</option>
           <?php
              $select_products = $conn->prepare("SELECT p.* FROM `products` p LEFT JOIN `recipes` r ON p.id = r.product_id WHERE r.product_id IS NULL");
              $select_products->execute();
              while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="'.$fetch_products['id'].'">'.$fetch_products['name'].'</option>';
              }
           ?>
        </select>
        
        <div class="ingredient-box">
           <label>Ingredientes</label>
           <button type="button" class="add-btn" onclick="addIngredientUpdate()">Añadir Ingrediente</button>
           <div id="ingredients-container-update">
           </div>
        </div>

        <div class="step-box">
           <label>Pasos</label>
           <button type="button" class="add-btn" onclick="addStepUpdate()">Añadir Paso</button>
           <div id="steps-container-update">
           </div>
        </div>

        <div class="tip-box">
           <label>Tips</label>
           <button type="button" class="add-btn" onclick="addTipUpdate()">Añadir Tip</button>
           <div id="tips-container-update">
           </div>
        </div>

        <input type="submit" value="Actualizar receta" name="update_recipe" class="btn">
    </form>
  </div>
</div>

<script>
function validateForm() {
    const ingredients = document.querySelectorAll('input[name="ingredients[]"]');
    const steps = document.querySelectorAll('input[name="steps[]"]');

    if (ingredients.length < 3 || steps.length < 3) {
        alert('¡La receta debe tener al menos 3 ingredientes y 3 pasos!');
        return false;
    }
    return true;
}

function validateFormUpdate() {
    const ingredients = document.querySelectorAll('input[name="ingredients_update[]"]');
    const steps = document.querySelectorAll('input[name="steps_update[]"]');

    if (ingredients.length < 3 || steps.length < 3) {
        alert('¡La receta debe tener al menos 3 ingredientes y 3 pasos!');
        return false;
    }
    return true;
}

function addIngredient() {
    const container = document.getElementById('ingredients-container');
    const div = document.createElement('div');
    div.className = 'ingredient-item';
    div.innerHTML = `
        <input type="text" name="ingredients[]" class="box" placeholder="Ingrediente" required>
        <button type="button" class="remove-btn" onclick="removeIngredient(this)">Quitar Ingrediente</button>
    `;
    container.appendChild(div);
}

function addStep() {
    const container = document.getElementById('steps-container');
    const div = document.createElement('div');
    div.className = 'step-item';
    div.innerHTML = `
        <input type="text" name="steps[]" class="box" placeholder="Paso" required>
        <button type="button" class="remove-btn" onclick="removeStep(this)">Quitar Paso</button>
    `;
    container.appendChild(div);
}

function addTip() {
    const container = document.getElementById('tips-container');
    const div = document.createElement('div');
    div.className = 'tip-item';
    div.innerHTML = `
        <input type="text" name="tips[]" class="box" placeholder="Tip">
        <button type="button" class="remove-btn" onclick="removeTip(this)">Quitar Tip</button>
    `;
    container.appendChild(div);
}

function addIngredientUpdate() {
    const container = document.getElementById('ingredients-container-update');
    const div = document.createElement('div');
    div.className = 'ingredient-item';
    div.innerHTML = `
        <input type="text" name="ingredients_update[]" class="box" placeholder="Ingrediente" required>
        <button type="button" class="remove-btn" onclick="removeIngredient(this)">Quitar Ingrediente</button>
    `;
    container.appendChild(div);
}

function addStepUpdate() {
    const container = document.getElementById('steps-container-update');
    const div = document.createElement('div');
    div.className = 'step-item';
    div.innerHTML = `
        <input type="text" name="steps_update[]" class="box" placeholder="Paso" required>
        <button type="button" class="remove-btn" onclick="removeStep(this)">Quitar Paso</button>
    `;
    container.appendChild(div);
}

function addTipUpdate() {
    const container = document.getElementById('tips-container-update');
    const div = document.createElement('div');
    div.className = 'tip-item';
    div.innerHTML = `
        <input type="text" name="tips_update[]" class="box" placeholder="Tip">
        <button type="button" class="remove-btn" onclick="removeTip(this)">Quitar Tip</button>
    `;
    container.appendChild(div);
}

function removeIngredient(element) {
    element.parentNode.remove();
}

function removeStep(element) {
    element.parentNode.remove();
}

function removeTip(element) {
    element.parentNode.remove();
}

function openModal(recipeId) {
    const modal = document.getElementById('updateModal');
    modal.style.display = 'block';

    // Fetch recipe data using AJAX and populate the form
    fetch(`fetch_recipe.php?id=${recipeId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('recipe_id').value = data.id;
            document.getElementById('update_product_id').value = data.product_id;

            const ingredientsContainer = document.getElementById('ingredients-container-update');
            ingredientsContainer.innerHTML = '';
            data.ingredients.split(',').forEach(ingredient => {
                const div = document.createElement('div');
                div.className = 'ingredient-item';
                div.innerHTML = `
                    <input type="text" name="ingredients_update[]" class="box" placeholder="Ingrediente" value="${ingredient}" required>
                    <button type="button" class="remove-btn" onclick="removeIngredient(this)">Quitar Ingrediente</button>
                `;
                ingredientsContainer.appendChild(div);
            });

            const stepsContainer = document.getElementById('steps-container-update');
            stepsContainer.innerHTML = '';
            data.steps.split('\n').forEach(step => {
                const div = document.createElement('div');
                div.className = 'step-item';
                div.innerHTML = `
                    <input type="text" name="steps_update[]" class="box" placeholder="Paso" value="${step}" required>
                    <button type="button" class="remove-btn" onclick="removeStep(this)">Quitar Paso</button>
                `;
                stepsContainer.appendChild(div);
            });

            const tipsContainer = document.getElementById('tips-container-update');
            tipsContainer.innerHTML = '';
            data.tips.split('\n').forEach(tip => {
                const div = document.createElement('div');
                div.className = 'tip-item';
                div.innerHTML = `
                    <input type="text" name="tips_update[]" class="box" placeholder="Tip" value="${tip}">
                    <button type="button" class="remove-btn" onclick="removeTip(this)">Quitar Tip</button>
                `;
                tipsContainer.appendChild(div);
            });
        });
}

function closeModal() {
    const modal = document.getElementById('updateModal');
    modal.style.display = 'none';
}

document.addEventListener('input', function (event) {
    if (event.target.name && event.target.name.startsWith('steps[')) {
        let steps = document.querySelectorAll('input[name^="steps["]');
        steps.forEach((step, index) => {
            step.placeholder = `Paso ${index + 1}`;
        });
    }
    if (event.target.name && event.target.name.startsWith('tips[')) {
        let tips = document.querySelectorAll('input[name^="tips["]');
        tips.forEach((tip, index) => {
            tip.placeholder = `Tip ${index + 1}`;
        });
    }
});

window.onclick = function(event) {
    const modal = document.getElementById('updateModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php if (isset($message)) {
   foreach ($message as $msg) {
      echo '<p class="message">' . $msg . '</p>';
   }
} ?>

</body>
</html>
