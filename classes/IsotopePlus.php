<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 *
 * @package isotope_plus
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\IsotopePlus;


use HeimrichHannot\HastePlus\Environment;
use HeimrichHannot\HastePlus\Files;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Download;

class IsotopePlus extends \Isotope\Isotope
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
		if (!$this->isOrderSizeValid($objProduct, $intQuantity)) {
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
	 *
	 * @return boolean
	 */
	public static function checkOrderForStock($objOrder)
	{
		$arrItems = $objOrder->getItems();
		$arrOrders = array();

		foreach ($arrItems as $objItem) {
			$objProduct = $objItem->getProduct();

			if ($objProduct->stock) {
				if (!static::isOrderSizeValid($objProduct, $objItem->quantity)) {
					return false;
				}
				$arrOrders[] = $objItem;
			}
		}

		// save new stock and if stock empty block shipping
		foreach ($arrOrders as $objItem) {
			$objProduct = $objItem->getProduct();
			$objProduct->stock -= $objItem->quantity;
			if ($objProduct->stock <= 0) {
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
	 *
	 * @return $arrSet
	 */
	public function updateItemInCollectionHook($objItem, $arrSet, $objCollection)
	{
		$objProduct = \Isotope\Model\Product::findPublishedByPk($objItem->product_id);
		if (!static::isOrderSizeValid($objProduct, $arrSet['quantity'])) {
			\Controller::reload();
		}
		return $arrSet;
	}


	public static function isOrderSizeValid($objProduct, $intQuantity)
	{
		if ($objProduct->stock <= 0 || $objProduct->stock == '' || $objProduct->stock == null
			|| $intQuantity > $objProduct->stock
		) {
			$_SESSION['ISO_ERROR'][] = 'Ihre Bestellmenge liegt über der noch vorhandenen Menge.';
		} elseif ($intQuantity <= $objProduct->max_order_size || $objProduct->max_order_size == '') {
			return true;
		} else {
			$_SESSION['ISO_ERROR'][] = 'Ihre Bestellmenge liegt über der maximal zulässigen Menge.';
		}
		return false;
	}

	public static function addDownloadSingleProductButton($arrButtons)
	{
		$arrButtons['downloadSingleProduct'] = array(
			'label' => $GLOBALS['TL_LANG']['MSC']['buttonLabel']['downloadSingleProduct'],
			'callback' => array('\HeimrichHannot\IsotopePlus\IsotopePlus', 'downloadSingleProduct')
		);

		return $arrButtons;
	}

	/**
	 * Currently only works for products containing one single download
	 * @param IsotopeProduct $objProduct
	 * @param array          $arrConfig
	 */
	public function downloadSingleProduct(IsotopeProduct $objProduct, array $arrConfig = array())
	{
		if (($objDownload = Download::findByPid($objProduct->getProductId())) !== null &&
			$strPath = Files::getPathFromUuid($objDownload->singleSRC))
		{
			// TODO count downloads
			// start downloading the file (protected folders also supported)
			\Controller::redirect(Environment::getUrl() . '?file=' . $strPath);
		}
	}
}