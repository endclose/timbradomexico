<?php
require '../vendor/autoload.php';

use CfdiUtils\CfdiCreator40;
use CfdiUtils\Certificado\Certificado;

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

    private $emisor = array();
    private $receptor = array();

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
    public function __construct($pathCertificado, $pathKey, $regimenFiscal = '601')
    {
        $this->pathCertificado = $pathCertificado;
        $this->pathKey = $pathKey;

        $this->certificado = new Certificado($this->pathCertificado);

        $this->emisor = array(
            'Nombre' => $this->certificado->getName(),
            'RegimenFiscal' => $regimenFiscal
        );
    }

    /**
     * Crea el CFDI.
     */
    public function createCfdi()
    {
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
        $this->comprobante->addEmisor($this->emisor);
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
        $this->receptor = array(
            'Rfc' => $Rfc,
            'Nombre' => $Nombre,
            'UsoCFDI' => $UsoCFDI,
            'RegimenFiscalReceptor' => $RegimenFiscalReceptor,
            'DomicilioFiscalReceptor' => $DomicilioFiscalReceptor
        );
        $this->comprobante->addReceptor($this->receptor);
    }

    /**
     * Agrega un concepto al CFDI.
     *
     * @param array $concepto Datos del concepto.
     * @param array $traslado Datos del traslado (opcional).
     */
    public function addConcepto($concepto, $traslado = array())
    {
        $this->conceptos[] = $concepto;
        $this->comprobante->addConcepto($concepto)->addTraslado($traslado);
        $this->creator->addSumasConceptos();
    }

    /**
     * Sella el CFDI con la llave privada.
     *
     * @param string $password Contraseña de la llave privada.
     */
    public function sellarCfdi($password)
    {
        $this->creator->moveSatDefinitionsToComprobante();
        $this->creator->addSello('file://' . $this->pathKey, $password);
    }

    /**
     * Valida el CFDI y devuelve los errores encontrados.
     *
     * @return array Errores encontrados durante la validación.
     */
    public function validate()
    {
        $errors = array();
        $assets = $this->creator->validate();
        foreach ($assets->errors() as $error) {
            $errors[] = $error->getTitle() . ' ' . $error->getExplanation();
        }
        return $errors;
    }

    /**
     * Obtiene el XML del CFDI.
     *
     * @return string XML del CFDI.
     */
    public function getXml()
    {
        return $this->creator->asXml();
    }

    /**
     * Guarda el XML del CFDI en un archivo.
     *
     * @param string $path Ruta donde se guardará el archivo.
     * @param string $name Nombre del archivo (opcional, se generará automáticamente si no se proporciona).
     */
    public function saveXml($path, $name = '')
    {
        if ($name == '') {
            $name = $this->serie . '_' . $this->folio;
        }
        $res = $this->creator->saveXml($path . '/' . $name . '.xml');
    }
}