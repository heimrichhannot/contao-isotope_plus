<?php

namespace Isotope\Form;

use HeimrichHannot\FormHybrid\Form;
use HeimrichHannot\IsotopePlus\IsotopePlus;
use Isotope\CheckoutStep\BillingAddress;
use Isotope\CheckoutStep\ShippingAddress;
use Isotope\CheckoutStep\ShippingMethod;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\Config;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductCollectionSurcharge\Shipping;
use Isotope\Model\Shipping\Flat;
use Isotope\RequestCache\Sort;

class DirectCheckoutForm extends Form
{
	protected $strMethod = FORMHYBRID_METHOD_POST;
	protected $arrBillingAddressFields = array();
	protected $arrShippingAddressFields = array();
	protected $objCheckoutModule;
	protected $objProduct;

	public function __construct($objCheckoutModule, \ModuleModel $objModule = null, $instanceId = 0)
	{
		$this->objCheckoutModule = $objCheckoutModule;
		parent::__construct($objModule, $instanceId);
	}

	protected function compile() {
		if (!$this->objProduct)
		{
			$this->Template->error = $GLOBALS['TL_LANG']['MSC']['productNotFound'];
		}
	}

	public function modifyDC() {
		// get the product
		switch ($this->iso_direct_checkout_product_mode) {
			case 'product_type':
				$arrColumns = array(
					'type=?'
				);

				$arrValues = array(
					$this->iso_direct_checkout_product_type
				);

				if ($this->iso_listingSortField)
					$arrSorting = array(
						$this->iso_listingSortField => ($this->iso_listingSortDirection == 'DESC' ? Sort::descending() : Sort::ascending())
					);
				else
					$arrSorting = array();

				$objProducts = Product::findPublishedBy(
					$arrColumns,
					$arrValues,
					array(
						'sorting' => $arrSorting,
					)
				);

				if ($objProducts->count() > 0)
				{
					$this->objProduct = $objProducts->current();
				}
				break;
			default:
				$this->objProduct = Product::findByPk($this->iso_direct_checkout_product);
				break;
		}

		// add quantity
		$this->addEditableField('quantity', array(
			'label'     => &$GLOBALS['TL_LANG']['MSC']['quantity'],
			'inputType' => 'text',
			'eval'      => array('mandatory'=>true)
		));

		// add subscription field
		if (in_array('isotope_subscriptions', \ModuleLoader::getActive()) && $this->iso_addSubscriptionCheckbox)
		{
			$this->addEditableField('subscribeToProduct', array(
				'label' => ' ',
				'inputType' => 'checkbox',
				'options'   => array(
					'1' => $GLOBALS['TL_LANG']['MSC']['subscribeToProduct']
				)
			));
		}

		// add address fields
		\Controller::loadDataContainer('tl_iso_address');
		\System::loadLanguageFile('tl_iso_address');

		$arrAddressFields = deserialize(Config::findByPk($this->iso_config_id)->address_fields, true);

		// add billing address fields
		foreach ($arrAddressFields as $strName => $arrAddressField)
		{
			$arrData = $GLOBALS['TL_DCA']['tl_iso_address']['fields'][$strName];

			if (!is_array($arrData) || $arrAddressField['billing'] == 'disabled')
				continue;

			$arrData['eval']['mandatory'] = $arrAddressField['billing'] == 'mandatory';

			$this->arrBillingAddressFields[] = $strName;
			$this->addEditableField($strName, $arrData);
		}

		if($this->iso_use_notes){
			$this->addEditableField('notes', array(
					'label'                     => &$GLOBALS['TL_LANG']['MSC']['iso_note'],
					'exclude'                   => true,
					'inputType'                 => 'textarea',
					'eval'						=> array('tl_class' => 'clr w50'),
					'sql'       				=> "text NULL"
			));
		}


		$this->addEditableField('shippingaddress', array(
			'label'     => array($GLOBALS['TL_LANG']['MSC']['differentShippingAddress'], $GLOBALS['TL_LANG']['MSC']['differentShippingAddress']),
			'inputType' => 'checkbox',
			'eval'      => array('submitOnChange' => true)
		));

		// add shipping address fields
		$arrShippingAddressFields = array();
		foreach ($arrAddressFields as $strName => $arrAddressField)
		{
			$arrData = $GLOBALS['TL_DCA']['tl_iso_address']['fields'][$strName];

			if (!is_array($arrData) || $arrAddressField['shipping'] == 'disabled')
				continue;

			$arrData['eval']['mandatory'] = $arrAddressField['shipping'] == 'mandatory';

			$this->addEditableField('shippingaddress_' . $strName, $arrData);

			$arrShippingAddressFields[] = 'shippingaddress_' . $strName;
		}

		$this->dca['palettes']['__selector__'][] = 'shippingaddress';
		$this->dca['subpalettes']['shippingaddress'] = implode(',', $arrShippingAddressFields);
		$this->arrShippingAddressFields = $arrShippingAddressFields;


	}

