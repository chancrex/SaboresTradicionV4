<?php
// Incluir archivo de conexión a la base de datos
include 'components/connect.php';

// Función para imprimir gráfico de torta
function print_chart($id, $labels, $data, $background_colors) {
    ?>
    <div class="chart-item">
        <h3><?= $id ?></h3>
        <canvas id="<?= str_replace(' ', '_', $id) ?>"></canvas>
        <script>
            var ctx_<?= str_replace(' ', '_', $id) ?> = document.getElementById('<?= str_replace(' ', '_', $id) ?>').getContext('2d');
            var chart_<?= str_replace(' ', '_', $id) ?> = new Chart(ctx_<?= str_replace(' ', '_', $id) ?>, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        label: '<?= $id ?>',
                        data: <?= json_encode($data) ?>,
                        backgroundColor: <?= json_encode($background_colors) ?>,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    var dataset = tooltipItem.dataset;
                                    var total = dataset.data.reduce(function(sum, value) {
                                        return sum + value;
                                    }, 0);
                                    var currentValue = dataset.data[tooltipItem.dataIndex];
                                    var percentage = ((currentValue / total) * 100).toFixed(2);
                                    return tooltipItem.label + ': ' + percentage + '%';
                                }
                            }
                        }
                    }
                }
            });
        </script>
    </div>
    <?php
}

// Función para interpolar colores entre rojo y verde claro
function interpolate_color($val) {
    $colors = [
        1 => [255, 87, 51], // Rojo (#FF5733)
        2 => [255, 195, 0], // Naranja-Amarillo (#FFC300)
        3 => [255, 255, 0], // Amarillo (#FFFF00)
        4 => [173, 255, 47], // Amarillo-Verde (#ADFF2F)
        5 => [50, 205, 50],  // Verde claro (#32CD32)
    ];

    if (array_key_exists($val, $colors)) {
        return 'rgb(' . implode(',', $colors[$val]) . ')';
    }

    return '#CCCCCC'; // Color gris por defecto si no hay coincidencia
}

// Función para generar datos de gráfico
function generate_chart_data($column_name) {
    global $conn;

    // Consulta para contar registros por valor en la columna especificada
    $query = "SELECT COUNT(*) AS count, $column_name FROM encuestas GROUP BY $column_name";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $labels = [];
    $data = [];
    $background_colors = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $labels[] = $row[$column_name];
        $data[] = $row['count'];
        $background_colors[] = interpolate_color($row[$column_name]);
    }

    return [
        'labels' => $labels,
        'data' => $data,
        'background_colors' => $background_colors
    ];
}

// Generar datos para cada gráfico
$data_servicio = generate_chart_data('satisfaccion_servicio');
$data_menu = generate_chart_data('satisfaccion_menu');
$data_precios = generate_chart_data('satisfaccion_precios');
$data_servicio_restaurante = generate_chart_data('satisfaccion_servicio_restaurante');
$data_recomendacion_restaurante = generate_chart_data('recomendacion_restaurante');

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuestas - Gráficos de Torta</title>

    <!-- Incluir Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="../css/admin_style.css">
    <!-- Estilos CSS -->
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .charts-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .chart-item {
            width: 30%;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            vertical-align: top; /* Alineación vertical superior */
        }
        canvas {
            max-width: 100%;
            height: auto;
        }
        /* Estilos específicos para los gráficos */
        .chart-item h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.5em;
            text-align: center;
        }
    </style>

    <!-- Biblioteca Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<!-- Encabezado -->
<?php include 'components/admin_header.php'; ?>

<!-- Contenido de la página -->
<div class="container">
<h1 class="heading">Visualización de encuestas</h1>

    <!-- Contenedor de gráficos -->
    <div class="charts-container">
        <!-- Gráficos de torta -->
        <?php
        // Imprimir gráficos
        print_chart('Satisfacción Servicio', $data_servicio['labels'], $data_servicio['data'], $data_servicio['background_colors']);
        print_chart('Satisfacción Menú', $data_menu['labels'], $data_menu['data'], $data_menu['background_colors']);
        print_chart('Satisfacción Precios', $data_precios['labels'], $data_precios['data'], $data_precios['background_colors']);
        print_chart('Satisfacción Servicio Restaurante', $data_servicio_restaurante['labels'], $data_servicio_restaurante['data'], $data_servicio_restaurante['background_colors']);
        print_chart('Recomendación Restaurante', $data_recomendacion_restaurante['labels'], $data_recomendacion_restaurante['data'], $data_recomendacion_restaurante['background_colors']);
        ?>
    </div>
</div>

<!-- Pie de página -->

</body>
</html>
