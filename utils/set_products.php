<?php
require '../../../main.inc.php';

global $db, $user;

require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

if (!$user->admin) {
    accessforbidden();
}
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
        echo $product->ref . ' actualizado<br>';
    }
}

$db->close();
