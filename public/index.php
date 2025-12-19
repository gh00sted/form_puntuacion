<?php
session_start();
require_once __DIR__ . '/../src/Database.php';

$db = new Database();
$response_message = '';

// Mostrar mensaje de éxito si viene del redirect
if (isset($_GET['success']) && $_GET['success'] === '1') {
    $response_message = '<div style="color: green; padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px;">✓ Expediente guardado correctamente</div>';
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_expediente = trim($_POST['id_expediente'] ?? '');
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $puntuacion = floatval($_POST['puntuacion'] ?? 0);

    // Validaciones
    $errors = [];
    if (empty($id_expediente)) {
        $errors[] = 'El ID de expediente es requerido';
    }
    if (empty($nombre_completo)) {
        $errors[] = 'El nombre completo es requerido';
    }
    if ($puntuacion < 0 || $puntuacion > 100) {
        $errors[] = 'La puntuación debe estar entre 0 y 100';
    }

    if (empty($errors)) {
        try {
            // Función para convertir booleanos
            $getBooleanValue = function($fieldName) {
                if (!isset($_POST[$fieldName]) || $_POST[$fieldName] === '' || $_POST[$fieldName] === null) {
                    return null;
                }
                
                $value = (string)$_POST[$fieldName];
                $value = trim($value);
                
                if ($value === '') {
                    return null;
                }
                
                // Convertir a booleano explícitamente
                if ($value === '1') {
                    return true;
                }
                
                if ($value === '0') {
                    return false;
                }
                
                return null;
            };
            
            // Preparar los datos para insertar
            $params = [
                'id_expediente' => $id_expediente,
                'nombre_completo' => $nombre_completo,
                'puntuacion' => $puntuacion,
                'llego_tiempo' => $getBooleanValue('llego_tiempo'),
                'informo_aseguradora' => $getBooleanValue('informo_aseguradora'),
                'fotos_antes' => $getBooleanValue('fotos_antes'),
                'localizo_averia' => $getBooleanValue('localizo_averia'),
                'llamo_encargado' => $getBooleanValue('llamo_encargado'),
                'foto_durante' => $getBooleanValue('foto_durante'),
                'reparo_1_visita' => $getBooleanValue('reparo_1_visita'),
                'foto_despues' => $getBooleanValue('foto_despues'),
                'need_seg_gremio' => $getBooleanValue('need_seg_gremio'),
                'gremio_correcto' => $getBooleanValue('gremio_correcto'),
                'tomo_datos_perj' => $getBooleanValue('tomo_datos_perj'),
                'tomo_medidas_est_pav' => $getBooleanValue('tomo_medidas_est_pav'),
                'llamo_encargado_en_visita' => $getBooleanValue('llamo_encargado_en_visita'),
                'justificado' => $getBooleanValue('justificado'),
                'nps' => isset($_POST['nps']) ? $_POST['nps'] : null,
                'firma_asegurado' => $getBooleanValue('firma_asegurado'),
                'cerro_exp' => $getBooleanValue('cerro_exp')
            ];
            
            // Insertar expediente
            $result = $db->insertExpediente($params);
            
            if ($result) {
                // Limpiar el POST y redirigir para evitar duplicados
                header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
                exit();
            }
        } catch (Exception $e) {
            $response_message = '<div style="color: red; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 20px;">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $response_message = '<div style="color: red; padding: 10px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 20px;">✗ Errores: ' . htmlspecialchars(implode(", ", $errors)) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Puntuación</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="logo-container">
            <img src="/img/logo.png" alt="Logo" class="logo">
        </div>
        <h1>Formulario de Evaluación</h1>
        <?php echo $response_message; ?>
        <form id="evaluationForm" method="POST">
            
            <!-- Datos básicos -->
            <div class="form-section">
                <h3>Datos básicos</h3>
                <div class="form-group">
                    <label for="id_expediente">ID Expediente:</label>
                    <input type="text" id="id_expediente" name="id_expediente" required>
                </div>
                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo:</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" required>
                </div>
                <div class="form-group">
                    <label for="puntuacion">Puntuación:</label>
                    <input type="number" id="puntuacion" name="puntuacion" step="0.01" required>
                </div>
            </div>
            
            <!-- llego_tiempo -->
            <div class="form-section">
                <h3>¿Llegó a tiempo?</h3>
                <div class="checkbox-group">
                    <label><input type="radio" name="llego_tiempo" value="1"> Sí</label>
                    <label><input type="radio" name="llego_tiempo" value="0"> No</label>
                </div>
            </div>
            
            <!-- informo_aseguradora (mostrar si llego_tiempo = 0) -->
            <div id="informo_aseguradora_group" class="form-section hidden">
                <h3>¿Informó a la aseguradora?</h3>
                <div class="checkbox-group">
                    <label><input type="radio" name="informo_aseguradora" value="1"> Sí</label>
                    <label><input type="radio" name="informo_aseguradora" value="0"> No</label>
                </div>
            </div>
            
            <!-- fotos_antes -->
            <div class="form-section">
                <h3>¿Fotos antes?</h3>
                <div class="checkbox-group">
                    <label><input type="radio" name="fotos_antes" value="1"> Sí</label>
                    <label><input type="radio" name="fotos_antes" value="0"> No</label>
                </div>
            </div>
            
            <!-- localizo_averia -->
            <div class="form-section">
                <h3>¿Localizó la avería?</h3>
                <div class="checkbox-group">
                    <label><input type="radio" name="localizo_averia" value="1"> Sí</label>
                    <label><input type="radio" name="localizo_averia" value="0"> No</label>
                </div>
            </div>
            
            <!-- llamo_encargado (mostrar si localizo_averia = 0) -->
            <div id="llamo_encargado_group" class="form-section hidden">
                <h3>¿Llamó al encargado?</h3>
                <div class="checkbox-group">
                    <label><input type="radio" name="llamo_encargado" value="1"> Sí</label>
                    <label><input type="radio" name="llamo_encargado" value="0"> No</label>
                </div>
            </div>
            
            <!-- foto_durante -->
            <div class="form-section">
                <h3>¿Foto durante?</h3>
                <div class="checkbox-group">
                    <label><input type="radio" name="foto_durante" value="1"> Sí</label>
                    <label><input type="radio" name="foto_durante" value="0"> No</label>
                </div>
            </div>
            
            <!-- reparo_1_visita -->
            <div class="form-section">
                <h3>¿Reparó en 1ª visita?</h3>
                <div class="checkbox-group">
                    <label><input type="radio" name="reparo_1_visita" value="1"> Sí</label>
                    <label><input type="radio" name="reparo_1_visita" value="0"> No</label>
                </div>
            </div>
            
            <!-- reparo_si_group (mostrar si reparo_1_visita = 1) -->
            <div id="reparo_si_group" class="hidden">
                <div class="form-section">
                    <h3>¿Foto después?</h3>
                    <div class="checkbox-group">
                        <label><input type="radio" name="foto_despues" value="1"> Sí</label>
                        <label><input type="radio" name="foto_despues" value="0"> No</label>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>¿Necesita seguro gremio?</h3>
                    <div class="checkbox-group">
                        <label><input type="radio" name="need_seg_gremio" value="1"> Sí</label>
                        <label><input type="radio" name="need_seg_gremio" value="0"> No</label>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>¿Gremio correcto?</h3>
                    <div class="checkbox-group">
                        <label><input type="radio" name="gremio_correcto" value="1"> Sí</label>
                        <label><input type="radio" name="gremio_correcto" value="0"> No</label>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>¿Tomó datos perjudicado?</h3>
                    <div class="checkbox-group">
                        <label><input type="radio" name="tomo_datos_perj" value="1"> Sí</label>
                        <label><input type="radio" name="tomo_datos_perj" value="0"> No</label>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>¿Tomó medidas estabilización pavimento?</h3>
                    <div class="checkbox-group">
                        <label><input type="radio" name="tomo_medidas_est_pav" value="1"> Sí</label>
                        <label><input type="radio" name="tomo_medidas_est_pav" value="0"> No</label>
                    </div>
                </div>
            </div>
            
            <!-- reparo_no_group (mostrar si reparo_1_visita = 0) -->
            <div id="reparo_no_group" class="hidden">
                <div class="form-section">
                    <h3>¿Llamó al encargado en visita?</h3>
                    <div class="checkbox-group">
                        <label><input type="radio" name="llamo_encargado_en_visita" value="1"> Sí</label>
                        <label><input type="radio" name="llamo_encargado_en_visita" value="0"> No</label>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>¿Reparación justificada?</h3>
                    <div class="checkbox-group">
                        <label><input type="radio" name="justificado" value="1"> Sí</label>
                        <label><input type="radio" name="justificado" value="0"> No</label>
                    </div>
                </div>
            </div>
            
            <!-- nps -->
            <div class="form-section">
                <h3>Puntuación NPS</h3>
                <div class="form-group">
                    <label for="nps">NPS (1-10):</label>
                    <input type="number" id="nps" name="nps" min="1" max="10">
                </div>
            </div>
            
            <!-- firma_asegurado -->
            <div class="form-section">
                <h3>¿Firma del asegurado?</h3>
                <div class="checkbox-group">
                    <label><input type="radio" name="firma_asegurado" value="1"> Sí</label>
                    <label><input type="radio" name="firma_asegurado" value="0"> No</label>
                </div>
            </div>
            
            <!-- cerro_exp -->
            <div class="form-section">
                <h3>¿Cerró expediente?</h3>
                <div class="checkbox-group">
                    <label><input type="radio" name="cerro_exp" value="1"> Sí</label>
                    <label><input type="radio" name="cerro_exp" value="0"> No</label>
                </div>
            </div>
            
            <button type="submit">Guardar Expediente</button>
        </form>
    </div>
    
    <div class="container">
        <h2>Expedientes guardados</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Expediente</th>
                    <th>Nombre Completo</th>
                    <th>Puntuación</th>
                    <th>Llegó a tiempo</th>
                    <th>Informó aseguradora</th>
                    <th>Fotos antes</th>
                    <th>Localizó avería</th>
                    <th>Llamó encargado</th>
                    <th>Foto durante</th>
                    <th>Reparó 1ª visita</th>
                    <th>Foto después</th>
                    <th>Necesita seguro gremio</th>
                    <th>Gremio correcto</th>
                    <th>Tomó datos perjudicado</th>
                    <th>Tomó medidas est. pavimento</th>
                    <th>Llamó encargado en visita</th>
                    <th>Justificado</th>
                    <th>NPS</th>
                    <th>Firma asegurado</th>
                    <th>Cerró expediente</th>
                    <th>Fecha Creación</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $expedientes = $db->getExpedientes();
                    
                    foreach ($expedientes as $exp) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($exp['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($exp['id_expediente']) . "</td>";
                        echo "<td>" . htmlspecialchars($exp['nombre_completo']) . "</td>";
                        echo "<td>" . htmlspecialchars($exp['puntuacion']) . "</td>";
                        echo "<td>" . ($exp['llego_tiempo'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['informo_aseguradora'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['fotos_antes'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['localizo_averia'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['llamo_encargado'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['foto_durante'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['reparo_1_visita'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['foto_despues'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['need_seg_gremio'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['gremio_correcto'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['tomo_datos_perj'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['tomo_medidas_est_pav'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['llamo_encargado_en_visita'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['justificado'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . htmlspecialchars($exp['nps'] ?? '') . "</td>";
                        echo "<td>" . ($exp['firma_asegurado'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . ($exp['cerro_exp'] ? 'Sí' : 'No') . "</td>";
                        echo "<td>" . htmlspecialchars($exp['fecha_creacion']) . "</td>";
                        echo "</tr>";
                    }
                } catch (Exception $e) {
                    echo "<tr><td colspan='22'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        // Update conditional fields based on radio selections
        function updateConditionalFields() {
            // llego_tiempo = 0 (No) → show informo_aseguradora
            const llegoTiempo = document.querySelector('input[name="llego_tiempo"]:checked');
            const informoGroup = document.getElementById('informo_aseguradora_group');
            if (llegoTiempo && informoGroup) {
                informoGroup.classList.toggle('hidden', llegoTiempo.value !== '0');
            }
            
            // localizo_averia = 0 (No) → show llamo_encargado
            const localizoAveria = document.querySelector('input[name="localizo_averia"]:checked');
            const llamoEncargadoGroup = document.getElementById('llamo_encargado_group');
            if (localizoAveria && llamoEncargadoGroup) {
                llamoEncargadoGroup.classList.toggle('hidden', localizoAveria.value !== '0');
            }
            
            // reparo_1_visita = 1 (Sí) → show reparo_si_group
            // reparo_1_visita = 0 (No) → show reparo_no_group
            const raroVisita = document.querySelector('input[name="reparo_1_visita"]:checked');
            const reparoSiGroup = document.getElementById('reparo_si_group');
            const reparoNoGroup = document.getElementById('reparo_no_group');
            
            if (raroVisita) {
                if (reparoSiGroup) {
                    reparoSiGroup.classList.toggle('hidden', raroVisita.value !== '1');
                }
                if (reparoNoGroup) {
                    reparoNoGroup.classList.toggle('hidden', raroVisita.value !== '0');
                }
            }
        }
        
        // Initialize event listeners when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Add change listeners to all radio buttons
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', updateConditionalFields);
            });
        });
    </script>
</body>
</html>

