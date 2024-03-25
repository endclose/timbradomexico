<?php
declare(strict_types=1);

use CfdiUtils\Cleaner\Cleaner;
use CfdiUtils\Nodes\XmlNodeUtils;
use PhpCfdi\CfdiToPdf\Builders\Html2PdfBuilder;
use PhpCfdi\CfdiToPdf\CfdiDataBuilder;
use PhpCfdi\CfdiToPdf\Converter;

require '../vendor/autoload.php';
require '../../../main.inc.php';
include_once DOL_DOCUMENT_ROOT . '/custom/timbradomexico/class/facture_mexico.class.php';

$id = GETPOST('id', 'int');

$facture = new FactureMexico($db);
$res = $facture->fetch($id);

if ($res < 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'El archivo XML no existe']);
    exit;
}

$xml = $conf->facture->multidir_output[$conf->entity] . '/' . $facture->ref . '/' . $facture->array_options['options_uuid'] . '.xml';

if (!file_exists($xml)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'El archivo XML no existe']);
    exit;
}

$xml = Cleaner::staticClean(file_get_contents($xml));

$comprobante = XmlNodeUtils::nodeFromXmlString($xml);
$cfdiData = (new CfdiDataBuilder())->build($comprobante);

$converter = new Converter(new Html2PdfBuilder());

$pdf = $conf->facture->multidir_output[$conf->entity] . '/' . $facture->ref . '/' . $facture->array_options['options_uuid'] . '.pdf';

$converter->createPdfAs($cfdiData, $pdf);

header('Content-Type: application/json');
echo json_encode(['pdf' => $pdf]);

$db->close();
