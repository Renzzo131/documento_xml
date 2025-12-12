<?php

// ========================================
// 1. CONEXIÓN A LA BD
// ========================================
$conexion = new mysqli("localhost", "root", "root", "bd_xml");

if ($conexion->connect_errno) {
    die("Error al conectar a MySQL: (" . $conexion->connect_errno . ") " . $conexion->connect_error);
}

// ========================================
// 2. CARGAR XML
// ========================================
$xml = simplexml_load_file('ies_db.xml') or die("Error cargando ies_db.xml");

// ========================================
// 3. RECORRIDO DEL XML + INSERT EN TABLAS
// ========================================
foreach ($xml as $i_pe => $pe) {

    echo "<b>PE:</b> $pe->nombre<br>";

    // -------------------------
    // INSERT EN PROGRAMA ESTUDIOS
    // -------------------------
    $sql_pe = "
        INSERT INTO sigi_programa_estudios (codigo, tipo, nombre)
        VALUES ('$pe->codigo', '$pe->tipo', '$pe->nombre')
    ";
    $conexion->query($sql_pe);
    $id_pe = $conexion->insert_id;


    // -------------------------
    // PLANES DE ESTUDIO
    // -------------------------
    foreach ($pe->planes_estudio[0] as $plan) {

        echo "-- Plan: $plan->nombre<br>";

        $sql_plan = "
            INSERT INTO sigi_planes_estudio (id_programa_estudios, nombre, resolucion, fecha_registro, perfil_egresado)
            VALUES (
                '$id_pe',
                '$plan->nombre',
                '$plan->resolucion',
                '$plan->fecha_registro',
                ''
            )
        ";
        $conexion->query($sql_plan);
        $id_plan = $conexion->insert_id;


        // -------------------------
        // MÓDULOS FORMATIVOS
        // -------------------------
        foreach ($plan->modulos_formativos[0] as $modulo) {

            echo "---- Módulo: " . $modulo->descripcion . "<br>";

            $sql_mod = "
                INSERT INTO sigi_modulo_formativo (descripcion, nro_modulo, id_plan_estudio)
                VALUES (
                    '".$modulo->descripcion."',
                    '".$modulo->nro_modulo."',
                    '$id_plan'
                )
            ";
            $conexion->query($sql_mod);
            $id_modulo = $conexion->insert_id;


            // -------------------------
            // PERIODOS (SEMESTRES)
            // -------------------------
            foreach ($modulo->periodos[0] as $periodo) {

                echo "------ Semestre: " . $periodo->descripcion . "<br>";

                $sql_semestre = "
                    INSERT INTO sigi_semestre (descripcion, id_modulo_formativo)
                    VALUES ('".$periodo->descripcion."', '$id_modulo')
                ";
                $conexion->query($sql_semestre);
                $id_semestre = $conexion->insert_id;


                // -------------------------
                // UNIDADES DIDÁCTICAS
                // -------------------------
                $orden = 1;
                foreach ($periodo->unidades_didacticas[0] as $ud) {

                    echo "-------- UD: " . $ud->nombre . "<br>";

                    $sql_ud = "
                        INSERT INTO sigi_unidad_didactica
                        (nombre, id_semestre, creditos_teorico, creditos_practico, tipo, orden)
                        VALUES (
                            '".$ud->nombre."',
                            '$id_semestre',
                            '".$ud->creditos_teorico."',
                            '".$ud->creditos_practico."',
                            '".$ud->tipo."',
                            '".$orden++."'
                        )
                    ";
                    $conexion->query($sql_ud);
                }
            }
        }
    }
}

echo "<br><br><b>IMPORTACIÓN COMPLETADA CON ÉXITO</b>";

?>
