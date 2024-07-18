<?php
session_start();
include 'components/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';

function verificarAforo($conn, $fecha, $hora, $num_personas, $reserva_id = null) {
    try {
        // Obtener la hora sin los minutos
        $hora_inicio = date('H:00:00', strtotime($hora));
        $hora_fin = date('H:59:59', strtotime($hora));

        // Añadir log
        error_log("Fecha: $fecha, Hora Inicio: $hora_inicio, Hora Fin: $hora_fin, Reserva ID: $reserva_id");

        $query = $conn->prepare("SELECT SUM(num_personas) as total_personas FROM reservas WHERE fecha = :fecha AND hora BETWEEN :hora_inicio AND :hora_fin AND estado = 'pendiente'" . ($reserva_id ? " AND id != :reserva_id" : ""));
        $query->bindParam(':fecha', $fecha);
        $query->bindParam(':hora_inicio', $hora_inicio);
        $query->bindParam(':hora_fin', $hora_fin);
        if ($reserva_id) {
            $query->bindParam(':reserva_id', $reserva_id, PDO::PARAM_INT);
        }
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $total_personas = $row['total_personas'] ?? 0; // Si es null, asignar 0

        // Añadir log
        error_log("Fecha: $fecha, Hora: $hora, Total Personas Existentes: $total_personas, Nuevas Personas: $num_personas");

        return ($total_personas + $num_personas) > 6;
    } catch (PDOException $e) {
        error_log("Error en verificarAforo: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $reserva_id = $_POST['id'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $num_personas = $_POST['num_personas'];

    $error_message = '';

    // Validar la fecha
    $fecha_actual = date('Y-m-d');
    if (strtotime($fecha) <= strtotime($fecha_actual)) {
        $error_message = "Error: La fecha de reserva debe ser posterior a hoy.";
    }

    // Validar la hora
    $hora_minima = strtotime("11:00");
    $hora_maxima = strtotime("16:00");
    $hora_reserva = strtotime($hora);
    if ($hora_reserva < $hora_minima || $hora_reserva > $hora_maxima) {
        $error_message = "Error: La hora de reserva debe ser entre las 11:00 y las 16:00.";
    }

    // Validar el aforo
    if (verificarAforo($conn, $fecha, $hora, $num_personas, $reserva_id)) {
        $error_message = "La cantidad de personas supera el aforo permitido para esa hora. 😢";
    }

    if (empty($error_message)) {
        try {
            $stmt = $conn->prepare("UPDATE reservas SET fecha = ?, hora = ?, num_personas = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$fecha, $hora, $num_personas, $reserva_id, $user_id]);
            $_SESSION['success_message'] = '¡Se actualizó la reserva! 😊';
            header('Location: historial_reservas.php');
            exit();
        } catch (PDOException $e) {
            echo 'Error: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $_SESSION['error_message'] = $error_message;
        header('Location: historial_reservas.php');
        exit();
    }
}

include 'components/add_cart.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'confirm') {
    $reserva_id = $_POST['id'];
    // Lógica para confirmar la reserva, por ejemplo, cambiar el estado a 'confirmada'
    try {
        $stmt = $conn->prepare("UPDATE reservas SET estado = 'confirmada' WHERE id = ? AND user_id = ?");
        $stmt->execute([$reserva_id, $user_id]);
        $_SESSION['success_message'] = '¡Reserva confirmada! 😊';
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

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
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
            background-color: #f44336;
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
        .success-modal-content {
            background-color: #4CAF50;
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
        .pasada {
            color: red;
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
        <div class="modal-content success-modal-content">
            <span class="close" onclick="document.getElementById('successModal').style.display='none'">&times;</span>
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

<?php if (isset($error_message)): ?>
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('errorModal').style.display='none'">&times;</span>
            <p><?= $error_message ?></p>
        </div>
    </div>
    <script>
        // Show the modal
        document.getElementById('errorModal').style.display = 'block';
    </script>
<?php endif; ?>

<div class="info-message">
    <p>Solo se podrán actualizar las reservas que estén por lo menos 5 horas antes de la fecha registrada inicialmente. 
        Para que su reserva se complete debe cancelar o pagar su confirmación de asistencia. Si desea cancelar una reserva tendrá que llamar al administrador.</p>
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
                    <?php $editable = isEditable($reserva['fecha'], $reserva['hora'], $reserva['estado']); ?>
                    <tr class="<?= strtolower($reserva['estado']) === 'pasada' ? 'pasada' : '' ?>">
                        <form action="" method="post">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($reserva['id']) ?>">
                            <td>
                                <input type="date" name="fecha" value="<?= htmlspecialchars($reserva['fecha']) ?>" <?= $editable ? '' : 'readonly' ?> required>
                            </td>
                            <td>
                                <input type="time" name="hora" value="<?= htmlspecialchars($reserva['hora']) ?>" <?= $editable ? '' : 'readonly' ?> required>
                            </td>
                            <td>
                                <input type="number" name="num_personas" value="<?= htmlspecialchars($reserva['num_personas']) ?>" <?= $editable ? '' : 'readonly' ?> required min="1" max="10" maxlength="2" oninput="validateNumberOfPersons(this)">
                            </td>
                            <td><?= htmlspecialchars($reserva['estado']) ?></td>
                            <td>
                                <?php if ($editable): ?>
                                    <button type="submit" name="action" value="update" class="btn-pencil">Actualizar</button>
                                    <button type="submit" name="add_to_cart" value="confirm" class="btn-pencil">Pagar/Confirmar Reserva</button>
                                <?php else: ?>
                                    <button type="button" class="btn-pencil" disabled>Confirmada/Terminada</button>
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
<script>
function validateNumberOfPersons(input) {
    if (input.value.length > 2) {
        input.value = input.value.slice(0, 2);
    }
    if (input.value > 10) {
        input.value = 10;
    }
    if (input.value < 1) {
        input.value = 1;
    }
}
</script>
<script src="js/script.js"></script>
</body>
</html>
