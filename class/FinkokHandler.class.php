<?php

use PhpCfdi\Finkok\FinkokSettings;
use PhpCfdi\Finkok\FinkokEnvironment;
use PhpCfdi\Finkok\QuickFinkok;
use PhpCfdi\Finkok\Services\Utilities\DownloadXmlCommand;
use PhpCfdi\Finkok\Services\Utilities\DownloadXmlService;

require_once '../vendor/autoload.php';

class FinkokHandler
{

    private $settings;
    private $quick_finkok;
    private $xmlTimbrado;

    public function __construct($username, $password, $environment = 'production')
    {
        $environment = $environment === 'production' ?
            FinkokEnvironment::makeProduction() :
            FinkokEnvironment::makeDevelopment();

        $this->settings = new FinkokSettings($username, $password, $environment);
        $this->quick_finkok = new QuickFinkok($this->settings);
    }

    public function stamp($xml)
    {
        $result = $this->quick_finkok->stamp($xml);
        $this->xmlTimbrado = $result->xml();
        return $result;
    }
    public function saveXmlTimbrado($path, $name)
    {
        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }
        file_put_contents($path . '/' . $name . '.xml', $this->xmlTimbrado);
    }
    public function recoverXmlTimbrado($uuid, $rfc_emisor, $type = 'I')
    {
        $downloadXMLSrv = new DownloadXmlService($this->settings);
        $result = $downloadXMLSrv->downloadXml(
            new DownloadXmlCommand(
                $uuid,
                $rfc_emisor,
                $type
            )
        );
        if (empty($result->error())) {
            $this->xmlTimbrado = $result->xml();
            return true;
        } else {
            return $result->error();
        }
    }
}
