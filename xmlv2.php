<?php

$conexion = new mysqli("localhost", "root", "root", "para_xml");
if($conexion->connect_errno){
    echo "Falló al conectar MySQL: (".$conexion->connect_errno.")".$conexion->connect_error;
}
$xml = new DOMDocument('1.0','UTF-8');
$xml->formatOutput = true;

$et1 = $xml->createElement('programas_estudio');
$xml->appendChild($et1);


$consulta = "SELECT * FROM sigi_programa_estudios";
$resultado = $conexion->query($consulta);
while ($pe = mysqli_fetch_assoc($resultado)) {
    echo $pe['nombre']."</br>";
    $num_pe = $xml->createElement('pe_'.$pe['id']);
    $codigo_pe = $xml->createElement('codigo',$pe['codigo']);
    $num_pe->appendChild($codigo_pe);
    $tipo_pe = $xml->createElement('tipo',$pe['tipo']);
    $num_pe->appendChild($tipo_pe);
    $nombre_pe = $xml->createElement('nombre',$pe['nombre']);
    $num_pe->appendChild($nombre_pe);
    
    $et_plan = $xml->createElement('planes_estudio');
    $consulta_plan = "SELECT * FROM sigi_planes_estudio WHERE id_programa_estudios=".$pe['id'];
    $resultado_plan = $conexion->query($consulta_plan);
    while ($pl = mysqli_fetch_assoc($resultado_plan)) {
        echo $pl['nombre']."</br>";
        $plan = $xml->createElement('plan_'.$pl['id']);
        $nombre_plan = $xml->createElement('nombre',$pl['nombre']);
        $plan->appendChild($nombre_plan);
        $resolucion = $xml->createElement('resolucion',$pl['resolucion']);
        $plan->appendChild($resolucion);
        $fecha = $xml->createElement('fecha',$pl['fecha_registro']);
        $plan->appendChild($fecha);

        $et_mod = $xml->createElement('modulos_formativos');
        $consulta_mod = "SELECT * FROM sigi_modulo_formativo WHERE id_plan_estudio=".$pl['id'];
        $resultado_mod = $conexion->query($consulta_mod);
        while ($mod = mysqli_fetch_assoc($resultado_mod)) {
            echo $mod['descripcion']."</br>";
            $modulo = $xml->createElement('modulo_'.$mod['id']);
            $descripcion_mod = $xml->createElement('descripcion',$mod['descripcion']);
            $modulo->appendChild($descripcion_mod);
            $nro_mod = $xml->createElement('numero_modulo',$mod['nro_modulo']);
            $modulo->appendChild($nro_mod);

            $et_peri = $xml->createElement('periodos');
            $consulta_peri = "SELECT * FROM sigi_semestre WHERE id_modulo_formativo=".$mod['id'];
            $resultado_peri = $conexion->query($consulta_peri);
            while ($per = mysqli_fetch_assoc($resultado_peri)) {
                echo $per['descripcion']."</br>";
                $periodo = $xml->createElement('periodo_'.$per['id']);
                $descripcion_per = $xml->createElement('descripcion',$per['descripcion']);
                $periodo->appendChild($descripcion_per);
                
                $et_ud = $xml->createElement('unidades_didacticas');
                $consulta_ud = "SELECT * FROM sigi_unidad_didactica WHERE id_semestre=".$per['id'];
                $resultado_ud = $conexion->query($consulta_ud);
                while ($ud = mysqli_fetch_assoc($resultado_ud)) {
                    echo $ud['nombre']."</br>";
                    $unidad = $xml->createElement('ud_'.$ud['id']);
                    $nombre_ud = $xml->createElement('nombre',$ud['nombre']);
                    $unidad->appendChild($nombre_ud);
                    $cred_te = $xml->createElement('creditos_teorico',$ud['creditos_teorico']);
                    $unidad->appendChild($cred_te);
                    $cred_pr = $xml->createElement('creditos_practico',$ud['creditos_practico']);
                    $unidad->appendChild($cred_pr);
                    $tipo = $xml->createElement('tipo',$ud['tipo']);
                    $unidad->appendChild($tipo);
                    $orden = $xml->createElement('orden',$ud['orden']);
                    $unidad->appendChild($orden);
                    $horas_semana = ($ud['creditos_teorico'] * 1) + ($ud['creditos_practico'] * 2);
                    $hs = $xml->createElement('horas_semana', $horas_semana);
                    $unidad->appendChild($hs);
                    $horas_semestre = $horas_semana * 16;
                    $hs_sem = $xml->createElement('horas_semestre', $horas_semestre);
                    $unidad->appendChild($hs_sem);

                    $et_ud->appendChild($unidad);
                }
                $periodo->appendChild($et_ud);
                $et_peri->appendChild($periodo);
                $modulo->appendChild($et_peri);
            }
            
            $et_mod->appendChild($modulo);
            $plan->appendChild($et_mod);
        }
        $et_plan->appendChild($plan);
    }
    $num_pe->appendChild($et_plan);
    $et1->appendChild($num_pe);
}


$archivo = "ies_db.xml";
$xml->save($archivo);