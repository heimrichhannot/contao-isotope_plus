<?php

namespace Isotope\Form;

use HeimrichHannot\FieldPalette\FieldPaletteModel;
use HeimrichHannot\FormHybrid\Form;
use HeimrichHannot\StatusMessages\StatusMessage;
use Isotope\CheckoutStep\BillingAddress;
use Isotope\CheckoutStep\ShippingAddress;
use Isotope\CheckoutStep\ShippingMethod;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\Config;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\Shipping\Flat;
use Isotope\RequestCache\Sort;

class DirectCheckoutForm extends Form
{
    protected $strMethod                = FORMHYBRID_METHOD_POST;
    protected $arrBillingAddressFields  = array();
    protected $arrShippingAddressFields = array();
    protected $arrProducts              = array();
    protected $objCheckoutModule;
    protected $productCount             = 0;
    protected $typeCount                = 0;

    protected $noEntity = true;

    public function __construct($objModule = null, $instanceId = 0)
    {
        $this->objCheckoutModule = $objModule;
        parent::__construct($objModule, $instanceId);
    }

    protected function compile()
    {
        if (empty($this->arrProducts))
        {
            $this->Template->error = $GLOBALS['TL_LANG']['MSC']['productNotFound'];
        }
    }

    public function modifyDC(&$arrDca = null)
    {
        // get the product
        switch ($this->iso_direct_checkout_product_mode)
        {
            case 'product_type':
                if (($objTypes = FieldPaletteModel::findByPidAndTableAndField($this->objModule->id, 'tl_module', 'iso_direct_checkout_product_types')) !== null)
                {
                    while ($objTypes->next())
                    {


                        $arrColumns = array(
                            'type=?',
                        );

                        $arrValues = array(
                            $objTypes->iso_direct_checkout_product_type,
                        );

                        if ($this->iso_listingSortField)
                        {
                            $arrSorting = array(
                                $this->iso_listingSortField => ($this->iso_listingSortDirection == 'DESC' ? Sort::descending() : Sort::ascending()),
                            );
                        }
                        else
                        {
                            $arrSorting = array();
                        }

                        $objProducts = Product::findPublishedBy(
                            $arrColumns,
                            $arrValues,
                            array(
                                'sorting' => $arrSorting,
                            )
                        );

                        if ($objProducts->count() > 0)
                        {
                            $objProduct = $objProducts->current();

                            $this->arrProducts[] = array(
                                'product'     => $objProduct,
                                'useQuantity' => $objTypes->iso_use_quantity,
                            );

                            $this->addProductFields($objProduct, $objTypes->iso_use_quantity, $objTypes->iso_addSubscriptionCheckbox, $arrDca);

                        }
                    }
                }
                break;
            default:
                if (($objProducts = FieldPaletteModel::findByPidAndTableAndField($this->objModule->id, 'tl_module', 'iso_direct_checkout_products')) !== null)
                {
                    while ($objProducts->next())
                    {
                        $objProduct = Product::findByPk($objProducts->iso_direct_checkout_product);

                        $this->arrProducts[] = array(
                            'product'     => $objProduct,
                            'useQuantity' => $objProducts->iso_use_quantity,
                        );
                        $this->addProductFields($objProduct, $objProducts->iso_use_quantity, $objProducts->iso_addSubscriptionCheckbox, $arrDca);
                        
                        
                    }
                }
                break;
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
            {
                continue;
            }

            $arrData['eval']['mandatory'] = $arrAddressField['billing'] == 'mandatory';

            $this->arrBillingAddressFields[] = $strName;
            $this->addEditableField($strName, $arrData);
        }
	
		$this->addFieldsToDefaultPalette($this->arrBillingAddressFields);
        
        if ($this->iso_use_notes)
        {
            $this->addEditableField(
                'notes',
                array(
                    'label'     => &$GLOBALS['TL_LANG']['MSC']['iso_note'],
                    'exclude'   => true,
                    'inputType' => 'textarea',
                    'eval'      => array('tl_class' => 'clr w50'),
                    'sql'       => "text NULL",
                )
            );
        }


        $this->addEditableField(
            'shippingaddress',
            array(
                'label'     => array($GLOBALS['TL_LANG']['MSC']['differentShippingAddress'], $GLOBALS['TL_LANG']['MSC']['differentShippingAddress']),
                'inputType' => 'checkbox',
                'eval'      => array('submitOnChange' => true),
            )
        );

        // add shipping address fields
        $arrShippingAddressFields = array();
        foreach ($arrAddressFields as $strName => $arrAddressField)
        {
            $arrData = $GLOBALS['TL_DCA']['tl_iso_address']['fields'][$strName];

            if (!is_array($arrData) || $arrAddressField['shipping'] == 'disabled')
            {
                continue;
            }

            $arrData['eval']['mandatory'] = $arrAddressField['shipping'] == 'mandatory';

            $this->addEditableField('shippingaddress_' . $strName, $arrData);

            $arrShippingAddressFields[] = 'shippingaddress_' . $strName;
        }

        $this->dca['palettes']['__selector__'][]     = 'shippingaddress';
        $this->dca['subpalettes']['shippingaddress'] = implode(',', $arrShippingAddressFields);
        $this->arrShippingAddressFields              = $arrShippingAddressFields;
	
		$this->addFieldsToDefaultPalette($this->arrShippingAddressFields);
	
    }
	
	
	protected function addFieldsToDefaultPalette($arrFields)
	{
		$strFields = '';
		
		if(!is_array($arrFields))
		{
			if($arrFields && !preg_match("~\b " . $arrFields . "\b~",$this->dca['palettes']['default']))
			{
				$strFields .= ',' . $arrFields;
			}
		}
		else {
			foreach($arrFields as $field)
			{
				if(!preg_match("~\b " . $field . "\b~",$this->dca['palettes']['default']))
				{
					$strFields .= ',' . $field;
				}
			}
		}
		
		$this->dca['palettes']['default'] .= $strFields . ';';
		
	}
	
