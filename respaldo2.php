<?php
$xml = simplexml_load_file('ies_db.xml') or die('Errror: no se cargo el xml. Escribe correctamente el nombre del archivo');

// Conexión a MySQL sin especificar base de datos
$conexion = new mysqli("localhost", "root", "root");

// Verificar conexión
if ($conexion->connect_errno) {
    die("Fallo al conectar a MySQL: (" . $conexion->connect_errno . ") " . $conexion->connect_error);
}

// Verificar si la base de datos existe
$result = $conexion->query("SHOW DATABASES LIKE 'nuevo_prueba_xml'");
if ($result->num_rows == 0) {
    // Crear la base de datos si no existe
    $sqlCrearDB = "CREATE DATABASE nuevo_prueba_xml CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    
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

// SQL para crear las tablas SIN las restricciones
$sqlTablas = "
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;
SET time_zone = '+00:00';

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `sigi_programa_estudios`

CREATE TABLE IF NOT EXISTS `sigi_programa_estudios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `tipo` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `sigi_planes_estudio`

CREATE TABLE IF NOT EXISTS `sigi_planes_estudio` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_programa_estudios` int NOT NULL,
  `nombre` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `resolucion` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `perfil_egresado` varchar(3000) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_planes_programa` (`id_programa_estudios`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `sigi_modulo_formativo`

CREATE TABLE IF NOT EXISTS `sigi_modulo_formativo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(1000) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `nro_modulo` int NOT NULL,
  `id_plan_estudio` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_modulo_plan` (`id_plan_estudio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `sigi_semestre`

CREATE TABLE IF NOT EXISTS `sigi_semestre` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descripcion` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `id_modulo_formativo` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_modulo_formativo` (`id_modulo_formativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `sigi_unidad_didactica`

CREATE TABLE IF NOT EXISTS `sigi_unidad_didactica` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `id_semestre` int NOT NULL,
  `creditos_teorico` int NOT NULL,
  `creditos_practico` int NOT NULL,
  `tipo` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish2_ci NOT NULL,
  `orden` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_semestre` (`id_semestre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish2_ci;

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

// Ahora agregar las restricciones de clave foránea si no existen
$restricciones = [
    [
        'tabla' => 'sigi_modulo_formativo',
        'nombre' => 'fk_modulo_plan',
        'sql' => "ALTER TABLE `sigi_modulo_formativo` ADD CONSTRAINT `fk_modulo_plan` FOREIGN KEY (`id_plan_estudio`) REFERENCES `sigi_planes_estudio` (`id`) ON UPDATE CASCADE"
    ],
    [
        'tabla' => 'sigi_planes_estudio',
        'nombre' => 'fk_planes_programa',
        'sql' => "ALTER TABLE `sigi_planes_estudio` ADD CONSTRAINT `fk_planes_programa` FOREIGN KEY (`id_programa_estudios`) REFERENCES `sigi_programa_estudios` (`id`) ON UPDATE CASCADE"
    ],
    [
        'tabla' => 'sigi_semestre',
        'nombre' => 'sigi_semestre_ibfk_1',
        'sql' => "ALTER TABLE `sigi_semestre` ADD CONSTRAINT `sigi_semestre_ibfk_1` FOREIGN KEY (`id_modulo_formativo`) REFERENCES `sigi_modulo_formativo` (`id`) ON UPDATE CASCADE"
    ],
    [
        'tabla' => 'sigi_unidad_didactica',
        'nombre' => 'sigi_unidad_didactica_ibfk_1',
        'sql' => "ALTER TABLE `sigi_unidad_didactica` ADD CONSTRAINT `sigi_unidad_didactica_ibfk_1` FOREIGN KEY (`id_semestre`) REFERENCES `sigi_semestre` (`id`) ON UPDATE CASCADE"
    ]
];

echo "<br>Verificando restricciones de clave foránea...<br>";

foreach ($restricciones as $restriccion) {
    // Verificar si la restricción ya existe
    $sqlCheck = "SELECT CONSTRAINT_NAME 
                 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = 'para_xml' 
                 AND TABLE_NAME = '{$restriccion['tabla']}' 
                 AND CONSTRAINT_NAME = '{$restriccion['nombre']}'";
    
    $result = $conexion->query($sqlCheck);
    
    if ($result->num_rows == 0) {
        // La restricción no existe, crearla
        if ($conexion->query($restriccion['sql']) === TRUE) {
            echo "Restricción '{$restriccion['nombre']}' agregada a '{$restriccion['tabla']}'.<br>";
        } else {
            echo "Error al agregar restricción '{$restriccion['nombre']}': " . $conexion->error . "<br>";
        }
    } else {
        echo "Restricción '{$restriccion['nombre']}' ya existe en '{$restriccion['tabla']}'.<br>";
    }
}

// Verificar que todas las tablas existen
$tablasEsperadas = ['sigi_programa_estudios', 'sigi_planes_estudio', 'sigi_modulo_formativo', 'sigi_semestre', 'sigi_unidad_didactica'];
$tablasExistentes = [];

$result = $conexion->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $tablasExistentes[] = $row[0];
    }
    
    echo "<br>Tablas en la base de datos 'para_xml': " . implode(', ', $tablasExistentes) . "<br>";
    
    // Verificar si faltan tablas
    $faltantes = array_diff($tablasEsperadas, $tablasExistentes);
    if (empty($faltantes)) {
        echo "<br>Todas las tablas necesarias están creadas correctamente.<br>";
    } else {
        echo "<br>Faltan las siguientes tablas: " . implode(', ', $faltantes) . "<br>";
    }
}

// Función para verificar conexión (opcional)
function verificarConexion($conexion) {
    if ($conexion->ping()) {
        echo "Conexión activa con la base de datos.<br>";
        return true;
    } else {
        echo "Conexión perdida con la base de datos.<br>";
        return false;
    }
}

// Verificar la conexión
verificarConexion($conexion);

echo "<br>Configuración completada. Base de datos 'para_xml' lista para usar.";

//echo $xml->pe_1->nombre.'</br>';
//echo $xml->pe_2->nombre;

//modificando las variables para trabajar en contexto
foreach ($xml as $i_pe => $pe) {
    echo "Nombre: " . $pe->nombre . '<br>';
    echo "Código: " . $pe->codigo . '<br>';
    echo "Tipo: " . $pe->tipo . '<br>';
    foreach ($pe->planes_estudio[0] as $i_ple => $plan) {
        echo "--" . $plan->nombre . '<br>';
        echo "--Resolución: " . $plan->resolucion . '<br>';
        echo "--Fecha de registro: " . $plan->fecha_registro . '<br>';
        foreach ($plan->modulos_formativos[0] as $i__mod => $modulo) {
            echo '----' . $modulo->nro_modulo . ": " . $modulo->descripcion . '<br>';
            foreach ($modulo->periodos[0] as $i_per => $periodo) {
                echo '------' . $periodo->descripcion . '<br>';
                foreach ($periodo->unidades_didacticas[0] as $i_ud => $ud) {
                    echo '--------' . $ud->nombre . '<br>';
                    echo '-------- Créditos Teórico: ' . $ud->creditos_teorico . '<br>';
                    echo '-------- Créditos Práctico: ' . $ud->creditos_practico . '<br>';
                    echo '-------- Tipo: ' . $ud->tipo . '<br>';
                    echo '-------- Horas Semanal: ' . $ud->horas_semanal . '<br>';
                    echo '-------- Horas Semestral: ' . $ud->horas_semestral . '<br>';
                }
            }
        }
    }
}


//generar la base de datos del archivo .xml solo necesitan la conexion a la base de datos, y luego en cada foreach hacer los registros usando consultas
//INSERT INTO, pero antes crear a base de datos y la tabla correspondientes. insertas programa de estudios y luego obtienes su id y luego obtienes id e insertas etc