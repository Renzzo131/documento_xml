<?php

$conexion = new mysqli("localhost", "root", "root", "para_xml");
if ($conexion->connect_errno) {
    echo "Fallo al conectar a MySQL: (" . $conexion->connect_errno . ") " . $conexion->connect_error;
}
$xml = new DOMDocument('1.0', 'UTF-8');
$xml->formatOutput = true;

$et1 = $xml->createElement('programas_estudio');
$xml->appendChild($et1);

$consulta = "SELECT * FROM sigi_programa_estudios";
$resultado = $conexion->query($consulta);
while ($pe = mysqli_fetch_assoc($resultado)) {
    echo $pe['nombre'] . "<br>";
    $num_pe = $xml->createElement('pe_' . $pe['id']);
    $codigo_pe = $xml->createElement('codigo', $pe['codigo']);
    $num_pe->appendChild($codigo_pe);
    $tipo_pe = $xml->createElement('tipo', $pe['tipo']);
    $num_pe->appendChild($tipo_pe);
    $nombre_pe = $xml->createElement('nombre', $pe['nombre']);
    $num_pe->appendChild($nombre_pe);
    $et_plan = $xml->createElement('planes_estudio');
    $consulta_plan = "SELECT * FROM sigi_planes_estudio WHERE id_programa_estudios=" . $pe['id'];
    $resultado_plan = $conexion->query($consulta_plan);
    while ($plan = mysqli_fetch_assoc($resultado_plan)) {
        echo "--" . $plan['nombre'] . "<br>";
        $num_plan = $xml->createElement('plan_' . $plan['id']);
        $nombre_plan = $xml->createElement('nombre', $plan['nombre']);
        $num_plan->appendChild($nombre_plan);
        $resolucion_plan = $xml->createElement('resolucion', $plan['resolucion']);
        $num_plan->appendChild($resolucion_plan);
        $fecha_registro_plan = $xml->createElement('fecha_registro', $plan['fecha_registro']);
        $num_plan->appendChild($fecha_registro_plan);
        $et_modulos = $xml->createElement('modulos_formativos');
        $consulta_mod = "SELECT * FROM sigi_modulo_formativo WHERE id_plan_estudio=" . $plan['id'];
        $resultado_mod = $conexion->query($consulta_mod);
        while ($modulo = mysqli_fetch_assoc($resultado_mod)) {
            echo "----" . $modulo['descripcion'] . "<br>";
            $num_modulo = $xml->createElement('modulo_' . $modulo['id']);
            $descripcion_mod = $xml->createElement('descripcion', $modulo['descripcion']);
            $num_modulo->appendChild($descripcion_mod);
            $nro_modulo_mod = $xml->createElement('nro_modulo', $modulo['nro_modulo']);
            $num_modulo->appendChild($nro_modulo_mod);
            $et_periodos = $xml->createElement('periodos');
            $consulta_per = "SELECT * FROM sigi_semestre WHERE id_modulo_formativo=" . $modulo['id'];
            $resultado_per = $conexion->query($consulta_per);
            while ($per = mysqli_fetch_assoc($resultado_per)) {
                echo "------" . $per['descripcion'] . "<br>";
                $num_per = $xml->createElement('periodo_' . $per['id']);
                $descripcion_per = $xml->createElement('descripcion', $per['descripcion']);
                $num_per->appendChild($descripcion_per);
                $et_uds = $xml->createElement('unidades_didacticas');
                $consulta_uds = "SELECT * FROM sigi_unidad_didactica WHERE id_semestre=" . $per['id'];
                $resultado_uds = $conexion->query($consulta_uds);
                while ($uds = mysqli_fetch_assoc($resultado_uds)) {
                    echo "--------" . $uds['nombre'] . "<br>";
                    $num_ud = $xml->createElement('ud_' . $uds['orden']);
                    $nombre_ud = $xml->createElement('nombre', $uds['nombre']);
                    $num_ud->appendChild($nombre_ud);
                    $creditos_teorico = $xml->createElement('creditos_teorico', $uds['creditos_teorico']);
                    $num_ud->appendChild($creditos_teorico);
                    $creditos_practico = $xml->createElement('creditos_practico', $uds['creditos_practico']);
                    $num_ud->appendChild($creditos_practico);
                    $tipo = $xml->createElement('tipo', $uds['tipo']);
                    $num_ud->appendChild($tipo);
                    $hr_semanal = ($uds['creditos_teorico']*1)+($uds['creditos_practico']*2);
                    $hr_sem = $xml->createElement('horas_semanal', $hr_semanal);
                    $num_ud->appendChild($hr_sem);
                    $hr_semestral = $xml->createElement('horas_semestral', $hr_semanal*16);
                    $num_ud->appendChild($hr_semestral);
                    $et_uds->appendChild($num_ud);
                }
                $num_per->appendChild($et_uds);
                $et_periodos->appendChild($num_per);
            }
            $num_modulo->appendChild($et_periodos);
            $et_modulos->appendChild($num_modulo);
        }
        $num_plan->appendChild($et_modulos);
        $et_plan->appendChild($num_plan);
    }
    $num_pe->appendChild($et_plan);
    $et1->appendChild($num_pe);
}

$archivo = "ies_db.xml";
$xml->save($archivo);
