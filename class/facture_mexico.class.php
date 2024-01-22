<?php

require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT . '/custom/timbradomexico/vendor/autoload.php';

use CfdiUtils\Certificado\Certificado;
use CfdiUtils\CfdiCreator40;
use CfdiUtils\Elements\Cfdi40\Comprobante;
use PhpCfdi\Finkok\FinkokEnvironment;
use PhpCfdi\Finkok\QuickFinkok;
use PhpCfdi\Finkok\FinkokSettings;
use PhpCfdi\Finkok\Services\Utilities\DownloadXmlCommand;
use PhpCfdi\Finkok\Services\Utilities\DownloadXmlService;

class FactureMexico extends Facture
{
    public $pathCertificado;
    public $pathKey;
    public $password_cert;
    public $my_soc;

    public $certificado;
    public CfdiCreator40 $creator;
    public Comprobante $comprobante;

    public QuickFinkok $quick_finkok;
    public FinkokSettings $finkok_settings;

    public $emisorRegimen;
    public $emisorRazonSocial;

    public $serie;
    public $folio;
    public $fechaTimbrado;
    public $formaPago;
    public $condicionesDePago;
    public $tipoDeComprobante;
    public $metodoPago;
    public $lugarExpedicion;
    public $exportacion;

    public $xml;
    public $xml_timbrado;

    function __construct($db)
    {
        global $conf;

        $this->db = $db;

        $this->pathCertificado = $conf->global->TIMBRADOMEXICO_PATH_CERTIFICADO;
        $this->pathKey = $conf->global->TIMBRADOMEXICO_PATH_LLAVE_PRIVADA;
        $this->password_cert = $conf->global->TIMBRADOMEXICO_LLAVE_PRIVADA_PASSWORD;
        $this->emisorRegimen = $conf->global->TIMBRADOMEXICO_EMISOR_REGIMEN;
        $this->emisorRazonSocial = $conf->global->TIMBRADOMEXICO_EMISOR_RAZON_SOCIAL;

        $this->my_soc = $conf->my_soc;

        // $this->finkok_settings = new FinkokSettings(
        //     $conf->global->TIMBRADOMEXICO_FINKOK_USERNAME,
        //     $conf->global->TIMBRADOMEXICO_FINKOK_PASSWORD,
        //     $conf->global->TIMBRADOMEXICO_FINKOK_ENV === 'prod' ? 
        //         FinkokEnvironment::makeProduction() : 
        //         FinkokEnvironment::makeDevelopment()
        // );
        $this->finkok_settings = new FinkokSettings(
            'benjamin.bailon@outlook.com',
            'Tier299S?',
            FinkokEnvironment::makeDevelopment()
        );
        $this->quick_finkok = new QuickFinkok($this->finkok_settings);
    }

