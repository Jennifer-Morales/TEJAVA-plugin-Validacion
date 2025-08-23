<?php
include('Resultado_Estudiantes.php');
function mostrar_formulario_cuestionario() {
    if (!is_user_logged_in()) {
        return '<p>Debes iniciar sesión para completar el cuestionario.</p>';
    }
    //wp_enqueue_style('formulario-css', plugin_dir_url(__FILE__) . 'css/estilos.css');



    global $wpdb;
    $user_id = get_current_user_id();
    $tabla_resultados = $wpdb->prefix . 'resultados';

    // Verifica si el usuario ya respondió

    $existe = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $tabla_resultados WHERE user_id = %d AND total > 0", $user_id
    ));
    
    //echo "<p>Resultado de la consulta (existe): $existe</p>";
    //echo "<p>Tabla usada: $tabla_resultados</p>";s
    
    if ($existe > 0) {
        return '
    <div style=" font-size:18px; ; padding: 20px;">
        <p>Ya has respondido este cuestionario. Gracias por tu participación.</p>
        ' . do_shortcode('[resultados_estudiante]') . '
    </div>';

    }
    //$user_id = get_current_user_id();
    //echo "<p>Tu ID de usuario es: $user_id</p>";
    

    // Preguntas por sección
    $preguntas = [
        1 => [
            'titulo' => 'Utilidad percibida',
            'items' => [
                '¿El tutorial mejoró su comprensión de los conceptos de Java?',
                '¿El tutorial le ayudó a resolver problemas prácticos relacionados con Java?',
                '¿Se siente más preparado para programar en Java después del tutorial?',
            ]
        ],
        2 => [
            'titulo' => 'Facilidad de uso percibida',
            'items' => [
                '¿El contenido del tutorial fue fácil de entender?',
                '¿La navegación y estructura del tutorial fueron claras?',
                '¿Aprender con el tutorial no requirió mucho esfuerzo?'
            ]
        ],
        3 => [
            'titulo' => 'Actitud hacia el uso',
            'items' => [
                '¿Le gustaría usar más recursos similares para aprender Java?',
                '¿Recomendaría este tutorial a otros estudiantes?',
                '¿Usaría este recurso de nuevo en el futuro?'
            ]
        ],
        4 => [
            'titulo' => 'Intensión de Uso',
            'items' => [
                '¿Considera que este tutorial ha aumentado su interés en continuar aprendiendo programación en Java?',
                '¿Estaría dispuesto a invertir más tiempo aprendiendo Java a través de tutoriales como este?',
                '¿Tiene la intención de seguir utilizando recursos similares para aprender más sobre Java?'
            ]
        ]
    ];

    // Procesar formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cuestionario_nonce']) && wp_verify_nonce($_POST['cuestionario_nonce'], 'guardar_cuestionario')) {
        $tabla_resultados = $wpdb->prefix . 'resultados';
        $tabla_respuestas = $wpdb->prefix . 'respuestas';

        $total = 0;
        $secciones = [];
        $respuestas_individuales = [];
        $asignatura = sanitize_text_field($_POST['asignatura']);

        foreach ($preguntas as $seccion => $datos) {
            $seccion_total = 0;

            foreach ($datos['items'] as $i => $texto) {
                $pregunta_num = $i + 1;
                $campo = "pregunta_{$seccion}_{$pregunta_num}";
                $respuesta = intval($_POST[$campo] ?? 0);

                $seccion_total += $respuesta;

                $respuestas_individuales[] = [
                    'seccion' => $seccion,
                    'pregunta' => $pregunta_num,
                    'respuesta' => $respuesta
                ];
            }

            $secciones["seccion{$seccion}"] = $seccion_total;
            $total += $seccion_total;
        }

        // Insertar resultado total
        /*$wpdb->insert($tabla_resultados, [
            'user_id' => $user_id,
            'seccion1' => $secciones['seccion1'] ?? 0,
            'seccion2' => $secciones['seccion2'] ?? 0,
            'seccion3' => $secciones['seccion3'] ?? 0,
            'seccion4' => $secciones['seccion4'] ?? 0,
            'total' => $total
        ]);*/
        $secciones_calculadas = [
            'seccion1' => isset($secciones['seccion1']) ? round(($secciones['seccion1'] / 15) * 25,2) : 0,
            'seccion2' => isset($secciones['seccion2']) ? round(($secciones['seccion2'] / 15) * 25,2) : 0,
            'seccion3' => isset($secciones['seccion3']) ? round(($secciones['seccion3'] / 15) * 25 , 2) : 0,
            'seccion4' => isset($secciones['seccion4']) ? round(($secciones['seccion4'] / 15) * 25, 2): 0,
        ];
        $total = round($secciones_calculadas['seccion1'] + $secciones_calculadas['seccion2'] + $secciones_calculadas['seccion3'] + $secciones_calculadas['seccion4'],2); 
    
        //INSERTAR RESULTADOR EN TABLA
        $wpdb->insert($tabla_resultados, [
            'user_id' => $user_id,
            'asignatura' => $asignatura,
            'seccion1' => $secciones_calculadas['seccion1'] ?? 0,
            'seccion2' => $secciones_calculadas['seccion2'] ?? 0,
            'seccion3' => $secciones_calculadas['seccion3'] ?? 0,
            'seccion4' => $secciones_calculadas['seccion4'] ?? 0,
            'total' => $total
        ]);

        // Validar si ocurrió un error
        if ($wpdb->last_error) {
            return '<p>Ocurrió un error al guardar tus resultados: ' . $wpdb->last_error . '</p>';
        }

        $resultado_id = $wpdb->insert_id;

        // Guardar respuestas individuales
        foreach ($respuestas_individuales as $r) {
            $wpdb->insert($tabla_respuestas, [
                'resultado_id' => $resultado_id,
                'seccion' => $r['seccion'],
                'pregunta' => $r['pregunta'],
                'respuesta' => $r['respuesta']
            ]);
        }

        return '<p>Gracias por completar el cuestionario.</p>' . do_shortcode('[resultados_estudiante]');
    }

    ob_start(); ?>
    <div id="formulario-container">
    <form method="post">
        <?php wp_nonce_field('guardar_cuestionario', 'cuestionario_nonce'); ?>

        <p>
            <label for="asignatura"><strong>Asignatura:</strong></label><br>
            <input type="text" name="asignatura" id="asignatura" class="AsignaturaCampo" required>
        </p>

        <?php foreach ($preguntas as $seccion => $datos): ?>
            <h2>Sección <?= $seccion ?>: <?= esc_html($datos['titulo']) ?></h2>
            <?php foreach ($datos['items'] as $i => $pregunta):
                $pregunta_num = $i + 1; ?>
                <p><strong><?= $pregunta_num ?>. <?= esc_html($pregunta) ?></strong></p>
                <?php for ($j = 1; $j <= 5; $j++): ?>
                    <label>
                        <input type="radio" name="pregunta_<?= $seccion ?>_<?= $pregunta_num ?>" value="<?= $j ?>" required>
                        <?= $j ?> - <?= [
                            1 => 'Totalmente en desacuerdo',
                            2 => 'En desacuerdo',
                            3 => 'Neutral',
                            4 => 'De acuerdo',
                            5 => 'Totalmente de acuerdo'
                        ][$j] ?>
                    </label><br>
                <?php endfor; ?>
            <?php endforeach; ?>
            <hr>
        <?php endforeach; ?>

        <button type="submit">Enviar</button>
    </form>
    </div>
    <?php
    return ob_get_clean();
}
