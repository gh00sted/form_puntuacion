<?php
session_start();
require_once __DIR__ . '/../src/Database.php';

$db = new Database();
$toast_message = '';
$toast_type = '';
$checklist_items = [];
$operarios = [];
$expedientes_filtrados = [];
$mostrar_filtrados = false;
$puntuacion_total = 0;
$operario_filtro = '';

// Obtener items del checklist
try {
    $checklist_items = $db->getChecklistItems();
} catch (Exception $e) {
    $toast_message = 'Aviso: ' . htmlspecialchars($e->getMessage());
    $toast_type = 'warning';
}

// Obtener operarios
try {
    $operarios = $db->getOperarios();
} catch (Exception $e) {
    // Silenciar errores al obtener operarios
}

// Mostrar mensaje de éxito si viene del redirect
if (isset($_GET['success']) && $_GET['success'] === '1') {
    $toast_message = 'Expediente guardado correctamente';
    $toast_type = 'success';
}


// Procesar búsqueda filtrada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buscar') {
    $operario_id = trim($_POST['operario_filtro'] ?? '');
    $fecha_inicio = trim($_POST['fecha_inicio'] ?? '');
    $fecha_fin = trim($_POST['fecha_fin'] ?? '');

    if (!empty($operario_id) && !empty($fecha_inicio) && !empty($fecha_fin)) {
        try {
            $expedientes_filtrados = $db->getExpedientesFiltrados($operario_id, $fecha_inicio, $fecha_fin);
            $mostrar_filtrados = true;
            
            // Obtener nombre del operario para mostrar en resultados
            foreach ($operarios as $op) {
                if ($op['id'] == $operario_id) {
                    $operario_filtro = $op['nombre_completo'];
                    break;
                }
            }
            
            // Calcular puntuación total
            $puntuacion_total = 0;
            foreach ($expedientes_filtrados as $exp) {
                $puntuacion_total += $exp['puntuacion'];
            }
        } catch (Exception $e) {
            $toast_message = 'Error: ' . htmlspecialchars($e->getMessage());
            $toast_type = 'error';
        }
    } else {
        $toast_message = 'Por favor complete todos los campos de búsqueda';
        $toast_type = 'warning';
    }
}

