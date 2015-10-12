<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Haste\Generator\RowClass;
use Haste\Util\Format;
use Isotope\Isotope;
use Isotope\Model\ProductCollection\Order;


/**
 * Class OrderHistory
 *
 * Adds a switch to display all orders (not only the ones of the currently logged in user)
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class OrderHistoryPlus extends OrderHistory
{

	protected $strTemplate = 'mod_iso_orderhistoryplus';

	/**
	 * Display a wildcard in the back end
	 *
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE') {
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ORDER HISTORY PLUS ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		$this->iso_config_ids = deserialize($this->iso_config_ids);

		if (FE_USER_LOGGED_IN !== true || !is_array($this->iso_config_ids)
			|| !count(
				$this->iso_config_ids
			)
		) // Can't use empty() because its an object property (using __get)
		{
			return '';
		}

		return parent::generate();
	}


	/**
	 * Generate the module
	 *
	 * @return void
	 */
	protected function compile()
	{
		$arrOrders = array();
		$arrColumns = array();

		if (\Input::get('order_status'))
			$arrColumns[] = 'order_status > 0 AND order_status = ' . \Input::get('order_status');
		else
			$arrColumns[] = 'order_status > 0';

		if (\Input::get('config_id'))
			$arrColumns[] = 'config_id = ' . \Input::get('config_id');
		else
			$arrColumns[] = 'config_id IN (' . implode(',', array_map('intval', $this->iso_config_ids)) . ')';

		if (!$this->iso_show_all_orders)
			$arrColumns[] = 'member=' . \FrontendUser::getInstance()->id;

		// auto_item = member
		if(isset($_GET['items']))
		{
			$arrColumns[] = 'member=' . \Input::get('auto_item');
		}

		$objOrders = Order::findBy(
			$arrColumns,
			array(),
			array('order' => 'locked DESC')
		);

		// No orders found, just display an "empty" message
		if (null === $objOrders) {
			$this->Template = new \Isotope\Template('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['ERR']['emptyOrderHistory'];

			return;
		}

		/** @type Order $objOrder */
		foreach ($objOrders as $objOrder) {
			Isotope::setConfig($objOrder->getRelated('config_id'));

			$arrOrders[] = array
			(
				'collection' => $objOrder,
				'raw'        => $objOrder->row(),
				'date'       => Format::date($objOrder->locked),
				'time'       => Format::time($objOrder->locked),
				'datime'     => Format::datim($objOrder->locked),
				'grandTotal' => Isotope::formatPriceWithCurrency($objOrder->getTotal()),
				'status'     => $objOrder->getStatusLabel(),
				'link'       => ($this->jumpTo ? (\Haste\Util\Url::addQueryString(
					'uid=' . $objOrder->uniqid, $this->jumpTo
				)) : ''),
				'class'      => $objOrder->getStatusAlias(),
			);

			// add member name
			if (!($intId = $objOrder->row()['member']))
			{
				$arrOrders[count($arrOrders) - 1]['memberName'] = $GLOBALS['TL_LANG']['tl_module']['guestOrder'];
			}
			else
			{
				if (($objMember = \MemberModel::findByPk($intId)) !== null)
				{
					$arrOrders[count($arrOrders) - 1]['memberName'] = $objMember->firstname . ' ' . $objMember->lastname;
				}
				else
				{
					$arrOrders[count($arrOrders) - 1]['memberName'] = $GLOBALS['TL_LANG']['tl_module']['notExistingAnyMore'];
				}
			}
		}

		RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($arrOrders);

		$this->Template->orders = $arrOrders;
	}
}
