<?php
/**
 * Plugin Name: Validacion TE JAVA
 * Description: Plugin que permite la validacion del tutorial educativo de Java 
 * Version: 1.0
 * Author: TE JAVA
 */

if (!defined('ABSPATH')) exit;

define('CUESTIONARIO_PATH', plugin_dir_path(__FILE__));

require_once CUESTIONARIO_PATH . 'Tablas.php';
require_once CUESTIONARIO_PATH . 'Formulario.php';
require_once CUESTIONARIO_PATH . 'Resultado_Estudiantes.php';
require_once CUESTIONARIO_PATH . 'Admin_Tabla.php';

// Crear tablas al activar el plugin
register_activation_hook(__FILE__, 'crear_tablas_validacion');

// Registrar shortcodes
add_shortcode('cuestionario_estudiante', 'mostrar_formulario_cuestionario');
add_shortcode('resultados_estudiante', 'mostrar_resultados_estudiante');
add_shortcode('resultados_admin', 'mostrar_resultados_admin');
require_once plugin_dir_path(__FILE__) . 'Tablas.php';


function validacion_cargar_estilos_condicional() {
    global $post;
    if (!isset($post->post_content)) return;

    error_log('Contenido del post: ' . $post->post_content);

    if (has_shortcode($post->post_content, 'cuestionario_estudiante')) {
        wp_enqueue_style('formulario-css', plugins_url('css/formulario.css', __FILE__));
        wp_enqueue_style('resultados-css', plugins_url('css/resultados.css', __FILE__));
        error_log('Estilos formulario y resultados cargados juntos');
    }

    if (has_shortcode($post->post_content, 'resultados_admin')) {
        wp_enqueue_style('tabla-admin-css', plugins_url('css/tablaAdmin.css', __FILE__));
        error_log('Estilos de tabla admin cargados');
    }
}
add_action('wp_enqueue_scripts', 'validacion_cargar_estilos_condicional');


function cargar_scripts_histograma() {
    if (current_user_can('administrator')) {
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
        wp_enqueue_script(
            'histograma',
            plugins_url('histograma.js', __FILE__), 
            ['chartjs'],
            null,
            true
        );

        global $wpdb;
        $tabla_respuestas = $wpdb->prefix . 'respuestas';

        // Promedios por sección
        $promedios = [];
        for ($s = 1; $s <= 4; $s++) {
            $promedios[] = round((float)$wpdb->get_var($wpdb->prepare(
                "SELECT AVG(respuesta) FROM $tabla_respuestas WHERE seccion = %d", $s
            )), 2);
        }

        // Promedios por pregunta por sección
        $resultados = $wpdb->get_results("
            SELECT seccion, pregunta, AVG(respuesta) AS promedio
            FROM $tabla_respuestas
            GROUP BY seccion, pregunta
            ORDER BY seccion, pregunta
        ");

        $promediosPorPregunta = [];

        foreach ($resultados as $fila) {
            $seccion = 'Sección ' . $fila->seccion;
            if (!isset($promediosPorPregunta[$seccion])) {
                $promediosPorPregunta[$seccion] = [];
            }
            $promediosPorPregunta[$seccion][] = round($fila->promedio, 2);
        }

        // Pasar ambos datos a JS
        wp_localize_script('histograma', 'datosGraficos', [
            'promediosPorSeccion' => $promedios,
            'promediosPorPregunta' => $promediosPorPregunta,
        ]);
    }
}
add_action('wp_enqueue_scripts', 'cargar_scripts_histograma');





