<?php
$xml = simplexml_load_file('ies_db.xml') or die ('Errror: no se cargo el xml. Escribe correctamente el nombre del archivo');


//echo $xml->pe_1->nombre.'</br>';
//echo $xml->pe_2->nombre;

foreach ($xml as $i_pe => $pe) {//modificando las variables para trabajar en contexto
    echo "Nombre: ".$pe->nombre . '<br>';
    echo "Código: ".$pe->codigo. '<br>';
    echo "Tipo: ".$pe->tipo. '<br>';
    foreach ($pe->planes_estudio[0] as $i_ple => $plan) {
        echo "--".$plan->nombre.'<br>';
        echo "--Resolución: ".$plan->resolucion.'<br>';
        echo "--Fecha de registro: ".$plan->fecha_registro.'<br>';
        foreach ($plan->modulos_formativos[0] as $i__mod => $modulo) {
            echo '----'.$modulo->nro_modulo.": ".$modulo->descripcion.'<br>';
//generar la base de datos del archivo .xml solo necesitan la conexion a la base de datos, y luego en cada foreach hacer los registros usando consultas
//INSERT INTO, pero antes crear a base de datos y la tabla correspondientes. insertas programa de estudios y luego obtienes su id y luego obtienes id e insertas etc
            foreach ($modulo->periodos[0] as $i_per => $periodo) {
                echo '------'.$periodo->descripcion.'<br>';
                foreach ($periodo->unidades_didacticas[0] as $i_ud => $ud) {
                    echo '--------'.$ud->nombre.' - Créditos Teórico: '.$ud->creditos_teorico.' - Créditos Práctico: '.$ud->creditos_practico.' - Tipo: '.$ud->tipo.' - Horas Semanal: '.$ud->horas_semanal.' - Horas Semestral: '.$ud->horas_semestral.'<br>';
                }
            }
        }
    }
}