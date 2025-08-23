<?php
function mostrar_resultados_estudiante() {
    if (!is_user_logged_in()) {
        return "<p>Debes iniciar sesión para ver tus resultados.</p>";
    }



    global $wpdb;
    $user_id = get_current_user_id();
    $tabla_resultados = $wpdb->prefix . 'resultados';
    $tabla_respuestas = $wpdb->prefix . 'respuestas';

    //Preguntas por seccion 
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

    // Obtener resultado general
    $resultado = $wpdb->get_row("SELECT * FROM $tabla_resultados WHERE user_id = $user_id");

    if (!$resultado) {
        return "<p>No has respondido el cuestionario aún.</p>";
    }

    // Organizar respuestas por sección
    $respuestas = $wpdb->get_results("SELECT seccion, pregunta, respuesta FROM $tabla_respuestas WHERE resultado_id = $resultado->id ORDER BY seccion, pregunta");

    $respuestas_organizadas = [];
    foreach ($respuestas as $r) {
        $respuestas_organizadas[$r->seccion][$r->pregunta] = $r->respuesta;
    }


    ob_start(); ?>
    <link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'css/resultados.css'; ?>">


    <p><button id="mostrar-respuestas" onclick="mostrarRespuestas()">Ver mis respuestas</button></p>

    <div id="respuestas-container" style="display: none;">
        <h4>Respuestas completas:</h4>
        
        
    <?php 
        for ($s = 1; $s <= 4; $s++):
            $puntaje_seccion = $resultado->{'seccion' . $s};
            $suma_respuestas = array_sum($respuestas_organizadas[$s]);
            $puntaje_calculado_seccion = round(($suma_respuestas / 15) * 25,2); 
            $Total+=$puntaje_calculado_seccion;
        ?>
            
            <h5>Sección <?= $s ?> - <?= $preguntas[$s]['titulo'] ?> - Puntaje: <?= $puntaje_calculado_seccion ?> / 25</h5>
            <ul>
                <?php for ($p = 1; $p <= 3; $p++): 
                    $texto_pregunta = $preguntas[$s]['items'][$p - 1];
                    $respuesta = isset($respuestas_organizadas[$s][$p]) ? $respuestas_organizadas[$s][$p] : null;
                    $porcentajes = [1 => '0%', 2 => '25%', 3 => '50%', 4 => '75%', 5 => '100%'];
                    $textos = [
                        1 => 'Totalmente en desacuerdo',
                        2 => 'En desacuerdo',
                        3 => 'Neutral',
                        4 => 'De acuerdo',
                        5 => 'Totalmente de acuerdo'
                    ];
                    ?>

                    <li>
                        <strong>Pregunta <?= $p ?>:</strong> <?= $texto_pregunta ?><br>
                        <?php if ($respuesta): ?>
                            <?= $respuesta ?> - <?= $textos[$respuesta]  ?> (<?= $porcentajes[$respuesta] ?> del valor de la pregunta)
                        <?php else: ?>
                            No respondida
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
            </ul>
        <?php endfor; ?>
        <p><button onclick="mostrarRespuestas()">Ocultar respuestas</button></p>
    </div>

    <div id="total">
        <h3>Puntaje total: <?= $Total ?> / 100</h3>
    </div>

    

    <script>
        function mostrarRespuestas() {
            var container = document.getElementById('respuestas-container');
            container.style.display = container.style.display === 'none' ? 'block' : 'none';
            }
    </script>
    
    <?php
    return ob_get_clean();
}
