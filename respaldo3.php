<?php
$xml = simplexml_load_file('ies_db.xml') or die('Error: no se cargó el XML. Escribe correctamente el nombre del archivo');

// Conexión a MySQL sin especificar base de datos
$conexion = new mysqli("localhost", "root", "");

// Verificar conexión
if ($conexion->connect_errno) {
    die("Fallo al conectar a MySQL: (" . $conexion->connect_errno . ") " . $conexion->connect_error);
}

// Verificar si la base de datos existe
$result = $conexion->query("SHOW DATABASES LIKE 'nuevo_prueba_xml'");
if ($result->num_rows == 0) {
    // Crear la base de datos si no existe con el collation correcto
    $sqlCrearDB = "CREATE DATABASE nuevo_prueba_xml CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci";
    
    if ($conexion->query($sqlCrearDB) === TRUE) {
        echo "Base de datos 'nuevo_prueba_xml' creada exitosamente.<br>";
    } else {
        die("Error al crear la base de datos: " . $conexion->error);
    }
} else {
    echo "Base de datos 'nuevo_prueba_xml' ya existe.<br>";
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
  PRIMARY KEY (`id`)
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

// Ejecutar las sentencias SQL para crear las tablas
if ($conexion->multi_query($sqlTablas)) {
    echo "Tablas creadas exitosamente o ya existían.<br>";
    
    // Vaciar los resultados adicionales
    while ($conexion->more_results() && $conexion->next_result()) {
        // No hacer nada, solo avanzar
    }
} else {
    echo "Error al crear las tablas: " . $conexion->error . "<br>";
}

// Ahora procedemos a insertar los datos del XML
echo "<h2>Insertando datos desde el XML...</h2>";

// Array para almacenar IDs de referencia
$ids_referencia = array();

// 1. Insertar Programas de Estudios
foreach ($xml->children() as $pe) {
    $codigo = $conexion->real_escape_string((string)$pe->codigo);
    $tipo = $conexion->real_escape_string((string)$pe->tipo);
    $nombre = $conexion->real_escape_string((string)$pe->nombre);
    
    $sql = "INSERT INTO sigi_programa_estudios (codigo, tipo, nombre) 
            VALUES ('$codigo', '$tipo', '$nombre')";
    
    if ($conexion->query($sql)) {
        $id_programa = $conexion->insert_id;
        $ids_referencia[(string)$pe->codigo] = $id_programa;
        echo "Insertado Programa de Estudios: $nombre (ID: $id_programa)<br>";
    } else {
        echo "Error al insertar Programa de Estudios: " . $conexion->error . "<br>";
    }
}

// 2. Insertar Planes de Estudio
foreach ($xml->children() as $pe) {
    $codigo_programa = (string)$pe->codigo;
    $id_programa = $ids_referencia[$codigo_programa];
    
    foreach ($pe->planes_estudio->children() as $plan) {
        $nombre = $conexion->real_escape_string((string)$plan->nombre);
        $resolucion = $conexion->real_escape_string((string)$plan->resolucion);
        $fecha_registro = $conexion->real_escape_string((string)$plan->fecha_registro);
        $perfil_egresado = $conexion->real_escape_string((string)$plan->perfil_egresado);
        
        $sql = "INSERT INTO sigi_planes_estudio (id_programa_estudios, nombre, resolucion, fecha_registro, perfil_egresado) 
                VALUES ('$id_programa', '$nombre', '$resolucion', '$fecha_registro', '$perfil_egresado')";
        
        if ($conexion->query($sql)) {
            $id_plan = $conexion->insert_id;
            $ids_referencia[$codigo_programa . '_' . $nombre] = $id_plan;
            echo "Insertado Plan de Estudio: $nombre (ID: $id_plan)<br>";
        } else {
            echo "Error al insertar Plan de Estudio: " . $conexion->error . "<br>";
        }
    }
}

// 3. Insertar Módulos Formativos
foreach ($xml->children() as $pe) {
    $codigo_programa = (string)$pe->codigo;
    
    foreach ($pe->planes_estudio->children() as $plan) {
        $nombre_plan = (string)$plan->nombre;
        $id_plan = $ids_referencia[$codigo_programa . '_' . $nombre_plan];
        
        foreach ($plan->modulos_formativos->children() as $modulo) {
            $descripcion = $conexion->real_escape_string((string)$modulo->descripcion);
            $nro_modulo = (int)$modulo->nro_modulo;
            
            $sql = "INSERT INTO sigi_modulo_formativo (descripcion, nro_modulo, id_plan_estudio) 
                    VALUES ('$descripcion', '$nro_modulo', '$id_plan')";
            
            if ($conexion->query($sql)) {
                $id_modulo = $conexion->insert_id;
                $ids_referencia[$codigo_programa . '_' . $nombre_plan . '_mod' . $nro_modulo] = $id_modulo;
                echo "Insertado Módulo Formativo: $descripcion (ID: $id_modulo)<br>";
            } else {
                echo "Error al insertar Módulo Formativo: " . $conexion->error . "<br>";
            }
        }
    }
}

// 4. Insertar Semestres/Periodos
foreach ($xml->children() as $pe) {
    $codigo_programa = (string)$pe->codigo;
    
    foreach ($pe->planes_estudio->children() as $plan) {
        $nombre_plan = (string)$plan->nombre;
        
        foreach ($plan->modulos_formativos->children() as $modulo) {
            $nro_modulo = (int)$modulo->nro_modulo;
            $id_modulo = $ids_referencia[$codigo_programa . '_' . $nombre_plan . '_mod' . $nro_modulo];
            
            foreach ($modulo->periodos->children() as $periodo) {
                $descripcion = $conexion->real_escape_string((string)$periodo->descripcion);
                
                $sql = "INSERT INTO sigi_semestre (descripcion, id_modulo_formativo) 
                        VALUES ('$descripcion', '$id_modulo')";
                
                if ($conexion->query($sql)) {
                    $id_semestre = $conexion->insert_id;
                    $ids_referencia[$codigo_programa . '_' . $nombre_plan . '_mod' . $nro_modulo . '_' . $descripcion] = $id_semestre;
                    echo "Insertado Semestre: $descripcion (ID: $id_semestre)<br>";
                } else {
                    echo "Error al insertar Semestre: " . $conexion->error . "<br>";
                }
            }
        }
    }
}

// 5. Insertar Unidades Didácticas
$orden_counter = 1;
foreach ($xml->children() as $pe) {
    $codigo_programa = (string)$pe->codigo;
    
    foreach ($pe->planes_estudio->children() as $plan) {
        $nombre_plan = (string)$plan->nombre;
        
        foreach ($plan->modulos_formativos->children() as $modulo) {
            $nro_modulo = (int)$modulo->nro_modulo;
            
            foreach ($modulo->periodos->children() as $periodo) {
                $descripcion_periodo = (string)$periodo->descripcion;
                $id_semestre = $ids_referencia[$codigo_programa . '_' . $nombre_plan . '_mod' . $nro_modulo . '_' . $descripcion_periodo];
                
                foreach ($periodo->unidades_didacticas->children() as $ud) {
                    $nombre = $conexion->real_escape_string((string)$ud->nombre);
                    $creditos_teorico = (int)$ud->creditos_teorico;
                    $creditos_practico = (int)$ud->creditos_practico;
                    $tipo = $conexion->real_escape_string((string)$ud->tipo);
                    $horas_semanal = isset($ud->horas_semanal) ? (int)$ud->horas_semanal : 0;
                    $horas_semestral = isset($ud->horas_semestral) ? (int)$ud->horas_semestral : 0;
                    
                    $sql = "INSERT INTO sigi_unidad_didactica (nombre, id_semestre, creditos_teorico, creditos_practico, tipo, orden, horas_semanal, horas_semestral) 
                            VALUES ('$nombre', '$id_semestre', '$creditos_teorico', '$creditos_practico', '$tipo', '$orden_counter', '$horas_semanal', '$horas_semestral')";
                    
                    if ($conexion->query($sql)) {
                        echo "Insertada Unidad Didáctica: $nombre (Orden: $orden_counter)<br>";
                        $orden_counter++;
                    } else {
                        echo "Error al insertar Unidad Didáctica: " . $conexion->error . "<br>";
                    }
                }
                $orden_counter = 1; // Resetear contador para el próximo semestre
            }
        }
    }
}

// Verificar tablas creadas
$tablasEsperadas = ['sigi_programa_estudios', 'sigi_planes_estudio', 'sigi_modulo_formativo', 'sigi_semestre', 'sigi_unidad_didactica'];
$tablasExistentes = [];

$result = $conexion->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $tablasExistentes[] = $row[0];
    }
    
    echo "<h3>Resumen:</h3>";
    echo "Tablas en la base de datos 'nuevo_prueba_xml': " . implode(', ', $tablasExistentes) . "<br>";
    
    // Verificar si faltan tablas
    $faltantes = array_diff($tablasEsperadas, $tablasExistentes);
    if (empty($faltantes)) {
        echo "<br>Todas las tablas necesarias están creadas correctamente.<br>";
    } else {
        echo "<br>Faltan las siguientes tablas: " . implode(', ', $faltantes) . "<br>";
    }
}

echo "<h2>Proceso completado exitosamente!</h2>";

// Cerrar conexión
$conexion->close();
?>