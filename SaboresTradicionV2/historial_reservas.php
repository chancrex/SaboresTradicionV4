<?php
session_start();
include 'components/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reserva_id = $_POST['id'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $num_personas = $_POST['num_personas'];

    try {
        $stmt = $conn->prepare("UPDATE reservas SET fecha = ?, hora = ?, num_personas = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$fecha, $hora, $num_personas, $reserva_id, $user_id]);
        $_SESSION['success_message'] = '¡Se actualizó la reserva! 😊';
        header('Location: historial_reservas.php');
        exit();
    } catch (PDOException $e) {
        echo 'Error: ' . htmlspecialchars($e->getMessage());
    }
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

try {
    $stmt = $conn->prepare("SELECT * FROM reservas WHERE user_id = ? ORDER BY fecha DESC");
    $stmt->execute([$user_id]);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
    exit();
}

function isEditable($fecha, $hora, $estado) {
    if ($estado !== 'pendiente') {
        return false;
    }
    
    $fecha_hora_reserva = new DateTime("$fecha $hora");
    $ahora = new DateTime();
    $interval = $ahora->diff($fecha_hora_reserva);
    
    return $fecha_hora_reserva > $ahora && $interval->h >= 5;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Reservas</title>
    <link rel="stylesheet" href="css/style.css">
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
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #4CAF50;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
            color: #f1f1f1;
            border-radius: 15px;
            font-size: 1.2em;
        }
        .info-message {
            text-align: center;
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .info-message p {
            font-size: 1.5em;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="heading">
   <h3><i class='fas fa-align-right' style='font-size:36px'></i> Historial de Reservas</h3>
   <p><span>Pasadas</span> <span> / Pendientes</span></p>
</div>

<?php if ($success_message): ?>
    <div id="successModal" class="modal">
        <div class="modal-content">
            <p><?= $success_message ?></p>
        </div>
    </div>
    <script>
        // Show the modal
        document.getElementById('successModal').style.display = 'block';
        // Hide the modal after 3 seconds
        setTimeout(function() {
            document.getElementById('successModal').style.display = 'none';
        }, 3000);
    </script>
<?php endif; ?>

<div class="info-message">
    <p>Solo se podrán actualizar las reservas que estén por lo menos 5 horas antes de la fecha registrada inicialmente. Si desea cancelar una reserva tendrá que llamar al administrador.</p>
</div>

<section class="reservas-historial">
    <h1>Tus Reservas</h1>
    <?php if (count($reservas) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Personas</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <form action="" method="post">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($reserva['id']) ?>">
                            <?php $editable = isEditable($reserva['fecha'], $reserva['hora'], $reserva['estado']); ?>
                            <td>
                                <input type="date" name="fecha" value="<?= htmlspecialchars($reserva['fecha']) ?>" min="<?= date('Y-m-d') ?>" <?= $editable ? '' : 'readonly' ?> required>
                            </td>
                            <td>
                                <input type="time" name="hora" value="<?= htmlspecialchars($reserva['hora']) ?>" <?= $editable ? '' : 'readonly' ?> required>
                            </td>
                            <td>
                                <input type="number" name="num_personas" value="<?= htmlspecialchars($reserva['num_personas']) ?>" <?= $editable ? '' : 'readonly' ?> required>
                            </td>
                            <td><?= htmlspecialchars($reserva['estado']) ?></td>
                            <td>
                                <?php if ($editable): ?>
                                    <button type="submit" class="btn-pencil">Guardar</button>
                                <?php else: ?>
                                    <button type="button" class="btn-pencil" disabled>Guardar</button>
                                <?php endif; ?>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No has realizado ninguna reserva.</p>
    <?php endif; ?>
</section>

</body>
</html>