    protected function addProductFields($objProduct, $blnAddQuantity, $blnAddSubscriptionCheckbox, &$arrDca)
    {
        $blnSubPalette = $blnAddQuantity || (in_array('isotope_subscriptions', \ModuleLoader::getActive()) && $blnAddSubscriptionCheckbox);

        $this->setProductCount(count(deserialize($this->iso_direct_checkout_products)));
        $this->setTypeCount(count(deserialize($this->iso_direct_checkout_product_types)));

        if($this->getProductCount() > 1 || $this->getTypeCount() > 1)
        {
            // add checkbox
            $this->addEditableField(
                'product_' . $objProduct->id,
                array(
                    'label'     => $objProduct->name,
                    'inputType' => 'checkbox',
                    'eval'      => array(
                        'submitOnChange' => $blnSubPalette,
                    ),
                )
            );
	
			$this->addFieldsToDefaultPalette('product_' . $objProduct->id);
			

            if ($blnSubPalette)
            {
                $arrDca['palettes']['__selector__'][] = 'product_' . $objProduct->id;
            }

            if ($blnAddQuantity)
            {
                $arrDca['subpalettes']['product_' . $objProduct->id] = 'quantity_' . $objProduct->id;
            }

            if (in_array('isotope_subscriptions', \ModuleLoader::getActive()) && $blnAddSubscriptionCheckbox)
            {
                $arrDca['subpalettes']['product_' . $objProduct->id] .= ',subscribeToProduct_' . $objProduct->id;
            }
        }

        if ($blnAddQuantity)
        {
            $this->addEditableField(
                'quantity_' . $objProduct->id,
                array(
                    'label'     => &$GLOBALS['TL_LANG']['MSC']['quantity'],
                    'inputType' => 'text',
                    'eval'      => array('mandatory' => true),
                )
            );
            
            $this->addFieldsToDefaultPalette('quantity_' . $objProduct->id);
        }


        if (in_array('isotope_subscriptions', \ModuleLoader::getActive()) && $blnAddSubscriptionCheckbox)
        {
            $this->addEditableField(
                'subscribeToProduct_' . $objProduct->id,
                array(
                    'label'     => ' ',
                    'inputType' => 'checkbox',
                    'options'   => array(
                        '1' => $GLOBALS['TL_LANG']['MSC']['subscribeToProduct'],
                    ),
                )
            );
	
			$this->addFieldsToDefaultPalette('subscribeToProduct_' . $objProduct->id);
        }

    }

    // avoid standard formhybrid save and callback routines, just process the form
    protected function save($varValue = '') { }

    protected function runCallbacks() { }

