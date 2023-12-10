<?php

// if (!defined('NOREQUIRESOC')) {
// 	define('NOREQUIRESOC', '1');
// }
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled because need to do translations
if (!defined('NOTOKENRENEWAL')) {
	define('NOTOKENRENEWAL', 1);
}
if (!defined('NOLOGIN')) {
	define('NOLOGIN', 1); // File must be accessed by logon page so without login
}
//if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);  // We need top menu content
if (!defined('NOREQUIREHTML')) {
	define('NOREQUIREHTML', 1);
}
if (!defined('NOREQUIREAJAX')) {
	define('NOREQUIREAJAX', '1');
}

require '../../../main.inc.php';
global $db, $user;

require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';

// if (!$user->admin) {
//     accessforbidden();
// }

$sql = "SELECT s.rowid AS 'rowid', rf.c_RegimenFiscal AS 'regimenfiscal', fp.c_FormaPago AS 'formapago', b.c_Banco AS 'banco' FROM " . MAIN_DB_PREFIX . "societe AS s";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "sat_c_reg_fiscal AS rf ON s.fk_regfiscal = rf.rowid";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "sat_c_formapago AS fp ON s.fk_formapago = fp.rowid";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "sat_c_bancos AS b ON s.fk_banco = b.rowid";

$resql = $db->query($sql);


//Recorre el resultado de la consulta y lo ingresa en societe
while ($obj = $db->fetch_object($resql)) {
    $societe = new Societe($db);
    $res = $societe->fetch(intval($obj->rowid));
    $societe->fetch_optionals();
    
    if ($res) {
        $societe->array_options['options_regimenfiscal'] = $obj->regimenfiscal;
        $societe->array_options['options_formapago'] = $obj->formapago;
        $societe->array_options['options_banco'] = $obj->banco;
        $societe->update(0, $user);
        echo $societe->name . " actualizado\n";
    }
}

$db->close();