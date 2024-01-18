<?php
require_once '../vendor/autoload.php';

use CfdiUtils\CfdiCreator40;
use CfdiUtils\Certificado\Certificado;
use PhpCfdi\Finkok\FinkokSettings;
use PhpCfdi\Finkok\FinkokEnvironment;
use PhpCfdi\Finkok\QuickFinkok;

/**
 * Clase Cfdi que representa un Comprobante Fiscal Digital por Internet (CFDI).
 */
class Cfdi
{

    private $pathCertificado;
    private $pathKey;

    private $certificado;
    private $creator;
    private $comprobante;
    private $password_cert;

    private QuickFinkok $quick_finkok;
    private FinkokSettings $finkok_settings;
    private $xml_timbrado;



    private $emisor = array(
        'Nombre' => '',
        'RegimenFiscal' => ''
    );
    private $receptor = array(
        'Rfc' => '',
        'Nombre' => '',
        'UsoCFDI' => '',
        'RegimenFiscalReceptor' => '',
        'DomicilioFiscalReceptor' => ''
    );

    private $conceptos = array();

    public $serie;
    public $folio;
    public $fecha;
    public $formaPago;
    public $condicionesDePago;
    public $moneda;
    public $tipoDeComprobante;
    public $metodoPago;
    public $lugarExpedicion;
    public $exportacion;

    /**
     * Constructor de la clase Cfdi.
     *
     * @param string $pathCertificado Ruta del archivo del certificado.
     * @param string $pathKey Ruta del archivo de la llave privada.
     * @param string $regimenFiscal Régimen fiscal del emisor (opcional, valor por defecto: '601').
     */
    public function __construct($pathCertificado, $pathKey,$password_cert)
    {
        $this->pathCertificado = $pathCertificado;
        $this->pathKey = $pathKey;
        $this->password_cert = $password_cert;

        $this->certificado = new Certificado($this->pathCertificado);

    }

    /**
     * Crea el CFDI.
     */
    public function createCfdi()
    {
        try {
            $this->creator = new CfdiCreator40(array(
                'Serie' => $this->serie,
                'Folio' => $this->folio,
                'Fecha' => $this->fecha,
                'FormaPago' => $this->formaPago,
                'CondicionesDePago' => $this->condicionesDePago,
                'Moneda' => $this->moneda,
                'TipoDeComprobante' => $this->tipoDeComprobante,
                'MetodoPago' => $this->metodoPago,
                'LugarExpedicion' => $this->lugarExpedicion,
                'Exportacion' => $this->exportacion
            ), $this->certificado);
            $this->comprobante = $this->creator->comprobante();
        } catch (Exception $e) {
            // Handle exception
            echo 'Error creating CFDI: ' . $e->getMessage();
        }
    }

    /**
     * Agrega un receptor al CFDI.
     *
     * @param string $Rfc RFC del receptor.
     * @param string $Nombre Nombre del receptor.
     * @param string $UsoCFDI Uso del CFDI por parte del receptor.
     * @param string $RegimenFiscalReceptor Régimen fiscal del receptor.
     * @param string $DomicilioFiscalReceptor Domicilio fiscal del receptor.
     */
    function addReceptor($Rfc, $Nombre, $UsoCFDI, $RegimenFiscalReceptor, $DomicilioFiscalReceptor)
    {
        try {
            $this->receptor['Rfc'] = $Rfc;
            $this->receptor['Nombre'] = $Nombre;
            $this->receptor['UsoCFDI'] = $UsoCFDI;
            $this->receptor['RegimenFiscalReceptor'] = $RegimenFiscalReceptor;
            $this->receptor['DomicilioFiscalReceptor'] = $DomicilioFiscalReceptor;
            $this->comprobante->addReceptor($this->receptor);
        } catch (Exception $e) {
            // Handle exception
            echo 'Error adding receptor: ' . $e->getMessage();
        }
    }

    function addEmisor($Nombre, $RegimenFiscal)
    {
        try {
            $this->emisor['Nombre'] = $Nombre;
            $this->emisor['RegimenFiscal'] = $RegimenFiscal;
            $this->comprobante->addEmisor($this->emisor);
        } catch (Exception $e) {
            // Handle exception
            echo 'Error adding emisor: ' . $e->getMessage();
        }
    }

    /**
     * Agrega un concepto al CFDI.
     *
     * @param array $concepto Datos del concepto.
     * @param array $traslado Datos del traslado (opcional).
     */
    public function addConcepto($concepto, $traslado = array())
    {
        try {
            $this->conceptos[] = $concepto;
            $this->comprobante->addConcepto($concepto)->addTraslado($traslado);
            $this->creator->addSumasConceptos();
        } catch (Exception $e) {
            // Handle exception
            echo 'Error adding concepto: ' . $e->getMessage();
        }
    }

    /**
     * Sella el CFDI con la llave privada.
     *
     * @param string $password Contraseña de la llave privada.
     */
    public function sellarCfdi()
    {
        try {
            $this->creator->moveSatDefinitionsToComprobante();
            $this->creator->addSello('file://' . $this->pathKey, $this->password_cert);
        } catch (Exception $e) {
            // Handle exception
            echo 'Error sealing CFDI: ' . $e->getMessage();
        }
    }

