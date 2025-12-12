<?php
$xml = simplexml_load_file('ies_db.xml') or die('Error: no se cargó el XML. Escribe correctamente el nombre del archivo');

echo "<h2>Iniciando procesamiento del XML...</h2>";

// Conexión a MySQL sin especificar base de datos
$conexion = new mysqli("localhost", "root", "root");

// Verificar conexión
if ($conexion->connect_errno) {
    die("Fallo al conectar a MySQL: (" . $conexion->connect_errno . ") " . $conexion->connect_error);
}

echo "✓ Conexión a MySQL establecida.<br>";

// Verificar si la base de datos existe
echo "Verificando base de datos...<br>";
$result = $conexion->query("SHOW DATABASES LIKE 'nuevo_prueba_xml'");
if ($result->num_rows == 0) {
    // Crear la base de datos si no existe con el collation correcto
    $sqlCrearDB = "CREATE DATABASE nuevo_prueba_xml CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish2_ci";
    
    if ($conexion->query($sqlCrearDB) === TRUE) {
        echo "✓ Base de datos 'nuevo_prueba_xml' creada exitosamente.<br>";
    } else {
        die("✗ Error al crear la base de datos: " . $conexion->error);
    }
} else {
    echo "✓ Base de datos 'nuevo_prueba_xml' ya existe.<br>";
}

// Seleccionar la base de datos
$conexion->select_db("nuevo_prueba_xml");

// Establecer el conjunto de caracteres a utf8mb4
$conexion->set_charset("utf8mb4");

// SQL para crear las tablas con restricciones y collation correcto
$sqlTablas = "
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;
SET time_zone = '+00:00';

-- Estructura de tabla para la tabla `sigi_programa_estudios`
CREATE TABLE IF NOT EXISTS `sigi_programa_estudios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Estructura de tabla para la tabla `sigi_planes_estudio`
CREATE TABLE IF NOT EXISTS `sigi_planes_estudio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_programa_estudios` int NOT NULL,
  `nombre` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `resolucion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `perfil_egresado` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_planes_programa` (`id_programa_estudios`),
  CONSTRAINT `fk_planes_programa` FOREIGN KEY (`id_programa_estudios`) REFERENCES `sigi_programa_estudios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Estructura de tabla para la tabla `sigi_modulo_formativo`
CREATE TABLE IF NOT EXISTS `sigi_modulo_formativo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `nro_modulo` int NOT NULL,
  `id_plan_estudio` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_modulo_plan` (`id_plan_estudio`),
  CONSTRAINT `fk_modulo_plan` FOREIGN KEY (`id_plan_estudio`) REFERENCES `sigi_planes_estudio` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Estructura de tabla para la tabla `sigi_semestre`
CREATE TABLE IF NOT EXISTS `sigi_semestre` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `id_modulo_formativo` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_modulo_formativo` (`id_modulo_formativo`),
  CONSTRAINT `sigi_semestre_ibfk_1` FOREIGN KEY (`id_modulo_formativo`) REFERENCES `sigi_modulo_formativo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Estructura de tabla para la tabla `sigi_unidad_didactica`
CREATE TABLE IF NOT EXISTS `sigi_unidad_didactica` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `id_semestre` int NOT NULL,
  `creditos_teorico` int NOT NULL,
  `creditos_practico` int NOT NULL,
  `tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL,
  `orden` int NOT NULL,
  `horas_semanal` int DEFAULT NULL,
  `horas_semestral` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_semestre` (`id_semestre`),
  CONSTRAINT `sigi_unidad_didactica_ibfk_1` FOREIGN KEY (`id_semestre`) REFERENCES `sigi_semestre` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

COMMIT;
";

echo "Creando tablas...<br>";
// Ejecutar las sentencias SQL para crear las tablas
if ($conexion->multi_query($sqlTablas)) {
    echo "✓ Tablas creadas exitosamente o ya existían.<br>";
    
    // Vaciar los resultados adicionales
    while ($conexion->more_results() && $conexion->next_result()) {
        // No hacer nada, solo avanzar
    }
} else {
    echo "✗ Error al crear las tablas: " . $conexion->error . "<br>";
}

echo "<hr><h2>Procesando datos del XML:</h2>";

