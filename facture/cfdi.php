<?php

require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/custom/timbradomexico/vendor/autoload.php';
include_once DOL_DOCUMENT_ROOT . '/custom/timbradomexico/class/facture_mexico.class.php';

$id = GETPOST('id', 'int');

$object = new FactureMexico($db);
$res = $object->fetch($id);

if ($res < 0) {
    accessforbidden('Error');
}

llxHeader();
$object->fetch_optionals();

$res = $object->getXMLFromSystem();

if (!$res) {
    accessforbidden('Error: '. $object->error);
}else{
    print_r($object->xml_timbrado);
}


llxFooter();
$db->close();