    /**
     * Valida el CFDI y devuelve los errores encontrados.
     *
     * @return array Errores encontrados durante la validación.
     */
    public function validate()
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

    /**
     * Obtiene el XML del CFDI.
     *
     * @return string XML del CFDI.
     */
    public function getXml()
    {
        try {
            return $this->creator->asXml();
        } catch (Exception $e) {
            // Handle exception
            echo 'Error getting XML: ' . $e->getMessage();
        }
    }

    /**
     * Guarda el XML del CFDI en un archivo.
     *
     * @param string $path Ruta donde se guardará el archivo.
     * @param string $name Nombre del archivo (opcional, se generará automáticamente si no se proporciona).
     */
    public function saveXml($path, $name = '')
    {
        try {
            if ($name == '') {
                $name = $this->serie . '_' . $this->folio;
            }
            $res = $this->creator->saveXml($path . '/' . $name . '.xml');
        } catch (Exception $e) {
            // Handle exception
            echo 'Error saving XML: ' . $e->getMessage();
        }
    }
    public function createFromObject( Facture $object, Conf $conf){
        try{
            if(empty($conf->global->TIMBRADOMEXICO_EMISOR_REGIMEN) || empty($conf->global->TIMBRADOMEXICO_EMISOR_RAZON_SOCIAL)){
                return 'No se ha configurado el emisor';
            }
            $object->fetch_thirdparty();

            $this->serie = 'A'; // TODO change this to a variable configurable from dolibarr
            $this->folio = str_contains($object->ref,'PROV') ? $object->getNextNumRef($conf->mysoc) : $object->ref;
            $this->fecha = dol_print_date($object->date_creation, '%Y-%m-%dT%H:%M:%S','tzuser');
            $this->formaPago = $object->array_options['options_formapago'];
            $this->metodoPago = $object->array_options['options_metodopago'];
            $this->tipoDeComprobante = $object->array_options['options_tipocomprobante'];
            $this->lugarExpedicion = '20928'; //TODO: Change from the dolibarr entity address
            $this->moneda = $object->multicurrency_code;
            $this->condicionesDePago = 'Contado'; // TODO Fill this from the object conditions
            $this->exportacion = $object->array_options['options_exportacion'];
    
            $this->createCfdi();
    
            $this->emisor['Nombre'] = $conf->global->TIMBRADOMEXICO_EMISOR_RAZON_SOCIAL;
            $this->emisor['RegimenFiscal'] = $conf->global->TIMBRADOMEXICO_EMISOR_REGIMEN;
            $this->addEmisor($this->emisor['Nombre'], $this->emisor['RegimenFiscal']);

            $this->receptor['Rfc'] = $object->thirdparty->idprof1;
            $this->receptor['Nombre'] = $object->thirdparty->name;
            $this->receptor['UsoCFDI'] = $object->array_options['options_usocfdi'];
            $this->receptor['RegimenFiscalReceptor'] = $object->array_options['options_regimenfiscalreceptor'];
            $this->receptor['DomicilioFiscalReceptor'] = $object->thirdparty->zip;

            $this->addReceptor(
                $this->receptor['Rfc'], 
                $this->receptor['Nombre'],
                $this->receptor['UsoCFDI'],
                $this->receptor['RegimenFiscalReceptor'],
                $this->receptor['DomicilioFiscalReceptor']
            );

            foreach($object->lines as $line){
                $line->fetch_product();
                $concepto = array(
                    'ObjetoImp'=> $line->product->array_options['options_objetoimp'],
                    'ClaveProdServ' => $line->product->array_options['options_prodserv'],
                    'NoIdentificacion' => $line->product->ref,
                    'Cantidad' => $line->qty,
                    'ClaveUnidad' => $line->product->array_options['options_claveunidad'],
                    'Descripcion' => $line->libelle,
                    'ValorUnitario' => $line->multicurrency_subprice,
                    'Importe' => $line->multicurrency_total_ht,
                );
                if($line->tva_tx > 0){
                    $impuestos = array(
                        'Impuesto' => '002',
                        'TipoFactor' => 'Tasa',
                        'TasaOCuota' => number_format($line->tva_tx/100, 6, '.', ''),
                        'Base'=> number_format($line->total_ht, 2, '.', ''),
                        'Importe' => number_format($line->total_tva,2, '.', '')
                    );
                    $this->addConcepto($concepto, $impuestos);
                }else{
                    $this->addConcepto($concepto);
                }

            }
            $this->sellarCfdi();

            $validations = $this->validate();
            if(!empty($validations)){
                throw new Exception('El CFDI no es válido: '.implode(', ',$validations));
            }

            return true;

        }catch(Exception $e){
            return $e->getMessage();
        }


    }
    public function setFinkokCredentials($username, $password, $environment = 'production')
    {
        $environment = $environment === 'production' ?
            FinkokEnvironment::makeProduction() :
            FinkokEnvironment::makeDevelopment();

        $this->finkok_settings = new FinkokSettings($username, $password, $environment);
        $this->quick_finkok = new QuickFinkok($this->finkok_settings);
    }
    public function stamp($xml){
        try{
            $result = $this->quick_finkok->stamp($xml);
            $this->xml_timbrado = $result->xml();
            return $result;
        }catch(Exception $e){
            return $e;
        }
    }
}