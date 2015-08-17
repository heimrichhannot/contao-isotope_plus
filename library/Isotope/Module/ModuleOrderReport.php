<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package isotope_plus
 * @author Oliver Janke <o.janke@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace Isotope\Module;

use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;

class ModuleOrderReport extends Module
{
	protected $strTemplate = 'mod_orderReport';

	protected $formId = 'orderReport';

	protected $orderId = null;

	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate = new \BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['iso_orderreport'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Set the item from the auto_item parameter
		if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])) {
			$this->Input->setGet('details', $this->Input->get('auto_item'));
		}

		if ($this->orderId = $this->Input->get('details')) {
			$this->strTemplate = 'mod_orderReport_details';
		}

		return parent::generate();
	}

	protected function compile()
	{
		$this->loadLanguageFile('tl_reports');

		if (!is_null($this->orderId)) {
			$this->compileDetails($this->orderId);
		} else {
			$this->compileList();
		}
	}

	protected function compileDetails($orderId)
	{
		$objOrderStmt = $this->Database->prepare("SELECT pc.*,
														 a.gender AS billing_gender,
														 a.salutation AS billing_salutation,
														 a.firstname AS billing_firstname,
														 a.lastname AS billing_lastname,
														 a.company AS billing_company,
														 a.street_1 AS billing_street1,
														 a.street_2 AS billing_street2,
														 a.street_3 AS billing_street3,
														 a.postal AS billing_postal,
														 a.city AS billing_city,
														 a.country AS billing_country,
														 a.phone AS billing_phone,
														 a.email AS billing_email
												  FROM tl_iso_product_collection pc
												  	INNER JOIN tl_iso_address a ON a.id = pc.billing_address_id
												  WHERE pc.id=?");
		$objOrder = $objOrderStmt->execute($orderId);

		$objOrderItemsStmt = $this->Database->prepare("SELECT name, quantity
												  	   FROM tl_iso_product_collection_item
												  	   WHERE pid=?");
		$objOrderItems = $objOrderItemsStmt->execute($orderId);

		$order = new Order();
		$order->id = $objOrder->id;
		$order->date = date("d.m.Y", $objOrder->tstamp);
		$order->billing = array(
			'gender'      => $objOrder->billing_gender,
			'salutation'  => $objOrder->billing_salutation,
			'firstname'   => $objOrder->billing_firstname,
			'lastname'    => $objOrder->billing_lastname,
			'company'     => $objOrder->billing_company,
			'street1'     => $objOrder->billing_street1,
			'street2'     => $objOrder->billing_street2,
			'street3'     => $objOrder->billing_street3,
			'postal'      => $objOrder->billing_postal,
			'city'        => $objOrder->billing_city,
			'country'     => $objOrder->billing_country,
			'phone'       => $objOrder->billing_phone,
			'email'       => $objOrder->billing_email
		);
		$arrItems = array();
		while($objOrderItems->next())
		{
			$item = new \Isotope\Model\ProductCollectionItem();
			$item->name = $objOrderItems->name;
			$item->quantity = $objOrderItems->quantity;
			$arrItems[] = $item;
		}
		$order->items = $arrItems;
		$this->Template->order = $order;
	}

	protected function compileList()
	{
		global $objPage;

		$arrOrders = array();
		$offset = 0;
		$limit = null;

		// Get the total number of items
		$objTotal = $this->Database->prepare("SELECT COUNT(*) AS total FROM tl_iso_product_collection WHERE type='order' AND order_status=1 ORDER BY id DESC")->execute();
		$total = $objTotal->total;

		// Split the results
		if ($this->perPage > 0 && !isset($limit)) {
			// Adjust the overall limit
			if (isset($limit)) {
				$total = min($limit, $total);
			}

			// Get the current page
			$page = $this->Input->get('page') ? $this->Input->get('page') : 1;

			// Do not index or cache the page if the page number is outside the range
			if ($page < 1 || $page > ceil($total / $this->perPage)) {
				global $objPage;
				$objPage->noSearch = 1;
				$objPage->cache = 0;

				// Send a 404 header
				header('HTTP/1.1 404 Not Found');

				return;
			}

			// Set limit and offset
			$limit = $this->perPage;
			$offset = (max($page, 1) - 1) * $this->perPage;

			// Overall limit
			if ($offset + $limit > $total) {
				$limit = $total - $offset;
			}

			// Add the pagination menu
			$objPagination = new Pagination($total, $this->perPage);
			$this->Template->pagination = $objPagination->generate("\n  ");
		}

		$objOrderStmt = $this->Database->prepare("SELECT pc.*, m.firstname AS firstname, m.lastname AS lastname FROM tl_iso_product_collection pc INNER JOIN tl_member m ON m.id = pc.member WHERE type='order' AND order_status=1 ORDER BY id DESC");

		// Limit the result
		if(isset($limit)) {
			$objOrderStmt->limit($limit, $offset + $skipFirst);
		} elseif ($skipFirst > 0) {
			$objOrderStmt->limit(max($total, 1), $skipFirst);
		}

		$objOrders = $objOrderStmt->execute();
		$jumpTo = $this->generateFrontendUrl($objPage->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ?  '/%s' : '/details/%s'), $objPage->language);

		while($objOrders->next())
		{
			$order = new Order();
			$order->id = $objOrders->id;
			$order->date = date("d.m.Y", $objOrders->tstamp);
			$order->customer = $objOrders->firstname . " " . $objOrders->lastname;
			$order->jumpTo = sprintf($jumpTo, $objOrders->id);
			$arrOrders[] = $order;
		}

		$this->Template->items = $arrOrders;
		$this->Template->id = 'orderReport';
		$this->Template->formId = $this->formId;
		$this->Template->href = $this->getIndexFreeRequest();
	}
}