    public function createCFDI()
    {
        try {
            $this->creator = new CfdiCreator40(
                array(
                    'Serie' => $this->serie(),
                    'Folio' => $this->folio(),
                    'Fecha' => $this->fechaTimbrado(),
                    'FormaPago' => $this->formaPago(),
                    'CondicionesDePago' => $this->condicionesDePago(),
                    'Moneda' => $this->moneda(),
                    'TipoDeComprobante' => $this->tipoComprobante(),
                    'MetodoPago' => $this->metodoPago(),
                    'LugarExpedicion' => $this->lugarExpedicion(),
                    'Exportacion' => $this->exportacion(),
                ),
                $this->certificado()
            );
            $this->comprobante = $this->creator->comprobante();
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function serie()
    {
        $this->serie = $this->getNextNumRef($this->my_soc);
        return $this->serie;
    }

    public function folio()
    {
        switch ($this->type) {
            case $this::TYPE_STANDARD:
            case $this::TYPE_REPLACEMENT:
                $this->folio = 'A';
                break;
            case $this::TYPE_CREDIT_NOTE:
                $this->folio = 'NC';
                break;
        }
        return $this->folio;
    }

    public function tipoComprobante()
    {
        switch ($this->type) {
            case $this::TYPE_STANDARD:
            case $this::TYPE_REPLACEMENT:
                $this->tipoDeComprobante = 'I';
                break;
            case $this::TYPE_CREDIT_NOTE:
                $this->tipoDeComprobante = 'E';
                break;
        }
        return $this->tipoDeComprobante;
    }

    public function fechaTimbrado()
    {
        $this->fechaTimbrado = dol_print_date($this->date_creation, '%Y-%m-%dT%H:%M:%S', 'tzuser');
        return $this->fechaTimbrado;
    }

    public function formaPago()
    {
        $this->formaPago = $this->array_options['options_formapago'] ? $this->array_options['options_formapago'] : '99';
        return $this->formaPago;
    }

    public function moneda()
    {
        return $this->multicurrency_code;
    }

    // TODO: Agregar las condiciones de pago del objecto
    public function condicionesDePago()
    {
        $this->condicionesDePago = $this->array_options['options_condicionespago'] ? $this->array_options['options_condicionespago'] : 'Contado';
        return $this->condicionesDePago;
    }

    public function metodoPago()
    {
        $this->metodoPago = $this->array_options['options_metodopago'] ? $this->array_options['options_metodopago'] : 'PUE';
        return $this->metodoPago;
    }

    public function lugarExpedicion()
    {
        global $conf;

        $this->lugarExpedicion = $this->array_options['options_lugarexpedicion'] ?
            $this->array_options['options_lugarexpedicion'] :
            $conf->global->MAIN_INFO_SOCIETE_ZIP;
        return $this->lugarExpedicion;
    }

    public function exportacion()
    {
        $this->exportacion = $this->array_options['options_exportacion'] ?
            $this->array_options['options_exportacion'] :
            '02';
        return $this->exportacion;
    }

    public function certificado()
    {
        $this->certificado = new Certificado($this->pathCertificado);
        return $this->certificado;
    }

    public function addEmisor()
    {
        $this->comprobante->addEmisor(
            array(
                'Nombre' => $this->emisorRazonSocial,
                'RegimenFiscal' => $this->emisorRegimen,
            )
        );
    }

    public function addReceptor()
    {
        if (empty($this->thirdparty))
            $this->fetch_thirdparty();
        $this->comprobante->addReceptor(
            array(
                'Nombre' => $this->thirdparty->name,
                'Rfc' => $this->thirdparty->idprof1,
                'UsoCFDI' => $this->array_options['options_usocfdi'],
                'RegimenFiscalReceptor' => $this->array_options['options_regimenfiscalreceptor'],
                'DomicilioFiscalReceptor' => $this->thirdparty->zip
            )
        );
    }

    function fillConceptos()
    {
        $this->fetch_lines();
        foreach ($this->lines as $line) {
            $line->fetch_product();
            $concepto = array(
                'ObjetoImp' => $line->product->array_options['options_objetoimp'],
                'ClaveProdServ' => $line->product->array_options['options_prodserv'],
                'NoIdentificacion' => $line->product->ref,
                'Cantidad' => $line->qty,
                'ClaveUnidad' => $line->product->array_options['options_claveunidad'],
                'Descripcion' => $line->libelle,
                'ValorUnitario' => abs($line->multicurrency_subprice),
                'Importe' => abs($line->multicurrency_total_ht),
            );
            if ($line->tva_tx > 0) {
                $impuestos = array(
                    'Impuesto' => '002',
                    'TipoFactor' => 'Tasa',
                    'TasaOCuota' => number_format(abs($line->tva_tx) / 100, 6, '.', ''),
                    'Base' => number_format(abs($line->total_ht), 2, '.', ''),
                    'Importe' => number_format(abs($line->total_tva), 2, '.', ''),
                );
            }
            $this->addConcepto($concepto, $impuestos);
        }
    }

    public function addConcepto($concepto, $impuestos = array())
    {
        $this->comprobante->addConcepto($concepto, $impuestos)
            ->addTraslado($impuestos);
        $this->creator->addSumasConceptos();
    }

    public function sellarCfdi()
    {
        $this->creator->addSello('file://' . $this->pathKey, $this->password_cert);
    }

    public function validateCFDI()
    {
        try {
            $errors = array();
            $assets = $this->creator->validate();
            foreach ($assets->errors() as $error) {
                $errors[] = $error->getTitle() . ' ' . $error->getExplanation();
            }
            return $errors;
        } catch (Exception $e) {
            // Handle exception
            echo 'Error validating CFDI: ' . $e->getMessage();
        }
    }

    public function getXML()
    {
        $this->xml = $this->creator->asXml();
        return $this->xml;
    }

    public function isStamped()
    {
        if (empty($this->array_options)) {
            $this->fetch_optionals();
        }
        return !empty($this->array_options['options_timbrada']);
    }

    function createXMLFromObject()
    {
        if (empty($this->ref)) {
            return false;
        }

        $this->createCFDI();
        $this->addEmisor();
        $this->addReceptor();
        $this->fillConceptos();
        $this->sellarCfdi();
        return $this->getXML();
    }
    function stamp()
    {
        global $user;

        if ($this->isStamped()) {
            $this->error = 'El CFDI ya ha sido timbrado.';
            return false;
        }
        if (empty($this->xml)) {
            $this->error = 'No se ha generado el XML.';
            return false;
        }

        if (count($this->validateCFDI()) > 0) {
            $errors = $this->validateCFDI();
            $this->error = implode(', ', $errors);
            return false;
        }

        $stampingResult = $this->quick_finkok->stamp($this->xml);
        if ($stampingResult->hasAlerts()) {
            $this->error = $stampingResult->alerts()->first()->message();
            return false;
        } else {
            $this->xml_timbrado = $stampingResult->xml();
            $this->array_options['options_timbrada'] = 1;
            $this->array_options['options_uuid'] = $stampingResult->uuid();
            $this->update($user, 1);
            $this->validate($user);

            return true;
        }
    }
    function saveXML($stamped = false)
    {
        global $conf;
        $path = $conf->facture->multidir_output[$conf->entity] . '/' . $this->ref . '/';
        if ($stamped) {
            $xml = $this->xml_timbrado;
            $path .= $this->array_options['options_uuid'] . '.xml';
        } else {
            $path .= $this->ref . '[NO VALIDO].xml';
            $xml = $this->xml;
        }

        if (!empty($xml)) {
            $file = fopen($path, 'w');
            fwrite($file, $xml);
            fclose($file);
            return true;
        } else {
            return false;
        }
    }

    public function getXMLFromFinkok(){
        global $conf;
        if (empty($this->array_options['options_uuid'])) {
            $this->error = 'No se ha proporcionado el UUID del CFDI a recuperar.';
            return false;
        }

        $downloadXMLSrv = new DownloadXmlService($this->finkok_settings);
        $result = $downloadXMLSrv->downloadXml(
            new DownloadXmlCommand(
                $this->array_options['options_uuid'],
                'EKU9003173C9',
                $this->tipoComprobante()
            )
        );
        if (empty($result->error())) {
            $this->xml_timbrado = $result->xml();
            return true;
        } else {
            $this->error = $result->error();
            return false;
        }
    }
}