// Array para almacenar IDs de referencia
$ids_referencia = array();

// Recorrer el XML y procesar los datos
foreach ($xml as $i_pe => $pe) {
    echo "<strong>Programa de Estudios:</strong><br>";
    echo "Nombre: " . $pe->nombre . '<br>';
    echo "Código: " . $pe->codigo . '<br>';
    echo "Tipo: " . $pe->tipo . '<br>';
    
    // Insertar programa de estudios
    $codigo = $conexion->real_escape_string((string)$pe->codigo);
    $tipo = $conexion->real_escape_string((string)$pe->tipo);
    $nombre = $conexion->real_escape_string((string)$pe->nombre);
    
    $consulta = "INSERT INTO sigi_programa_estudios (codigo, tipo, nombre) 
                 VALUES ('$codigo', '$tipo', '$nombre') 
                 ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)";
    
    if ($conexion->query($consulta)) {
        $id_programa = $conexion->insert_id;
        // Si es un duplicado, obtener el ID existente
        if ($id_programa == 0) {
            $result = $conexion->query("SELECT id FROM sigi_programa_estudios WHERE codigo = '$codigo'");
            $row = $result->fetch_assoc();
            $id_programa = $row['id'];
        }
        $ids_referencia[(string)$pe->codigo] = $id_programa;
        echo "✓ Insertado en DB (ID: $id_programa)<br><br>";
    } else {
        echo "✗ Error al insertar: " . $conexion->error . "<br><br>";
        continue;
    }
    
    // Procesar planes de estudio
    foreach ($pe->planes_estudio[0] as $i_ple => $plan) {
        echo "--<strong>Plan de Estudio:</strong><br>";
        echo "--Nombre: " . $plan->nombre . '<br>';
        echo "--Resolución: " . $plan->resolucion . '<br>';
        echo "--Fecha de registro: " . $plan->fecha_registro . '<br>';
        
        // Insertar plan de estudio
        $nombre_plan = $conexion->real_escape_string((string)$plan->nombre);
        $resolucion = $conexion->real_escape_string((string)$plan->resolucion);
        $fecha_registro = $conexion->real_escape_string((string)$plan->fecha_registro);
        $perfil_egresado = isset($plan->perfil_egresado) ? $conexion->real_escape_string((string)$plan->perfil_egresado) : '';
        
        $consulta = "INSERT INTO sigi_planes_estudio (id_programa_estudios, nombre, resolucion, fecha_registro, perfil_egresado) 
                     VALUES ('$id_programa', '$nombre_plan', '$resolucion', '$fecha_registro', '$perfil_egresado')";
        
        if ($conexion->query($consulta)) {
            $id_plan = $conexion->insert_id;
            $ids_referencia[(string)$pe->codigo . '_' . (string)$plan->nombre] = $id_plan;
            echo "--✓ Insertado en DB (ID: $id_plan)<br><br>";
        } else {
            echo "--✗ Error al insertar: " . $conexion->error . "<br><br>";
            continue;
        }
        
        // Procesar módulos formativos
        foreach ($plan->modulos_formativos[0] as $i__mod => $modulo) {
            echo '----<strong>Módulo Formativo:</strong><br>';
            echo '----' . $modulo->nro_modulo . ": " . $modulo->descripcion . '<br>';
            
            // Insertar módulo formativo
            $descripcion = $conexion->real_escape_string((string)$modulo->descripcion);
            $nro_modulo = (int)$modulo->nro_modulo;
            
            $consulta = "INSERT INTO sigi_modulo_formativo (descripcion, nro_modulo, id_plan_estudio) 
                         VALUES ('$descripcion', '$nro_modulo', '$id_plan')";
            
            if ($conexion->query($consulta)) {
                $id_modulo = $conexion->insert_id;
                $ids_referencia[(string)$pe->codigo . '_' . (string)$plan->nombre . '_mod' . $nro_modulo] = $id_modulo;
                echo '----✓ Insertado en DB (ID: $id_modulo)<br><br>';
            } else {
                echo '----✗ Error al insertar: ' . $conexion->error . '<br><br>';
                continue;
            }
            
            // Procesar periodos/semestres
            foreach ($modulo->periodos[0] as $i_per => $periodo) {
                echo '------<strong>Semestre/Período:</strong><br>';
                echo '------' . $periodo->descripcion . '<br>';
                
                // Insertar semestre
                $descripcion_periodo = $conexion->real_escape_string((string)$periodo->descripcion);
                
                $consulta = "INSERT INTO sigi_semestre (descripcion, id_modulo_formativo) 
                             VALUES ('$descripcion_periodo', '$id_modulo')";
                
                if ($conexion->query($consulta)) {
                    $id_semestre = $conexion->insert_id;
                    $ids_referencia[(string)$pe->codigo . '_' . (string)$plan->nombre . '_mod' . $nro_modulo . '_' . (string)$periodo->descripcion] = $id_semestre;
                    echo '------✓ Insertado en DB (ID: $id_semestre)<br><br>';
                } else {
                    echo '------✗ Error al insertar: ' . $conexion->error . '<br><br>';
                    continue;
                }
                
                // Procesar unidades didácticas
                $orden = 1;
                foreach ($periodo->unidades_didacticas[0] as $i_ud => $ud) {
                    echo '--------<strong>Unidad Didáctica:</strong><br>';
                    echo '--------' . $ud->nombre . '<br>';
                    echo '-------- Créditos Teórico: ' . $ud->creditos_teorico . '<br>';
                    echo '-------- Créditos Práctico: ' . $ud->creditos_practico . '<br>';
                    echo '-------- Tipo: ' . $ud->tipo . '<br>';
                    echo '-------- Horas Semanal: ' . $ud->horas_semanal . '<br>';
                    echo '-------- Horas Semestral: ' . $ud->horas_semestral . '<br>';
                    
                    // Insertar unidad didáctica
                    $nombre_ud = $conexion->real_escape_string((string)$ud->nombre);
                    $creditos_teorico = (int)$ud->creditos_teorico;
                    $creditos_practico = (int)$ud->creditos_practico;
                    $tipo = $conexion->real_escape_string((string)$ud->tipo);
                    $horas_semanal = isset($ud->horas_semanal) ? (int)$ud->horas_semanal : 0;
                    $horas_semestral = isset($ud->horas_semestral) ? (int)$ud->horas_semestral : 0;
                    
                    $consulta = "INSERT INTO sigi_unidad_didactica (nombre, id_semestre, creditos_teorico, creditos_practico, tipo, orden, horas_semanal, horas_semestral) 
                                 VALUES ('$nombre_ud', '$id_semestre', '$creditos_teorico', '$creditos_practico', '$tipo', '$orden', '$horas_semanal', '$horas_semestral')";
                    
                    if ($conexion->query($consulta)) {
                        echo '--------✓ Insertado en DB (Orden: $orden)<br><br>';
                    } else {
                        echo '--------✗ Error al insertar: ' . $conexion->error . '<br><br>';
                    }
                    $orden++;
                }
            }
        }
    }
    echo "<hr>";
}

// Verificar tablas creadas y contenido
echo "<h2>Resumen de la base de datos:</h2>";

$tablas = ['sigi_programa_estudios', 'sigi_planes_estudio', 'sigi_modulo_formativo', 'sigi_semestre', 'sigi_unidad_didactica'];

foreach ($tablas as $tabla) {
    $result = $conexion->query("SELECT COUNT(*) as total FROM $tabla");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Tabla <strong>$tabla</strong>: " . $row['total'] . " registros<br>";
    } else {
        echo "Error al contar registros en $tabla: " . $conexion->error . "<br>";
    }
}

// Mostrar algunos datos de ejemplo
echo "<h3>Ejemplo de datos insertados:</h3>";

// Mostrar programas
$result = $conexion->query("SELECT codigo, nombre, tipo FROM sigi_programa_estudios LIMIT 3");
echo "<strong>Programas de Estudios (primeros 3):</strong><br>";
while ($row = $result->fetch_assoc()) {
    echo "- " . $row['codigo'] . ": " . $row['nombre'] . " (" . $row['tipo'] . ")<br>";
}

echo "<br><strong>Estructura completada exitosamente!</strong>";

// Cerrar conexión
$conexion->close();
?>