	// avoid standard formhybrid save and callback routines, just process the form
	protected function save($varValue = '') {}
	protected function runCallbacks() {}

	protected function processForm() {
		// get a product collection (aka cart)
		global $objPage;

		$objCart = new Cart();
		$objCheckoutModule = $this->objCheckoutModule;

		// Can't call the individual rows here, it would trigger markModified and a save()
		$objCart->setRow(
			array_merge(
				$objCart->row(), array(
					'tstamp'    => time(),
					'member'    => 0,
					'uniqid'    => null,
					'config_id' => $this->iso_config_id,
					'store_id'  => (int) \PageModel::findByPk($objPage->rootId)->iso_store_id,
				)
			)
		);

		$objSubmission = $this->getSubmission();

		if (!$objCart->addProduct($this->objProduct, $objSubmission->quantity))
			return;

		$objCart->save();

		$objOrder = $objCart->getDraftOrder();

		// temporarily override the cart for generating the reviews...
		$objCartTmp = Isotope::getCart();
		Isotope::setCart($objCart);

		// create steps
		$arrSteps = array();
		$arrCheckoutInfo = array();

		// billing address
		$objBillingAddress = new Address();

		foreach ($this->arrBillingAddressFields as $strName)
		{
			$objBillingAddress->{$strName} = $objSubmission->{$strName};
		}

		$objBillingAddress->save();
		$objOrder->setBillingAddress($objBillingAddress);
		$objBillingAddressStep = new BillingAddress($objCheckoutModule);
		$arrSteps[] = $objBillingAddressStep;
		$arrCheckoutInfo['billing_address'] = $objBillingAddressStep->review()['billing_address'];

		// shipping address
		$objShippingAddress = new Address();

		// standard isotope handling for distinguishing between the address types:
		// -> if only a billing address is available, it's also the shipping address
		foreach (($objSubmission->shippingaddress ? $this->arrShippingAddressFields : $this->arrBillingAddressFields)
			as $strName)
		{
			$objShippingAddress->{str_replace('shippingaddress_', '', $strName)} =
				$objSubmission->{$objSubmission->shippingaddress ? $strName : str_replace('shippingaddress_', 'billingaddress_', $strName)};
		}

		$objShippingAddress->save();
		$objOrder->setShippingAddress($objShippingAddress);
		$objShippingAddressStep = new ShippingAddress($objCheckoutModule);
		$arrSteps[] = $objShippingAddressStep;
		$arrCheckoutInfo['shipping_address'] = $objShippingAddressStep->review()['shipping_address'];

		// add shipping method
		$objIsotopeShipping = Flat::findByPk($this->iso_shipping_modules);
		$objOrder->setShippingMethod($objIsotopeShipping);
		$objShippingMethodStep = new ShippingMethod($objCheckoutModule);
		$arrSteps[] = $objShippingMethodStep;
		$arrCheckoutInfo['shipping_method'] = $objShippingMethodStep->review()['shipping_method'];

		// add all the checkout info to the order
		$objOrder->checkout_info = $arrCheckoutInfo;

		$objOrder->notes = $objSubmission->notes;

		//... restore the former cart again
		Isotope::setCart($objCartTmp);

		$objOrder->nc_notification      = $this->nc_notification;
		$objOrder->email_data           = $this->getNotificationTokensFromSteps($arrSteps, $objOrder);

		// !HOOK: pre-process checkout
		if (isset($GLOBALS['ISO_HOOKS']['preCheckout']) && is_array($GLOBALS['ISO_HOOKS']['preCheckout'])) {
			foreach ($GLOBALS['ISO_HOOKS']['preCheckout'] as $callback) {
				$objCallback = \System::importStatic($callback[0]);

				if ($objCallback->$callback[1]($objOrder, $objCheckoutModule) === false) {
					\System::log('Callback ' . $callback[0] . '::' . $callback[1] . '() cancelled checkout for Order ID ' . $this->id, __METHOD__, TL_ERROR);

					$objCheckoutModule->redirectToStep('failed');
				}
			}
		}

		$objOrder->lock();
		$objOrder->checkout();
		$objOrder->complete();

		parent::processForm();
	}

	// copy from Checkout.php
	protected function getNotificationTokensFromSteps(array $arrSteps, IsotopeProductCollection $objOrder)
	{
		$arrTokens = array();

		// Run trough all steps to collect checkout information
		foreach ($arrSteps as $objModule) {

//			foreach ($arrModules as $objModule) {
				$arrTokens = array_merge($arrTokens, $objModule->getNotificationTokens($objOrder));
//			}
		}

		return $arrTokens;
	}

}

