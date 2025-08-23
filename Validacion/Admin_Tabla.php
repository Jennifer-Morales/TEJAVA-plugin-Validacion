<?php
function mostrar_resultados_admin() {
    if (!current_user_can('administrator')) {
        return "<p>No tienes permisos para ver esta información.</p>";
    }
    


    global $wpdb;
    $tabla_resultados = $wpdb->prefix . 'resultados';
    $tabla_respuestas = $wpdb->prefix . 'respuestas';

    $resultados = $wpdb->get_results("
        SELECT r.*, u.display_name 
        FROM $tabla_resultados r
        JOIN {$wpdb->prefix}users u ON u.ID = r.user_id
        ORDER BY r.fecha DESC
    ");

    if (!$resultados) {
        return "<p>No hay resultados aún.</p>";
    }

    ob_start(); ?>
    
    <h3 id="titulo">Resultados de todos los estudiantes</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Nombre</th>
            <th>Asignatura</th>
            <th>Fecha</th>
            <th>Sección</th>
            <th>Pregunta 1</th>
            <th>Pregunta 2</th>
            <th>Pregunta 3</th>
            <th>Total Sección</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
        global $wpdb;
        $resultados = $wpdb->get_results("
            SELECT r.*, u.display_name 
            FROM $tabla_resultados r
            JOIN {$wpdb->prefix}users u ON u.ID = r.user_id
            ORDER BY r.fecha DESC
        ");

        // Recorrer los resultados para mostrar la tabla
        foreach ($resultados as $r):
            // Datos del usuario
            $user = get_userdata($r->user_id);
        ?>
            <?php for ($s = 1; $s <= 4; $s++): ?>
                <tr>
                    <!-- Datos del usuario -->
                    <?php if ($s == 1): ?>
                        <td rowspan="4"><?= esc_html($r->user_id) ?></td>
                        <td rowspan="4"><?= esc_html($user->display_name) ?></td>
                        <td rowspan="4"><?= esc_html($r->asignatura) ?></td>
                        <td rowspan="4"><?= esc_html($r->fecha) ?></td>
                    <?php endif; ?>

                    <td>Sección <?= $s ?></td>

                    <!-- Muestra las respuestas de la sección -->
                    <?php
                    for ($p = 1; $p <= 3; $p++) {
                        // Consulta para obtener la respuesta
                        $respuesta = $wpdb->get_var($wpdb->prepare("
                            SELECT respuesta
                            FROM $tabla_respuestas
                            WHERE resultado_id = %d AND seccion = %d AND pregunta = %d
                        ", $r->id, $s, $p));
                        
                        // Verificar si la respuesta existe y la muestra
                        echo "<td>" . ($respuesta ? esc_html($respuesta) : 'No respondida') . "</td>";
                    }
                    
                    ?>

                    <!-- Calcula el puntaje total por sección -->
                    <td>
                        <?php 
                        $puntaje_seccion = isset($r->{'seccion' . $s}) ? $r->{'seccion' . $s} : 0;
                        echo round($puntaje_seccion, 2);
                        ?>
                    </td>
                    <?php if ($s == 1): ?>
                        <td rowspan="4"><?= esc_html($r->total) ?></td>
                    <?php endif; ?>
                    
                </tr>
            <?php endfor; ?>
        <?php endforeach; ?>
    </tbody>
</table>
<button id="mostrarHistograma" class="boton-grafico">📊 Ver grafico (Promedio seccion )</button>
<canvas id="graficoHistograma" style="display:none; max-width: 800px; "></canvas>


<button id="mostrarGraficoLineas" class="boton-grafico">📈 Ver grafico (promedio preguntas)</button>
<canvas id="graficoLineas" style="display:none; max-width: 800px; "></canvas>


<button id="ocultarGraficas" class="boton-grafico boton-ocultar">❌ Ocultar gráficas</button>



    <?php return ob_get_clean();
}
    