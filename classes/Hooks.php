<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package isotope_plus
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Isotope;


class Hooks extends \Isotope\Isotope
{
	public function generateProductHook(&$objTemplate, $objProduct)
	{
        DownloadHelper::addDownloadsFromProductDownloadsToTemplate($objTemplate);
	}


    /**
     * Validierung im Warenkorb
     */
    public function addProductToCollectionHook($objProduct, $intQuantity)
    {
		if( !$this->isOrderSizeValid($objProduct, $intQuantity) )
		{
			return 0;
		} else {
			unset($_SESSION['ISO_ERROR']);
		}

        return $intQuantity;
    }


	/**
	 * Check for stocks and block shipping if there is not enough
	 *
	 * @param $objOrder
	 * @return boolean
	 */
    public function preCheckoutHook($objOrder)
    {
        $arrItems = $objOrder->getItems();
		$arrOrders = array();

        foreach($arrItems as $objItem)
        {
            $objProduct = $objItem->getProduct();

			if($objProduct->stock) {
				if( !static::isOrderSizeValid($objProduct, $objItem->quantity) ) return false;
				$arrOrders[] = $objItem;
			}
        }

		// save new stock and if stock empty block shipping
		foreach($arrOrders as $objItem)
		{
				$objProduct = $objItem->getProduct();
				$objProduct->stock -= $objItem->quantity;
				if($objProduct->stock <= 0) {
					$objProduct->shipping_exempt = '1';
				}
				$objProduct->save();
		}

		return true;
    }

	/**
	 * Check for updated quantity
	 *
	 * @param $objItem
	 * @param $arrSet
	 * @param $objCollection
	 * @return $arrSet
	 */
	public function updateItemInCollectionHook($objItem, $arrSet, $objCollection)
	{
		$objProduct = \Isotope\Model\Product::findPublishedByPk($objItem->product_id);
		if( !static::isOrderSizeValid($objProduct, $arrSet['quantity']) ) {
			\Controller::reload();
		}
		return $arrSet;
	}


	public static function isOrderSizeValid($objProduct, $intQuantity)
	{
		if($objProduct->stock <= 0 || $objProduct->stock == '' || $objProduct->stock == null || $intQuantity > $objProduct->stock) {
			$_SESSION['ISO_ERROR'][] = 'Ihre Bestellmenge liegt über der noch vorhandenen Menge.';
		} elseif($intQuantity <= $objProduct->max_order_size || $objProduct->max_order_size == '') {
			return true;
		} else {
			$_SESSION['ISO_ERROR'][] = 'Ihre Bestellmenge liegt über der maximal zulässigen Menge.';
		}
		return false;
	}
}