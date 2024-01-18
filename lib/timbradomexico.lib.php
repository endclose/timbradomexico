<?php

function getRegimenFiscal($db)
{
	$sql = "SELECT c_RegimenFiscal AS id , label FROM " . MAIN_DB_PREFIX . "sat_c_reg_fiscal WHERE status = 1";
	$res = $db->query($sql);
	$regimen = array();

	if (!$res) {
		dol_print_error($db);
		exit;
	}
	
	while ($row = $db->fetch_object($res)) {
		$regimen[$row->id] = $row->id . '-'.$row->label;
	}
	return $regimen;
}
