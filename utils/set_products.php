<?php
ini_set('memory_limit', '1024M');
if (!defined('NOREQUIRESOC')) {
	define('NOREQUIRESOC', '1');
}
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

require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

// if (!$user->admin) {
//     accessforbidden();
// }
$sql = "SELECT p.rowid, lp.rowid AS fk_lineaprod, u.c_ClaveUnidad AS claveunidad, oi.c_ObjetoImp AS objetoimp, p.prodserv  FROM " . MAIN_DB_PREFIX . "product AS p";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_lineas_prod AS  lp ON p.fk_linea = lp.rowid";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "sat_c_unidades AS u ON p.claveunidad = u.c_ClaveUnidad";
$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "sat_c_objetoimp AS oi ON p.objetoimp = oi.c_ObjetoImp";

$resql = $db->query($sql);

//Recorre el resultado de la consulta y lo ingresa en product
while ($obj = $db->fetch_object($resql)) {
    $product = new Product($db);
    $res = $product->fetch(intval($obj->rowid));

    $product->fetch_optionals();

    if ($res) {
        $product->array_options['options_lineaproducto'] = $obj->fk_lineaprod;
        $product->array_options['options_claveunidad'] = $obj->claveunidad;
        $product->array_options['options_objetoimp'] = $obj->objetoimp;
        $product->array_options['options_prodserv'] = $obj->prodserv;
        $product->update($product->id, $user);
        echo $product->id. ' ' .$product->ref . " actualizado\n";
    }
}
$db->free($resql);
$db->close();
