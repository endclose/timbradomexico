<?php

function createSocieteExtrafields()
{
    global $conf, $db;

    if (!class_exists('ExtraFields')) {
        include_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
    }

    $extrafields = new ExtraFields($db);
    // Regimen fiscal
    $result1 = $extrafields->addExtraField(
        'regimenfiscal',
        "Régimen fiscal",
        'sellist',
        '100',
        '',
        'societe',
        0,
        0,
        '',
        array('options' => array("sat_c_reg_fiscal:CONCAT(c_RegimenFiscal, ' - ', label):c_RegimenFiscal" => NULL)),
        1,
        '',
        '1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    // Forma de pago
    $result1 = $extrafields->addExtraField(
        'formapago',
        "Forma de pago",
        'sellist',
        '100',
        '',
        'societe',
        0,
        0,
        '',
        array('options' => array("sat_c_formapago:CONCAT(c_FormaPago, ' - ', label):c_FormaPago" => NULL)),
        1,
        '',
        '1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    $result1 = $extrafields->addExtraField(
        'banco',
        "Banco",
        'sellist',
        '100',
        '',
        'societe',
        0,
        0,
        '',
        array('options' => array("sat_c_bancos:label:c_Banco" => NULL)),
        1,
        '',
        '1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
}

function createProductsExtrafields()
{
    global $conf, $db;

    if (!class_exists('ExtraFields')) {
        include_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
    }

    $extrafields = new ExtraFields($db);

    $result1 = $extrafields->addExtraField(
        'lineaproducto',
        "Linea producto",
        'sellist',
        '100',
        '',
        'product',
        0,
        0,
        '',
        array('options' => array("c_lineas_prod:libelle:rowid" => NULL)),
        1,
        '',
        '1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    $result1 = $extrafields->addExtraField(
        'claveunidad',
        "Clave Unidad",
        'sellist',
        '100',
        '',
        'product',
        0,
        0,
        '',
        array('options' => array("sat_c_unidades:CONCAT(c_ClaveUnidad, ' - ', label):c_ClaveUnidad" => NULL)),
        1,
        '',
        '1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    $result1 = $extrafields->addExtraField(
        'objetoimp',
        "Objeto de impuesto",
        'sellist',
        '100',
        '',
        'product',
        0,
        0,
        '',
        array('options' => array("sat_c_objetoimp:label:c_ObjetoImp" => NULL)),
        1,
        '',
        '1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    $result1 = $extrafields->addExtraField(
        'prodserv',
        "Clave Producto/Servicio",
        'varchar',
        '100',
        '255',
        'product',
        0,
        0,
        '',
        array('options' => array("" => NULL)),
        1,
        '',
        '1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
}

function createFactureExtrafields()
{
    global $db, $conf;
    if (!class_exists('ExtraFields')) {
        include_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
    }

    $extrafields = new ExtraFields($db);
    // USO CFDI
    $result1 = $extrafields->addExtraField(
        'usocfdi',
        "Uso de CFDI",
        'sellist',
        '104',
        '',
        'facture',
        0,
        1,
        'G03',
        array('options' => array("sat_c_uso_cfdi:CONCAT(c_UsoCFDI, ' ', label):c_UsoCFDI" => NULL)),
        0,
        '',
        '-1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    // Tipo de comprobante
    $result1 = $extrafields->addExtraField(
        'tipocomprobante',
        "Tipo de comprobante",
        'sellist',
        '100',
        '',
        'facture',
        0,
        0,
        '',
        array('options' => array("sat_c_tipo_comprobante:label:c_TipoDeComprobante" => NULL)),
        0,
        '',
        '5',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    // Metodo de pago
    $result1 = $extrafields->addExtraField(
        'metodopago',
        "Método de pago",
        'sellist',
        '101',
        '',
        'facture',
        0,
        1,
        '',
        array('options' => array("sat_c_metodopago:CONCAT(c_MetodoPago, ' ' , label):c_MetodoPago" => NULL)),
        0,
        '',
        '-1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    // Forma de pago
    $result1 = $extrafields->addExtraField(
        'formapago',
        "Forma de pago",
        'sellist',
        '102',
        '',
        'facture',
        0,
        1,
        '',
        array('options' => array("sat_c_formapago:CONCAT(c_FormaPago, ' ' , label):c_FormaPago" => NULL)),
        0,
        '',
        '-1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    // UUID
    $result1 = $extrafields->addExtraField(
        'uuid',
        "UUID",
        'varchar',
        '99',
        '255',
        'facture',
        1,
        0,
        '',
        array('options' => array("" => NULL)),
        0,
        '',
        '5',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    // ID Facturapi
    $result1 = $extrafields->addExtraField(
        'idfacturapi',
        "ID Facturapi",
        'varchar',
        '100',
        '255',
        'facture',
        1,
        0,
        '',
        array('options' => array("" => NULL)),
        0,
        '',
        '0',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '0',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    // Bool timbrada
    $result1 = $extrafields->addExtraField(
        'timbrada',
        'timbrada',
        'boolean',
        100,
        '',
        'facture',
        0,
        0,
        '0',
        array('options' => array("" => NULL)),
        0,
        '',
        '0',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled'
    );
    // Regimen fiscal RECEPTOR
    $result1 = $extrafields->addExtraField(
        'regimenfiscalreceptor',
        "Régimen fiscal receptor",
        'sellist',
        '106',
        '',
        'facture',
        0,
        1,
        '',
        array('options' => array("sat_c_reg_fiscal:CONCAT(c_RegimenFiscal, ' - ', label):c_RegimenFiscal" => NULL)),
        0,
        '',
        '-1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
        0,
        '1',
        array(
            'css' => '',
            'cssview' => '',
            'csslist' => ''
        ),
    );
    // Exportacion ?
    $result1 = $extrafields->addExtraField(
        'exportacion',
        'Exportación',
        'sellist',
        107,
        '',
        'facture',
        0,
        0,
        '0',
        array('options' => array("sat_c_exportacion:CONCAT(c_Exportacion, ' - ', label):c_Exportacion" => NULL)),
        0,
        '',
        '-1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled'
    );
    // Desglose descuento?
    $result1 = $extrafields->addExtraField(
        'desglosedescuento',
        'Desglose descuento',
        'boolean',
        108,
        '',
        'facture',
        0,
        0,
        '0',
        array('options' => array("" => NULL)),
        0,
        '',
        '-1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
    );
    // Retiene IVA?
    $result1 = $extrafields->addExtraField(
        'retieneiva',
        'Retiene IVA',
        'boolean',
        109,
        '',
        'facture',
        0,
        0,
        '0',
        array('options' => array("" => NULL)),
        0,
        '',
        '-1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
    );
    // Factura Global?
    $result1 = $extrafields->addExtraField(
        'facturaglobal',
        'Factura Global',
        'boolean',
        110,
        '',
        'facture',
        0,
        0,
        '0',
        array('options' => array("" => NULL)),
        0,
        '',
        '-1',
        '',
        '',
        '',
        '',
        '$conf->timbradomexico->enabled',
    );
}
