<?php
function crear_tablas_validacion() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tabla_resultados = $wpdb->prefix . 'resultados';
    $tabla_respuestas = $wpdb->prefix . 'respuestas';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql_resultados = "CREATE TABLE $tabla_resultados (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        asignatura VARCHAR(50) DEFAULT '' NOT NULL,
        seccion1 INT DEFAULT 0,
        seccion2 INT DEFAULT 0,
        seccion3 INT DEFAULT 0,
        seccion4 INT DEFAULT 0,
        total INT DEFAULT 0,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT wp_resultados_fk_user_id FOREIGN KEY (user_id) REFERENCES wp_users(ID) ON DELETE CASCADE
    ) $charset_collate;";
    

    $sql_respuestas = "CREATE TABLE $tabla_respuestas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        resultado_id INT NOT NULL,
        seccion INT NOT NULL,
        pregunta INT NOT NULL,
        respuesta INT NOT NULL
    ) $charset_collate;";

    // Ejecutar ambas tablas con dbDelta
    dbDelta($sql_resultados);
    dbDelta($sql_respuestas);
}

// Ejecutar al activar el plugin
register_activation_hook(__FILE__, 'crear_tablas_validacion');
