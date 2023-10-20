<?php
require_once DOL_DOCUMENT_ROOT.'/custom/timbradomexico/vendor/autoload.php';

use Facturapi\Facturapi;
class FacturaHandle
{
	private $facturapi;

	public function __construct($token)
	{
		$this->facturapi = new Facturapi($token);
	}

	public function formatInvoice($object){
		$object->fetch_thirdparty();

		$invoice = new stdClass();
		$invoice->customer = new stdClass();
		$invoice->customer->address = new stdClass();
		$invoice->items = array();

		$invoice->customer->legal_name = $object->thirdparty->name;
		$invoice->customer->tax_id = $object->thirdparty->idprof1;
		$invoice->customer->tax_system = strval(intval(explode('-',$object->thirdparty->forme_juridique)[0]));

		$invoice->customer->address->zip = $object->thirdparty->zip;

		foreach($object->lines as $line){
			$line->fetch_product();
			$line_product = $line->product;
			$line_product->fetch_optionals();
			$item_line = new stdClass();
			$item_line->quantity = $line->qty;
			$item_line->product = new stdClass();

			$item_line->product->description = $line->libelle;
			$item_line->product->product_key = '60131324';
			$item_line->product->price = $line->subprice;
			$item_line->product->tax_included = false;

			$invoice->items[] = $item_line;
		}

		$invoice->payment_form = '28';
		$invoice->payment_method = 'PUE';
		$invoice->currency = 'MXN';


		return $invoice;
	}

	public function createInvoice($object){
		$invoice = $this->formatInvoice($object);
		// echo '<pre>';var_dump($object);echo '</pre>';exit;
		$res = $this->facturapi->Invoices->create($invoice);

		return $res;
	}
}
