<?php

include 'components/connect.php';

if (isset($_GET['id'])) {
    $recipe_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $select_recipe = $conn->prepare("SELECT * FROM `recipes` WHERE id = ?");
    $select_recipe->execute([$recipe_id]);
    $recipe = $select_recipe->fetch(PDO::FETCH_ASSOC);
    echo json_encode($recipe);
}

?>
