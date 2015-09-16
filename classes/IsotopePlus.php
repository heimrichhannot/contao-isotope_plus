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


use Haste\Generator\RowClass;
use Haste\Haste;
use HeimrichHannot\HastePlus\Environment;
use HeimrichHannot\HastePlus\Files;
use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Model\Download;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductType;
use NotificationCenter\Model\Notification;

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

	public static function sendOrderNotification($objOrder, $arrTokens)
	{
		$arrItems = $objOrder->getItems();

		// only send one one notification per product type and order
		$arrProductTypes = array();
		foreach ($arrItems as $objItem) {
			$arrProductTypes[] = $objItem->getProduct()->type;
		}

		foreach (array_unique($arrProductTypes) as $intProductType)
		{
			if (($objProductType = ProductType::findByPk($intProductType)) !== null)
			{
				if ($objProductType->sendOrderNotification &&
					($objNotification = Notification::findByPk($objProductType->orderNotification)) !== null) {

					if ($objProductType->removeOtherProducts)
						$objNotification->send(
							static::getCleanTokens($intProductType, $objOrder, $objNotification),
							$GLOBALS['TL_LANGUAGE']
						);
					else
						$objNotification->send($arrTokens, $GLOBALS['TL_LANGUAGE']);
				}
			}
		}
	}

	// copy of code in Order->getNotificationTokens
	public static function getCleanTokens($intProductType, Order $objOrder, $objNotification)
	{
		$objTemplate                 = new \Isotope\Template($objNotification->iso_collectionTpl);
		$objTemplate->isNotification = true;

		// FIX - call to custom function since addToTemplate isn't static
		static::addToTemplate(
			$intProductType,
			$objOrder,
			$objTemplate,
			array(
				'gallery'   => $objNotification->iso_gallery,
				'sorting'   => $objOrder->getItemsSortingCallable($objNotification->iso_orderCollectionBy),
			)
		);

		$arrTokens['cart_html'] = Haste::getInstance()->call('replaceInsertTags', array($objTemplate->parse(), false));
		$objTemplate->textOnly  = true;
		$arrTokens['cart_text'] = strip_tags(Haste::getInstance()->call('replaceInsertTags', array($objTemplate->parse(), true)));

		return $arrTokens;
	}

	// copy of code in ProductCollection->addToTemplate
	public function addToTemplate($intProductType, Order $objOrder, \Template $objTemplate, array $arrConfig = array())
	{
		$arrGalleries = array();
		// FIX - call to custom function since addItemsToTemplate isn't static
		$arrItems     = static::addItemsToTemplate($intProductType, $objOrder, $objTemplate, $arrConfig['sorting']);

		$objTemplate->id                = $objOrder->id;
		$objTemplate->collection        = $objOrder;
		$objTemplate->config            = ($objOrder->getRelated('config_id') || Isotope::getConfig());
		$objTemplate->surcharges        = \Isotope\Frontend::formatSurcharges($objOrder->getSurcharges());
		$objTemplate->subtotal          = Isotope::formatPriceWithCurrency($objOrder->getSubtotal());
		$objTemplate->total             = Isotope::formatPriceWithCurrency($objOrder->getTotal());
		$objTemplate->tax_free_subtotal = Isotope::formatPriceWithCurrency($objOrder->getTaxFreeSubtotal());
		$objTemplate->tax_free_total    = Isotope::formatPriceWithCurrency($objOrder->getTaxFreeTotal());

		$objTemplate->hasAttribute = function ($strAttribute, ProductCollectionItem $objItem) {
			if (!$objItem->hasProduct()) {
				return false;
			}

			$objProduct = $objItem->getProduct();

			return in_array($strAttribute, $objProduct->getAttributes())
			|| in_array($strAttribute, $objProduct->getVariantAttributes());
		};

		$objTemplate->generateAttribute = function (
			$strAttribute,
			ProductCollectionItem $objItem,
			array $arrOptions = array()
		) {
			if (!$objItem->hasProduct()) {
				return '';
			}

			$objAttribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$strAttribute];

			if (!($objAttribute instanceof IsotopeAttribute)) {
				throw new \InvalidArgumentException($strAttribute . ' is not a valid attribute');
			}

			return $objAttribute->generate($objItem->getProduct(), $arrOptions);
		};

		$objTemplate->getGallery = function (
			$strAttribute,
			ProductCollectionItem $objItem
		) use (
			$arrConfig,
			&$arrGalleries
		) {
			if (!$objItem->hasProduct()) {
				return new \Isotope\Model\Gallery\Standard();
			}

			$strCacheKey         = 'product' . $objItem->product_id . '_' . $strAttribute;
			$arrConfig['jumpTo'] = $objItem->getRelated('jumpTo');

			if (!isset($arrGalleries[$strCacheKey])) {
				$arrGalleries[$strCacheKey] = Gallery::createForProductAttribute(
					$objItem->getProduct(),
					$strAttribute,
					$arrConfig
				);
			}

			return $arrGalleries[$strCacheKey];
		};

		// !HOOK: allow overriding of the template
		if (isset($GLOBALS['ISO_HOOKS']['addCollectionToTemplate'])
			&& is_array($GLOBALS['ISO_HOOKS']['addCollectionToTemplate'])
		) {
			foreach ($GLOBALS['ISO_HOOKS']['addCollectionToTemplate'] as $callback) {
				$objCallback = \System::importStatic($callback[0]);
				$objCallback->$callback[1]($objTemplate, $arrItems, $objOrder);
			}
		}
	}

	// copy of code in ProductCollection->generateItem
	protected static function generateItem(ProductCollectionItem $objItem)
	{
		$blnHasProduct = $objItem->hasProduct();
		$objProduct    = $objItem->getProduct();

		// Set the active product for insert tags replacement
		if ($blnHasProduct) {
			Product::setActive($objProduct);
		}

		$arrCSS = ($blnHasProduct ? deserialize($objProduct->cssID, true) : array());

		$arrItem = array(
			'id'                => $objItem->id,
			'sku'               => $objItem->getSku(),
			'name'              => $objItem->getName(),
			'options'           => Isotope::formatOptions($objItem->getOptions()),
			'configuration'     => $objItem->getConfiguration(),
			'quantity'          => $objItem->quantity,
			'price'             => Isotope::formatPriceWithCurrency($objItem->getPrice()),
			'tax_free_price'    => Isotope::formatPriceWithCurrency($objItem->getTaxFreePrice()),
			'total'             => Isotope::formatPriceWithCurrency($objItem->getTotalPrice()),
			'tax_free_total'    => Isotope::formatPriceWithCurrency($objItem->getTaxFreeTotalPrice()),
			'tax_id'            => $objItem->tax_id,
			'href'              => false,
			'hasProduct'        => $blnHasProduct,
			'product'           => $objProduct,
			'item'              => $objItem,
			'raw'               => $objItem->row(),
			'rowClass'          => trim('product ' . (($blnHasProduct && $objProduct->isNew()) ? 'new ' : '') . $arrCSS[1]),
		);

		if (null !== $objItem->getRelated('jumpTo') && $blnHasProduct && $objProduct->isAvailableInFrontend()) {
			$arrItem['href'] = $objProduct->generateUrl($objItem->getRelated('jumpTo'));
		}

		Product::unsetActive();

		return $arrItem;
	}

	// copy of code in ProductCollection->addItemsToTemplate
	protected function addItemsToTemplate($intProductType, $objOrder, \Template $objTemplate, $varCallable = null)
	{
		$taxIds   = array();
		$arrItems = array();

		foreach ($objOrder->getItems($varCallable) as $objItem) {
			// FIX - check for product type id
			if ($objItem->getProduct()->type != $intProductType)
				continue;
			// ENDFIX

			$item = static::generateItem($objItem);

			$taxIds[]   = $item['tax_id'];
			$arrItems[] = $item;
		}

		RowClass::withKey('rowClass')->addCount('row_')->addFirstLast('row_')->addEvenOdd('row_')->applyTo($arrItems);

		$objTemplate->items         = $arrItems;
		$objTemplate->total_tax_ids = count(array_unique($taxIds));

		return $arrItems;
	}

}