// Procesar formulario de crear expediente (solo si no es búsqueda)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || $_POST['action'] !== 'buscar')) {
    $id_expediente = trim($_POST['id_expediente'] ?? '');
    $operario_nombre = trim($_POST['operario_nombre'] ?? '');
    $fecha_expediente = trim($_POST['fecha_expediente'] ?? '');
    
    // Validaciones
    $errors = [];
    if (empty($id_expediente)) {
        $errors[] = 'El ID de expediente es requerido';
    }
    if (empty($operario_nombre)) {
        $errors[] = 'El operario es requerido';
    }

    if (empty($errors)) {
        try {
            // Obtener o crear el operario
            $operario_id = $db->getOrCreateOperario($operario_nombre);
            
            // Mapeo de nombres de campos a IDs de items del checklist
            $field_to_item = [
                'llego_a_tiempo' => 1,
                'informo_aseg_tram' => 2,
                'fotos_antes' => 3,
                'localizo_averia' => 4,
                'foto_durante' => 5,
                'reparo_primera' => 6,
                'llamo_encargado_1' => 7,
                'justificado' => 8,
                'foto_despues' => 9,
                'segundo_gremio' => 10,
                'tomo_datos' => 11,
                'tomo_medidas' => 12,
                'firma_asegurado' => 13,
                'expediente_cerrado' => 14,
                'llamo_encargado_2' => 15  // Item separado para el segundo "Llamó a encargado"
            ];

            // Preparar respuestas del checklist
            $respuestas = [];
            foreach ($field_to_item as $field_name => $item_id) {
                $value = $_POST[$field_name] ?? null;
                if ($value !== null) {
                    $respuestas[$item_id] = $value === '1';
                }
            }

            // Calcular puntuación basada en respuestas
            $puntuacion = 0;

            foreach ($checklist_items as $item) {
                if (isset($respuestas[$item['id']]) && $respuestas[$item['id']]) {
                    $puntuacion += $item['puntos_si'];
                }
            }

            // Preparar datos para insertar
            $params = [
                'id_expediente' => $id_expediente,
                'operario_id' => $operario_id,
                'puntuacion' => $puntuacion,
                'fecha_expediente' => $fecha_expediente,
                'respuestas' => $respuestas
            ];
            
            // Insertar expediente
            $result = $db->insertExpediente($params);
            
            if ($result) {
                // Limpiar el POST y redirigir para evitar duplicados
                header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                exit();
            }
        } catch (Exception $e) {
            $toast_message = 'Error: ' . htmlspecialchars($e->getMessage());
            $toast_type = 'error';
        }
    } else {
        $toast_message = 'Errores: ' . htmlspecialchars(implode(", ", $errors));
        $toast_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Puntuación</title>
    <link rel="icon" href="/img/logo.png" type="image/png">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="/img/logo.png" alt="Logo" class="logo">
        </div>
        <h1>Formulario de Evaluación</h1>
        <div id="toast-container"></div>
        <form id="evaluationForm" method="POST">
            
            <!-- Datos básicos -->
            <div class="form-section">
                <h3>Datos básicos</h3>
                <div class="form-group">
                    <label for="id_expediente">ID Expediente:</label>
                    <input type="text" id="id_expediente" name="id_expediente" required>
                </div>
                <div class="form-group">
                    <label for="fecha_expediente">Fecha Expediente:</label>
                    <input type="date" id="fecha_expediente" name="fecha_expediente" required>
                </div>
                <div class="form-group">
                    <label for="operario_nombre">Operario:</label>
                    <input type="text" id="operario_nombre" name="operario_nombre" list="operarios_list" required placeholder="Selecciona o escribe un operario">
                    <datalist id="operarios_list">
                        <?php foreach ($operarios as $op): ?>
                            <option value="<?php echo htmlspecialchars($op['nombre_completo']); ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                
            </div>

            <!-- Checklist Items - Estructura Condicional -->
            <div class="form-section">
                <h3>Checklist de Evaluación</h3>
                
                <!-- LLEGO_A_TIEMPO -->
                <div class="checklist-item">
                    <label class="checklist-label">¿Llegó a tiempo?</label>
                    <div class="radio-group">
                        <label><input type="radio" name="llego_a_tiempo" value="1" class="conditional-trigger"> Sí</label>
                        <label><input type="radio" name="llego_a_tiempo" value="0" class="conditional-trigger"> No</label>
                    </div>
                </div>

                <!-- INFORMO_ASEG_TRAM (mostrar si llego_a_tiempo = 0) -->
                <div id="group_informo_aseg" class="checklist-item conditional-item hidden" style="margin-left: 30px;">
                    <label class="checklist-label">└─ ¿Informó a aseguradora y tramitadora?</label>
                    <div class="radio-group">
                        <label><input type="radio" name="informo_aseg_tram" value="1"> Sí</label>
                        <label><input type="radio" name="informo_aseg_tram" value="0"> No</label>
                    </div>
                </div>

                <!-- FOTOS_ANTES -->
                <div class="checklist-item">
                    <label class="checklist-label">¿Fotos antes?</label>
                    <div class="radio-group">
                        <label><input type="radio" name="fotos_antes" value="1"> Sí</label>
                        <label><input type="radio" name="fotos_antes" value="0"> No</label>
                    </div>
                </div>

                <!-- LOCALIZO_AVERIA -->
                <div class="checklist-item">
                    <label class="checklist-label">¿Localizó avería?</label>
                    <div class="radio-group">
                        <label><input type="radio" name="localizo_averia" value="1" class="conditional-trigger"> Sí</label>
                        <label><input type="radio" name="localizo_averia" value="0" class="conditional-trigger"> No</label>
                    </div>
                </div>

                <!-- LLAMO_ENCARGADO - Para localizo_averia (mostrar si localizo_averia = 0) -->
                <div id="group_llamo_encargado_1" class="checklist-item conditional-item hidden" style="margin-left: 30px;">
                    <label class="checklist-label">└─ ¿Llamó a encargado?</label>
                    <div class="radio-group">
                        <label><input type="radio" name="llamo_encargado_1" value="1"> Sí</label>
                        <label><input type="radio" name="llamo_encargado_1" value="0"> No</label>
                    </div>
                </div>

                <!-- FOTO_DURANTE -->
                <div class="checklist-item">
                    <label class="checklist-label">¿Foto durante?</label>
                    <div class="radio-group">
                        <label><input type="radio" name="foto_durante" value="1"> Sí</label>
                        <label><input type="radio" name="foto_durante" value="0"> No</label>
                    </div>
                </div>

                <!-- REPARO_PRIMERA -->
                <div class="checklist-item">
                    <label class="checklist-label">¿Reparó en 1ª visita?</label>
                    <div class="radio-group">
                        <label><input type="radio" name="reparo_primera" value="1" class="conditional-trigger"> Sí</label>
                        <label><input type="radio" name="reparo_primera" value="0" class="conditional-trigger"> No</label>
                    </div>
                </div>

                <!-- Grupo SI - Reparó en 1ª visita (mostrar si reparo_primera = 1) -->
                <div id="group_reparo_si" class="conditional-group hidden">
                    <div class="checklist-item" style="margin-left: 30px;">
                        <label class="checklist-label">└─ ¿Foto después?</label>
                        <div class="radio-group">
                            <label><input type="radio" name="foto_despues" value="1"> Sí</label>
                            <label><input type="radio" name="foto_despues" value="0"> No</label>
                        </div>
                    </div>

                    <div class="checklist-item" style="margin-left: 30px;">
                        <label class="checklist-label">└─ ¿Segundo gremio?</label>
                        <div class="radio-group">
                            <label><input type="radio" name="segundo_gremio" value="1"> Sí</label>
                            <label><input type="radio" name="segundo_gremio" value="0"> No</label>
                        </div>
                    </div>

                    <div class="checklist-item" style="margin-left: 30px;">
                        <label class="checklist-label">└─ ¿Tomó datos del perjudicado?</label>
                        <div class="radio-group">
                            <label><input type="radio" name="tomo_datos" value="1"> Sí</label>
                            <label><input type="radio" name="tomo_datos" value="0"> No</label>
                        </div>
                    </div>

                    <div class="checklist-item" style="margin-left: 30px;">
                        <label class="checklist-label">└─ ¿Tomó medidas, estancias y pavimento?</label>
                        <div class="radio-group">
                            <label><input type="radio" name="tomo_medidas" value="1"> Sí</label>
                            <label><input type="radio" name="tomo_medidas" value="0"> No</label>
                        </div>
                    </div>
                </div>

                <!-- Grupo NO - No reparó en 1ª visita (mostrar si reparo_primera = 0) -->
                <div id="group_reparo_no" class="conditional-group hidden">
                    <div class="checklist-item" style="margin-left: 30px;">
                        <label class="checklist-label">└─ ¿Llamó a encargado?</label>
                        <div class="radio-group">
                            <label><input type="radio" name="llamo_encargado_2" value="1"> Sí</label>
                            <label><input type="radio" name="llamo_encargado_2" value="0"> No</label>
                        </div>
                    </div>

                    <div class="checklist-item" style="margin-left: 30px;">
                        <label class="checklist-label">└─ ¿Reparación justificada?</label>
                        <div class="radio-group">
                            <label><input type="radio" name="justificado" value="1"> Sí</label>
                            <label><input type="radio" name="justificado" value="0"> No</label>
                        </div>
                    </div>
                </div>

                <!-- FIRMA_ASEGURADO -->
                <div class="checklist-item">
                    <label class="checklist-label">¿Firma del asegurado?</label>
                    <div class="radio-group">
                        <label><input type="radio" name="firma_asegurado" value="1"> Sí</label>
                        <label><input type="radio" name="firma_asegurado" value="0"> No</label>
                    </div>
                </div>

                <!-- EXPEDIENTE_CERRADO -->
                <div class="checklist-item">
                    <label class="checklist-label">¿Se ha cerrado expediente?</label>
                    <div class="radio-group">
                        <label><input type="radio" name="expediente_cerrado" value="1"> Sí</label>
                        <label><input type="radio" name="expediente_cerrado" value="0"> No</label>
                    </div>
                </div>

            </div>

            <!-- Puntuación calculada -->
            <div class="form-section">
                <div class="form-group">
                    <label for="puntuacion">Puntuación Total:</label>
                    <input type="number" id="puntuacion" name="puntuacion" step="0.01" readonly>
                </div>
            </div>
            
            <button type="submit">Guardar Expediente</button>
        </form>
    </div>
    <!-- Formulario de Búsqueda Filtrada -->
    <div class="container">
        <h2>Buscar Expedientes</h2>
        <form method="POST" id="searchForm">
            <input type="hidden" name="action" value="buscar">
            <div class="form-section">
                <div class="form-group">
                    <label for="operario_filtro">Operario:</label>
                    <select id="operario_filtro" name="operario_filtro" required>
                        <option value="">-- Selecciona un operario --</option>
                        <?php foreach ($operarios as $op): ?>
                            <option value="<?php echo htmlspecialchars($op['id']); ?>">
                                <?php echo htmlspecialchars($op['nombre_completo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_inicio">Fecha Inicio:</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                </div>

                <div class="form-group">
                    <label for="fecha_fin">Fecha Fin:</label>
                    <input type="date" id="fecha_fin" name="fecha_fin" required>
                </div>

                <button type="submit" class="search-button">Buscar</button>
            </div>
        </form>
    </div>

    <!-- Resultados Filtrados -->
    <?php if ($mostrar_filtrados): ?>
    <div class="container">
        <h2>Resultados de Búsqueda - <?php echo htmlspecialchars($operario_filtro); ?> (Puntuación Total: <span class="puntuacion-total"><?php echo number_format($puntuacion_total, 2); ?></span>)</h2>
        <?php if (empty($expedientes_filtrados)): ?>
            <p style="padding: 20px; background-color: #f5f5f5; border-radius: 4px; text-align: center;">No se encontraron expedientes con los criterios especificados.</p>
        <?php else: ?>
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ID Expediente</th>
                        <th>Operario</th>
                        <th>Puntuación</th>
                        <th>Fecha Expediente</th>
                        <th>Fecha Creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expedientes_filtrados as $exp): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($exp['id']); ?></td>
                            <td><?php echo htmlspecialchars($exp['id_expediente']); ?></td>
                            <td><?php echo htmlspecialchars($exp['nombre_completo']); ?></td>
                            <td><?php echo number_format($exp['puntuacion'], 2); ?></td>
                            <td><?php echo htmlspecialchars($exp['fecha_expediente'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($exp['fecha_creacion']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <script>
        // Mostrar toast si hay mensaje
        <?php if (!empty($toast_message)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?php echo addslashes($toast_message); ?>', '<?php echo $toast_type; ?>');
                
                // Limpiar parámetro success de la URL
                if (window.location.search.includes('success=1')) {
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            });
        <?php endif; ?>
    </script>
    <script src="scripts.js"></script>
</body>
</html>

