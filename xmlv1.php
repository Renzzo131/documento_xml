<?php
$ies = [];
//--------------UNIDADES DIDÁCTICAS--------------
$udp1 = [
    'ANÁLISIS Y DISEÑO DE SISTEMAS',
    'FUNDAMENTOS DE PROGRAMACIÓN',
    'REDES E INTERNET',
    'INTRODUCCIÓN DE BASE DE DATOS',
    'ARQUITECTURA DE COMPUTADORAS',
    'COMUNICACIÓN ORAL',
    'APLICACIONES EN INTERNET'
];

$udp2 = [
    'OFIMÁTICA',
    'INTERPRETACIÓN Y PRODUCCIÓN DE TEXTOS',
    'METODOLOGÍA DE DESARROLLO DE SOFTWARE',
    'PROGRAMACIÓN ORIENTADA A OBJETOS',
    'ARQUITECTURA DE SERVIDORES WEB',
    'APLICACIONES SISTEMATIZADAS',
    'TALLER DE BASE DE DATOS'
];

$udp3 = [
    'ADMINISTRACIÓN DE BASE DE DATOS',
    'PROGRAMACIÓN DE APLICACIONES WEB',
    'DISEÑO DE INTERFACES WEB',
    'PRUEBAS DE SOFTWARE',
    'INGLÉS PARA LA COMUNICACIÓN ORAL'
];

$udp4 = [
    'DESARROLLO DE ENTORNOS WEB',
    'PROGRAMACIÓN DE SOLUCIONES WEB',
    'PROYECTOS DE SOFTWARE',
    'SEGURIDAD EN APLICACIONES WEB',
    'COMPRENSIÓN Y REDACCIÓN EN INGLÉS',
    'COMPORTAMIENTO ÉTICO'
];

$udp5 = [
    'PROGRAMACIÓN DE APLICACIONES MÓVILES',
    'MARKETING DIGITAL',
    'DISEÑO DE SOLUCIONES WEB',
    'GESTIÓN Y ADMINISTRACIÓN DE SITIOS WEB',
    'DIAGRAMACIÓN DIGITAL',
    'SOLUCIÓN DE PROBLEMAS',
    'OPORTUNIDADES DE NEGOCIO'
];

$udp6 = [
    'PLATAFORMA DE SERVICIOS WEB',
    'ILUSTRACIÓN GRÁFICA Y DIGITAL',
    'ADMINISTRACIÓN DE SERVIDORES WEB',
    'COMERCIO ELECTRÓNICO',
    'PLAN DE NEGOCIOS'
];

//--------------PERDIODOS--------------
$p1=[];
$p1['nombre'] = 'I';
$p1['unidades_didacticas'] = $udp1;

$p2=[];
$p2['nombre'] = 'II';
$p2['unidades_didacticas'] = $udp2;

$p3=[];
$p3['nombre'] = 'III';
$p3['unidades_didacticas'] = $udp3;

$p4=[];
$p4['nombre'] = 'IV';
$p4['unidades_didacticas'] = $udp4;

$p5=[];
$p5['nombre'] = 'V';
$p5['unidades_didacticas'] = $udp5;

$p6=[];
$p6['nombre'] = 'VI';
$p6['unidades_didacticas'] = $udp6;

//--------------MÓDULOS--------------
$m1 = array();
$m1['nombre'] = 'ANÁLISIS Y DISEÑO DE SISTEMAS WEB';
$m1['periodos'] = [$p1, $p2];

$m2 = array();
$m2['nombre'] = 'DESARROLLO DE APLICACIONES WEB';
$m2['periodos'] = [$p3, $p4];

$m3 = array();
$m3['nombre'] = 'DISEÑO DE SERVICIOS WEB';
$m3['periodos'] = [$p5, $p6];

//--------------PROGRAMAS DE ESTUDIOS--------------
$pe1 = array();
$pe1['nombre'] = 'DISEÑO Y PROGRAMACIÓN WEB';
$pe1['modulos'] = [$m1, $m2, $m3];

$pe2 = array();
$pe2['nombre'] = 'ENFERMERÍA TÉCNICA';
$pe2['modulos'] = [];


$pe3 = array();
$pe3['nombre'] = 'INDUSTRIAS DE ALIMENTOS Y BEBIDAS';
$pe3['modulos'] = [];


$pe4 = array();
$pe4['nombre'] = 'MECATRÓNICA AUTOMOTRIZ';
$pe4['modulos'] = [];


$pe5 = array();
$pe5['nombre'] = 'PRODUCCIÓN AGROPECUARIA';
$pe5['modulos'] = [];


$ies['nombre'] = 'IES Pub HUANTA';
$ies['programas_de_estudios'] = [$pe1, $pe2, $pe3, $pe4, $pe5];

$xml = new DOMDocument('1.0', 'UTF-8');
$xml->formatOutput = true;

$et1 = $xml->createElement('ies');
$xml->appendChild($et1);


$nombre_ies = $xml->createElement('nombre', $ies['nombre']);
$programas_ies = $xml->createElement('programas_de_estudios');
$et1->appendChild($nombre_ies);
$et1->appendChild($programas_ies);
foreach ($ies['programas_de_estudios'] as $indice => $PEs) {
    $num_pe = $xml->createElement("pe".($indice+1));
    $nombre_pe = $xml->createElement("nombre", $PEs['nombre']);
    foreach ($PEs['modulos'] as $indice_modulo => $Modulo) {
        $num_mod = $xml->createElement("mod".($indice_modulo+1));
        $nom_mod = $xml->createElement("nombre", $Modulo['nombre']);
        foreach ($Modulo['periodos'] as $indice_periodo => $Periodo) {
            $num_per = $xml->createElement("per".($indice_periodo+1));
            $nom_per = $xml->createElement("nombre", $Periodo['nombre']);
            $uds = $xml->createElement("unidades_didacticas");
            foreach ($Periodo['unidades_didacticas'] as $indice_ud => $Ud) {
                $num_ud = $xml->createElement("ud".($indice_ud+1));
                $nom_ud = $xml->createElement("nombre", $Ud);
                $num_ud->appendChild($nom_ud);
                $uds->appendChild($num_ud);
            }
            $num_per->appendChild($nom_per);
            $num_per->appendChild($uds);
            $num_mod->appendChild($num_per);
        }
        $num_mod->appendChild($nom_mod);
        $num_pe->appendChild($num_mod);
    }
    $num_pe->appendChild($nombre_pe);
    $programas_ies -> appendChild($num_pe);
}




$archivo = "ies.xml";
$xml->save($archivo);