<?php
header('X-Frame-Options: DENY');
include 'components/connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configura la cookie de sesión con HttpOnly y SameSite=Lax
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), $_COOKIE[session_name()], 0, '/', '', true, true); // Agrega el último true para HttpOnly
    session_regenerate_id(true);
    setcookie(session_name(), session_id(), 0, '/', '', true, true); // Agrega SameSite=Lax
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sabores Tradición</title>

    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="hero">
    <div class="swiper hero-slider">
        <div class="swiper-wrapper">
            <div class="swiper-slide slide">
                <div class="content">
                    <span>reserva online</span>
                    <h3>Platos Tradicion</h3>
                    <a href="menu.php" class="btn">ver menú</a>
                </div>
                <div class="image">
                    <img src="images/home-img-1.png" alt="">
                </div>
            </div>
            <div class="swiper-slide slide">
                <div class="content">
                    <span>reserva online</span>
                    <h3>hamburguesa con queso</h3>
                    <a href="menu.php" class="btn">ver menú</a>
                </div>
                <div class="image">
                    <img src="images/home-img-2.png" alt="">
                </div>
            </div>
            <div class="swiper-slide slide">
                <div class="content">
                    <span>reserva online</span>
                    <h3>pollo asado</h3>
                    <a href="menu.php" class="btn">ver menú</a>
                </div>
                <div class="image">
                    <img src="images/home-img-3.png" alt="">
                </div>
            </div>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<section class="category">
    <h1 class="title">categoría de comida</h1>
    <div class="box-container">
        <a href="category.php?category=Entradas" class="box">
            <img src="images/picarones.png" alt="">
            <h3>Entradas</h3>
        </a>
        <a href="category.php?category=Plato principal" class="box">
            <img src="images/dish-2.png" alt="">
            <h3>platos principales</h3>
        </a>
        <a href="category.php?category=Bebidas" class="box">
            <img src="images/jugo_naranja.png" alt="">
            <h3>Bebidas</h3>
        </a>
        <a href="category.php?category=Postres" class="box">
            <img src="images/helado.png" alt="">
            <h3>Postres</h3>
        </a>
    </div>
</section>

<section class="products">
    <h1 class="title">Nuestros platos</h1>
    <div class="box-container">
        <?php
        $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 6");
        $select_products->execute();
        if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <form action="" method="post" class="box">
                    <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
                    <input type="hidden" name="name" value="<?= $fetch_products['name']; ?>">
                    <input type="hidden" name="price" value="<?= $fetch_products['price']; ?>">
                    <input type="hidden" name="image" value="<?= $fetch_products['image']; ?>">
                    <!-- <a href="" class="fas fa-eye"></a> -->
                    <!-- <button type="submit" class="fas fa-shopping-cart" name="add_to_cart"></button> -->
                    <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                    <a href="category.php?category=<?= $fetch_products['category']; ?>" class="cat"><?= $fetch_products['category']; ?></a>
                    <div class="name"><?= $fetch_products['name']; ?></div>
                    <!-- <div class="flex">
                        <div class="price"><span>S/.</span><?= $fetch_products['price']; ?></div>
                        <input type="number" name="qty" class="qty" min="1" max="99" value="1" maxlength="2">
                    </div> -->
                </form>
                <?php
            }
        } else {
            echo '<p class="empty">¡Aún no se han añadido productos!</p>';
        }
        ?>
    </div>
</section>


<?php include 'components/footer.php'; ?>

<script src="js/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>

<script>
var swiper = new Swiper(".hero-slider", {
    loop: true,
    grabCursor: true,
    effect: "flip",
    pagination: {
        el: ".swiper-pagination",
        clickable: true,
    },
});
</script>

</body>
</html>
