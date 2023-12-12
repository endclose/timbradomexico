<?php
require_once DOL_DOCUMENT_ROOT . '/custom/timbradomexico/vendor/autoload.php';

use Facturapi\Facturapi;

class FacturaHandle
{
	private $facturapi;

	public function __construct($token)
	{
		$this->facturapi = new Facturapi($token);
	}

	public function formatInvoice($object)
	{
		$object->fetch_thirdparty();
		$object->thirdparty->fetch_optionals();

		$type = $object->type;

		$invoice = new stdClass();
		$invoice->customer = new stdClass();
		$invoice->customer->address = new stdClass();
		$invoice->items = array();

		$invoice->customer->legal_name = $object->thirdparty->name;
		$invoice->customer->tax_id = $object->thirdparty->idprof1;
		$invoice->customer->tax_system = $object->array_options['options_regimenfiscalreceptor'];

		$invoice->customer->address->zip = $object->thirdparty->zip;

		foreach ($object->lines as $line) {
			$line->fetch_product();
			// we saved the product on a variable because we need to fetch the optionals fields
			$product = $line->product;
			if (is_null($product)) {
				setEventMessage('No se pueden usar productos/servicios libres en facturas.<br> Elimine o sutituya la partida ' . $line->rang . '[' . $line->desc . ']', 'errors');
				return false;
			}
			$product->fetch_optionals();
			$product->fetch_optionals();

			$item_line = new stdClass();

			$item_line->quantity = $line->qty;
			$item_line->discount = 0;
			if ($object->array_options['options_desglosedescuento']) {
				$item_line->discount = $line->subprice * $line->remise_percent / 100;
			}
			$item_line->product = new stdClass();

			$item_line->product->sku = $line->ref;
			$item_line->product->description = $line->libelle;
			$item_line->product->product_key = $product->array_options['options_prodserv'];
			$item_line->product->price = $type == $object::TYPE_CREDIT_NOTE ?  $line->subprice * -1 : ($object->array_options['options_desglosedescuento'] ? $line->subprice : $line->subprice - $item_line->discount);
			$item_line->product->taxability = $product->array_options['options_objetoimp'];
			$item_line->product->unit_key = $product->array_options['options_claveunidad'];
			$item_line->product->tax_included = false;

			$invoice->items[] = $item_line;
		}

		$invoice->type = $object->array_options['options_tipocomprobante'];
		$invoice->export = $object->array_options['options_exportacion'];
		$invoice->payment_form = $object->array_options['options_formapago'];
		$invoice->payment_method = $object->array_options['options_metodopago'];
		$invoice->currency = 'MXN';
		$invoice->series = 'A';
		$invoice->folio_number = $object->ref;

		// echo '<pre>';var_dump($invoice);echo '</pre>';exit;
		return $invoice;
	}

	public function createInvoice(&$object, $returned_format = 'nozip')
	{
		global $user;
		$object->fetch_optionals();

		$invoice = $this->formatInvoice($object);
		if ($invoice === false) {
			return false;
		}
		$res = $this->facturapi->Invoices->create($invoice);
		if ($res->ok === false) {
			setEventMessage($res->message, 'errors');
			return false;
		}

		$object->array_options['options_idfacturapi'] = $res->id;
		$object->array_options['options_uuid'] = $res->uuid;
		$object->array_options['options_timbrada'] = 1;
		$object->update($user);

		// Save PDF
		$this->recoverFiles($object, $returned_format);
		return $res;
	}

	public function cancelInvoice($id, $params)
	{
		$res = $this->facturapi->Invoices->cancel($id, $params);
		return $res;
	}

	public function getInvoicePDF($id)
	{
		$res = $this->facturapi->Invoices->download_pdf($id);
		return $res;
	}

	public function getInvoiceXML($id)
	{
		$res = $this->facturapi->Invoices->download_xml($id);
		return $res;
	}

	public function getInvoiceZIP($id)
	{
		$res = $this->facturapi->Invoices->download_zip($id);
		return $res;
	}

	public function saveFile($object, $file, $extension)
	{
		global $conf;

		$object_type = $object->element;

		$file_name = dol_sanitizeFileName($object->ref);
		$dir = $dir = $conf->$object_type->multidir_output[$conf->entity] . '/' . $file_name;
		if (!is_dir($dir)) {
			dol_mkdir($dir);
		}

		$final_dir = $dir . '/' . $file_name . $extension;

		return file_put_contents($final_dir, $file);
	}

	public function recoverFiles($object, $returned_format = 'nozip')
	{
		switch ($returned_format) {
			case 'pdf':
				$pdf = $this->getInvoicePDF($object->array_options['options_idfacturapi']);
				$this->saveFile($object, $pdf, '.pdf');
				break;
			case 'xml':
				$xml = $this->getInvoiceXML($object->array_options['options_idfacturapi']);
				$this->saveFile($object, $xml, '.xml');
				break;
			case 'zip':
				$zip = $this->getInvoiceZIP($object->array_options['options_idfacturapi']);
				$this->saveFile($object, $zip, '.zip');
				break;
			case 'nozip':
				$xml = $this->getInvoiceXML($object->array_options['options_idfacturapi']);
				$this->saveFile($object, $xml, '.xml');
				$pdf = $this->getInvoicePDF($object->array_options['options_idfacturapi']);
				$this->saveFile($object, $pdf, '.pdf');
				break;
		}
	}
}
