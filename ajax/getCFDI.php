<?php
if (!defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}
if (!defined('NOCSRFCHECK')) {
	define('NOCSRFCHECK', 1);
}
if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', 1);
}
if (!defined('NOLOGIN')) {
	define('NOLOGIN', 1);
}
if (!defined('NOREQUIREMENU')) {
	define('NOREQUIREMENU', 1);
}
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', 1);
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}

use PhpCfdi\CfdiToJson\JsonConverter;
use CfdiUtils\Cfdi;
use CfdiUtils\ConsultaCfdiSat\RequestParameters;
use PhpCfdi\CfdiToPdf\Catalogs\StaticCatalogs;

// Load Dolibarr environment
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/custom/timbradomexico/vendor/autoload.php';
include_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';

header('Content-Type: application/json');

$id = GETPOST('id', 'int');

if (empty($id)) {
	echo json_encode(['error' => 'ID no proporcionado']);
	exit;
}

$object = new Facture($db);

$object->fetch($id);
$object->fetch_optionals();

$xml = $conf->facture->multidir_output[$conf->entity] . '/' . $object->ref . '/' . $object->array_options['options_uuid'] . '.xml';


if (!file_exists($xml)) {
	header('Content-Type: application/json');
	echo json_encode(['error' => 'El archivo XML no existe']);
	exit;
}

$cfdi = Cfdi::newFromString(file_get_contents($xml));
$catalogs = new StaticCatalogs();

$parameters = RequestParameters::createFromCfdi($cfdi);
$qr_url = $parameters->expression();

$array_cfdi = JsonConverter::convertToArray(file_get_contents($xml));

$array_cfdi['UrlQr'] = $qr_url;
$array_cfdi['TipoDeComprobante'] = $catalogs->catTipoComprobante($array_cfdi['TipoDeComprobante']);
$array_cfdi['MetodoPago'] = $catalogs->catMetodoPago($array_cfdi['MetodoPago']);
$array_cfdi['Exportacion'] = $catalogs->catExportacion($array_cfdi['Exportacion']);
$array_cfdi['FormaPago'] = $catalogs->catFormaPago($array_cfdi['FormaPago']);
$array_cfdi['Emisor']['RegimenFiscal'] = $catalogs->catRegimenFiscal($array_cfdi['Emisor']['RegimenFiscal']);
$array_cfdi['Receptor']['UsoCFDI'] = $catalogs->catUsoCFDI($array_cfdi['Receptor']['UsoCFDI']);
$array_cfdi['Receptor']['RegimenFiscalReceptor'] = $catalogs->catRegimenFiscal($array_cfdi['Receptor']['RegimenFiscalReceptor']);

foreach($array_cfdi['Conceptos']['Concepto'] as $key => $concepto) {
	$array_cfdi['Conceptos']['Concepto'][$key]['ObjetoImp'] = $catalogs->catObjetoImp($concepto['ObjetoImp']);
	foreach($concepto['Impuestos']['Traslados']['Traslado'] as $key2 => $traslado) {
		$array_cfdi['Conceptos']['Concepto'][$key]['Impuestos']['Traslados']['Traslado'][$key2]['Impuesto'] = $catalogs->catImpuesto($traslado['Impuesto']);
	}
}

foreach($array_cfdi['Impuestos']['Traslados']['Traslado'] as $key => $traslado) {
	$array_cfdi['Impuestos']['Traslados']['Traslado'][$key]['Impuesto'] = $catalogs->catImpuesto($traslado['Impuesto']);
}


$db->close();

echo json_encode($array_cfdi);