    protected function processForm()
    {
        // get a product collection (aka cart)
        global $objPage;

        $objCart = new Cart();

        // Can't call the individual rows here, it would trigger markModified and a save()
        $objCart->setRow(
            array_merge(
                $objCart->row(),
                array(
                    'tstamp'    => time(),
                    'member'    => 0,
                    'uniqid'    => null,
                    'config_id' => $this->iso_config_id,
                    'store_id'  => (int) \PageModel::findByPk($objPage->rootId)->iso_store_id,
                )
            )
        );

        $objSubmission = $this->getSubmission(false);


        // add products to cart
        foreach ($this->arrProducts as $arrProduct)
        {
            $strProduct  = 'product_' . $arrProduct['product']->id;
            $strQuantity = 'quantity_' . $arrProduct['product']->id;

            if (( $this->getProductCount() > 1 || $this->getTypeCount() > 1 ) && !$objSubmission->{$strProduct})
            {
                continue;
            }


            if (!$objCart->addProduct($arrProduct['product'], $arrProduct['useQuantity'] ? $objSubmission->{$strQuantity} : 1))
            {
                $this->transformIsotopeErrorMessages();

                return;
            }
        }

        $objCart->save();

        $objOrder = $objCart->getDraftOrder();

        // temporarily override the cart for generating the reviews...
        $objCartTmp = Isotope::getCart();
        Isotope::setCart($objCart);

        // create steps
        $arrSteps        = array();
        $arrCheckoutInfo = array();

        // billing address
        $objBillingAddress = new Address();

        foreach ($this->arrBillingAddressFields as $strName)
        {
            $objBillingAddress->{$strName} = $objSubmission->{$strName};
        }

        $objBillingAddress->save();
        $objOrder->setBillingAddress($objBillingAddress);
        $objBillingAddressStep              = new BillingAddress($this->objCheckoutModule);
        $arrSteps[]                         = $objBillingAddressStep;
        $arrCheckoutInfo['billing_address'] = $objBillingAddressStep->review()['billing_address'];



        // shipping address
        $objShippingAddress = new Address();

        // standard isotope handling for distinguishing between the address types:
        // -> if only a billing address is available, it's also the shipping address
        foreach (
        ($objSubmission->shippingaddress ? $this->arrShippingAddressFields : $this->arrBillingAddressFields) as $strName
        )
        {
            $objShippingAddress->{str_replace('shippingaddress_', '', $strName)} =
                $objSubmission->{$objSubmission->shippingaddress ? $strName : str_replace('shippingaddress_', 'billingaddress_', $strName)};
        }

        $objShippingAddress->save();

        $objOrder->setShippingAddress($objShippingAddress);
        $objShippingAddressStep              = new ShippingAddress($this->objCheckoutModule);
        $arrSteps[]                          = $objShippingAddressStep;
        $arrCheckoutInfo['shipping_address'] = $objShippingAddressStep->review()['shipping_address'];

        // add shipping method
        $objIsotopeShipping = Flat::findByPk($this->iso_shipping_modules);
        $objOrder->setShippingMethod($objIsotopeShipping);
        $objShippingMethodStep              = new ShippingMethod($this->objCheckoutModule);
        $arrSteps[]                         = $objShippingMethodStep;

        $arrCheckoutInfo['shipping_method'] = $objShippingMethodStep->review()['shipping_method'];






        // add all the checkout info to the order
        $objOrder->checkout_info = $arrCheckoutInfo;

        $objOrder->notes = $objSubmission->notes;

        //... restore the former cart again
        Isotope::setCart($objCartTmp);

        $objOrder->nc_notification = $this->nc_notification;
        $objOrder->email_data      = $this->getNotificationTokensFromSteps($arrSteps, $objOrder);




        // !HOOK: pre-process checkout
        if (isset($GLOBALS['ISO_HOOKS']['preCheckout']) && is_array($GLOBALS['ISO_HOOKS']['preCheckout']))
        {
            foreach ($GLOBALS['ISO_HOOKS']['preCheckout'] as $callback)
            {
                $this->import($callback[0]);
	
				if($this->{$callback[0]}->{$callback[1]}($objOrder, $this->objCheckoutModule) === false)
				{
					\System::log('Callback ' . $callback[0] . '::' . $callback[1] . '() cancelled checkout for Order ID ' . $this->id, __METHOD__, TL_ERROR);
					
					$this->objCheckoutModule->redirectToStep('failed');
				}
            }
        }

        $objOrder->lock();
        $objOrder->checkout();
        $objOrder->complete();

        if (is_array($this->dca['config']['onsubmit_callback']))
        {
            foreach ($this->dca['config']['onsubmit_callback'] as $key => $callback)
            {
                if ($callback[0] == 'Isotope\Backend\ProductCollection\Callback' && $callback[1] == 'executeSaveHook')
                {
                    unset($this->dca['config']['onsubmit_callback'][$key]);
                    break;
                }
            }
        }

        $this->transformIsotopeErrorMessages();

        parent::processForm();
    }

    protected function transformIsotopeErrorMessages()
    {
        if (is_array($_SESSION['ISO_ERROR']))
        {
            if (!empty($_SESSION['ISO_ERROR']))
            {
                // no redirect!
                $this->jumpTo = null;
            }

            foreach ($_SESSION['ISO_ERROR'] as $strError)
            {
                StatusMessage::addError($strError, $this->getConfig()->getModule()->id);
            }

            unset($_SESSION['ISO_ERROR']);
        }
    }

    // copy from Checkout.php
    protected function getNotificationTokensFromSteps(array $arrSteps, IsotopeProductCollection $objOrder)
    {
        $arrTokens = array();

        // Run trough all steps to collect checkout information
        foreach ($arrSteps as $objModule)
        {

// foreach ($arrModules as $objModule) {
            $arrTokens = array_merge($arrTokens, $objModule->getNotificationTokens($objOrder));
// }
        }

        return $arrTokens;
    }

    public function setProductCount($count)
    {
        $this->productCount = $count;
    }

    public function getProductCount()
    {
        return $this->productCount;
    }

    public function setTypeCount($count)
    {
        $this->typeCount = $count;
    }

    public function getTypeCount()
    {
        return $this->typeCount;
    }
}

