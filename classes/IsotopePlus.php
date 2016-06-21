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
use Isotope\Frontend;
use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Model\Config;
use Isotope\Model\Download;
use Isotope\Model\Gallery;
use Isotope\Model\Gallery\Standard;
use Isotope\Model\Product;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductType;
use Isotope\Template;
use NotificationCenter\Model\Notification;

class IsotopePlus extends \Isotope\Isotope
{
	/**
	 * Remove isotope js, as we dont want to use mootools
	 *
	 * @param $strBuffer
	 *
	 * @return mixed
	 */
	public function hookReplaceDynamicScriptTags($strBuffer)
	{
		if(!is_array($GLOBALS['TL_JAVASCRIPT']))
		{
			return $strBuffer;
		}

		$arrJs = $GLOBALS['TL_JAVASCRIPT'];

		foreach($arrJs as $key => $strValue)
		{
			$arrData = trimsplit('|', $strValue);

			if(\HeimrichHannot\Haste\Util\StringUtil::endsWith($arrData[0], 'system/modules/isotope/assets/js/isotope.min.js'))
			{
				unset($GLOBALS['TL_JAVASCRIPT'][$key]);
			}
		}

		return $strBuffer;
	}

	public function generateProductHook(&$objTemplate, $objProduct)
	{
		DownloadHelper::addDownloadsFromProductDownloadsToTemplate($objTemplate);
	}

	public static function validateStockPreCheckout($objOrder)
	{
		return self::validateStockCheckout($objOrder);
	}

	public static function validateStockPostCheckout($objOrder)
	{
		return self::validateStockCheckout($objOrder, true);
	}

	public static function validateStockCheckout($objOrder, $blnIsPostCheckout = false)
	{
		$arrItems  = $objOrder->getItems();
		$arrOrders = array();

		foreach ($arrItems as $objItem) {
			$objProduct = $objItem->getProduct();

			if ($objProduct->stock != '' && $objProduct->stock !== null) {
				// override the quantity!
				if (!static::validateQuantity($objProduct, $objItem->quantity)) {
					return false;
				}

				if ($blnIsPostCheckout)
				{
					$arrOrders[] = $objItem;
				}
			}
		}

		// save new stock
		if ($blnIsPostCheckout) {
			foreach ($arrOrders as $objItem) {
				$objProduct = $objItem->getProduct();

				if (static::getOverridableStockProperty('skipStockEdit', $objProduct))
					continue;

				$intQuantity = static::getTotalStockQuantity($objItem->quantity, $objProduct);

				$objProduct->stock -= $intQuantity;

				if ($objProduct->stock <= 0
					&& !static::getOverridableStockProperty('skipExemptionFromShippingWhenStockEmpty', $objProduct)
				) {
					$objProduct->shipping_exempt = true;
				}

				$objProduct->save();
			}
		}

		return true;
	}

	public function validateStockCollectionAdd($objProduct, $intQuantity, ProductCollection $objProductCollection)
	{
		if (!$this->validateQuantity($objProduct, $intQuantity, $objProductCollection->getItemForProduct($objProduct))
		) {
			return 0;
		} else {
			unset($_SESSION['ISO_ERROR']);
		}

		return $intQuantity;
	}


	public function validateStockCollectionUpdate($objItem, $arrSet)
	{
		$objProduct = Product::findPublishedByPk($objItem->product_id);

		if (!static::validateQuantity($objProduct, $arrSet['quantity'])) {
			\Controller::reload();
		}

		return $arrSet;
	}

	// watch out: also in backend the current set quantity is used
	public static function getTotalStockQuantity($intQuantity, $objProduct, $objCartItem = null, $intSetQuantity = null, $objConfig = null)
	{
		$intFinalSetQuantity = 1;

		if ($intSetQuantity) {
			$intFinalSetQuantity = $intSetQuantity;
		} elseif (!static::getOverridableShopConfigProperty('skipSets', $objConfig) && $objProduct->setQuantity) {
			$intFinalSetQuantity = $objProduct->setQuantity;
		}

		$intQuantity *= $intFinalSetQuantity;

		if ($objCartItem !== null) {
			$intQuantity += $objCartItem->quantity * $intFinalSetQuantity;
		}

		return $intQuantity;
	}

	/**
	 * @param            $objProduct
	 * @param            $intQuantity
	 * @param null       $objCartItem
	 * @param bool|false $blnIncludeError
	 * @param bool|false $skipSets override the normal handling of this property -> used for backend handling since this
	 *                             uses the order item's setQuantity property
	 *
	 * @return array|bool
	 */
	public static function validateQuantity($objProduct, $intQuantity, $objCartItem = null, $blnIncludeError = false, $intSetQuantity = null)
	{
		$blnSkipStockValidation = static::getOverridableStockProperty('skipStockValidation', $objProduct);

		// no quantity at all
		if ($intQuantity === null) {
			return true;
		} elseif ($intQuantity == '') {
			$intQuantity = 1;
		}

		$intQuantityTotal = static::getTotalStockQuantity($intQuantity, $objProduct, $objCartItem, $intSetQuantity);

		// stock
		if (!$blnSkipStockValidation && $objProduct->stock != '' && $objProduct->stock !== null) {
			if ($objProduct->stock <= 0) {
				$strErrorMessage = sprintf($GLOBALS['TL_LANG']['MSC']['stockEmpty'], $objProduct->name);

				if (TL_MODE == 'FE') {
					$_SESSION['ISO_ERROR'][] = $strErrorMessage;
				} else {
					\Message::addError($strErrorMessage);
				}

				if ($blnIncludeError) {
					return array(false, $strErrorMessage);
				} else {
					return false;
				}
			} elseif ($intQuantityTotal > $objProduct->stock) {
				$strErrorMessage = sprintf($GLOBALS['TL_LANG']['MSC']['stockExceeded'], $objProduct->name, $objProduct->stock);

				if (TL_MODE == 'FE') {
					$_SESSION['ISO_ERROR'][] = $strErrorMessage;
				} else {
					\Message::addError($strErrorMessage);
				}

				if ($blnIncludeError) {
					return array(false, $strErrorMessage);
				} else {
					return false;
				}
			}
		}

		// maxOrderSize
		if ($objProduct->maxOrderSize != '' && $objProduct->maxOrderSize !== null) {
			if ($intQuantityTotal > $objProduct->maxOrderSize) {
				$strErrorMessage = sprintf(
					$GLOBALS['TL_LANG']['MSC']['maxOrderSizeExceeded'],
					$objProduct->name,
					$objProduct->maxOrderSize
				);

				if (TL_MODE == 'FE') {
					$_SESSION['ISO_ERROR'][] = $strErrorMessage;
				} else {
					\Message::addError($strErrorMessage);
				}


				if ($blnIncludeError) {
					return array(false, $strErrorMessage);
				} else {
					return false;
				}
			}
		}

		if ($blnIncludeError) {
			return array(true, null);
		} else {
			return true;
		}
	}

	public static function setSetQuantity($objOrder, $arrTokens)
	{
		if (static::getOverridableShopConfigProperty('skipSets')) {
			return;
		}

		$arrItems = $objOrder->getItems();

		foreach ($arrItems as $objItem) {
			$objProduct = $objItem->getProduct();

			if ($objProduct->setQuantity) {
				$objItem->setQuantity = $objProduct->setQuantity;
				$objItem->save();
			}
		}
	}

	public static function addDownloadSingleProductButton($arrButtons)
	{
		$arrButtons['downloadSingleProduct'] = array(
			'label'    => $GLOBALS['TL_LANG']['MSC']['buttonLabel']['downloadSingleProduct'],
			'callback' => array('\HeimrichHannot\IsotopePlus\IsotopePlus', 'downloadSingleProduct'),
		);

		return $arrButtons;
	}

	/**
	 * Currently only works for products containing one single download
	 *
	 * @param IsotopeProduct $objProduct
	 * @param array          $arrConfig
	 */
	public function downloadSingleProduct(IsotopeProduct $objProduct, array $arrConfig = array())
	{
		if (($objDownload = Download::findByPid($objProduct->getProductId())) !== null
			&& $strPath = Files::getPathFromUuid($objDownload->singleSRC)
		) {
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

		foreach (array_unique($arrProductTypes) as $intProductType) {
			if (($objProductType = ProductType::findByPk($intProductType)) !== null) {
				if ($objProductType->sendOrderNotification
					&& ($objNotification = Notification::findByPk($objProductType->orderNotification)) !== null
				) {

					if ($objProductType->removeOtherProducts) {
						$objNotification->send(
							static::getCleanTokens($intProductType, $objOrder, $objNotification, $arrTokens),
							$GLOBALS['TL_LANGUAGE']
						);
					} else {
						$objNotification->send($arrTokens, $GLOBALS['TL_LANGUAGE']);
					}
				}
			}
		}
	}

	// copy of code in Order->getNotificationTokens
	public static function getCleanTokens($intProductType, Order $objOrder, $objNotification, $arrTokens = array())
	{
		$objTemplate                 = new Template($objNotification->iso_collectionTpl);
		$objTemplate->isNotification = true;

		// FIX - call to custom function since addToTemplate isn't static
		static::addToTemplate(
			$intProductType,
			$objOrder,
			$objTemplate,
			array(
				'gallery' => $objNotification->iso_gallery,
				'sorting' => $objOrder->getItemsSortingCallable($objNotification->iso_orderCollectionBy),
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
		$arrItems = static::addItemsToTemplate($intProductType, $objOrder, $objTemplate, $arrConfig['sorting']);

		$objTemplate->id                = $objOrder->id;
		$objTemplate->collection        = $objOrder;
		$objTemplate->config            = ($objOrder->getRelated('config_id') || Isotope::getConfig());
		$objTemplate->surcharges        = Frontend::formatSurcharges($objOrder->getSurcharges());
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
				return new Standard();
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
			'id'             => $objItem->id,
			'sku'            => $objItem->getSku(),
			'name'           => $objItem->getName(),
			'options'        => Isotope::formatOptions($objItem->getOptions()),
			'configuration'  => $objItem->getConfiguration(),
			'quantity'       => $objItem->quantity,
			'price'          => Isotope::formatPriceWithCurrency($objItem->getPrice()),
			'tax_free_price' => Isotope::formatPriceWithCurrency($objItem->getTaxFreePrice()),
			'total'          => Isotope::formatPriceWithCurrency($objItem->getTotalPrice()),
			'tax_free_total' => Isotope::formatPriceWithCurrency($objItem->getTaxFreeTotalPrice()),
			'tax_id'         => $objItem->tax_id,
			'href'           => false,
			'hasProduct'     => $blnHasProduct,
			'product'        => $objProduct,
			'item'           => $objItem,
			'raw'            => $objItem->row(),
			'rowClass'       => trim('product ' . (($blnHasProduct && $objProduct->isNew()) ? 'new ' : '') . $arrCSS[1]),
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
			if ($objItem->getProduct()->type != $intProductType) {
				continue;
			}
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

	// priorities (first is the most important):
	// product, product type, global shop config
	public static function getOverridableStockProperty($strProperty, $objProduct)
	{
		// at first check for product and product type
		if ($objProduct->overrideStockShopConfig) {
			return $objProduct->{$strProperty};
		} else {
			if (($objProductType = ProductType::findByPk($objProduct->type)) !== null && $objProductType->overrideStockShopConfig) {
				return $objProductType->{$strProperty};
			}
		}

		// nothing returned?
		$objConfig = Isotope::getConfig();
//		global $objPage;
//
//		if ($objPage->iso_config)
//		{
//			$objConfig = Config::findByPk($objPage->iso_config);
//		}

		// defaultly return the value defined in the global config
		return $objConfig->{$strProperty};
	}

	public static function getOverridableShopConfigProperty($strProperty, $objConfig = null)
	{
		if (!$objConfig) {
			$objConfig = Isotope::getConfig();
		}
//		global $objPage;
//
//		if ($objPage->iso_config)
//		{
//			$objConfig = Config::findByPk($objPage->iso_config);
//		}

		return $objConfig->{$strProperty};
	}

	public static function updateStock(Order $objOrder, $objNewStatus)
	{
		// atm only for backend
		if (TL_MODE != 'BE') {
			return false;
		}

		// the order's config is used!
		$objConfig = Isotope::getConfig();

		$arrStockIncreaseOrderStates = deserialize($objConfig->stockIncreaseOrderStates, true);

		// e.g. new -> cancelled => increase the stock based on the order item's setQuantity-values (no validation required, of course)
		if (!in_array($objOrder->order_status, $arrStockIncreaseOrderStates) && in_array($objNewStatus->id, $arrStockIncreaseOrderStates)) {
			foreach ($objOrder->getItems() as $objItem) {
				if (($objProduct = $objItem->getProduct()) !== null) {
					$intTotalQuantity = static::getTotalStockQuantity($objItem->quantity, $objProduct, null, $objItem->setQuantity);

					if ($intTotalQuantity) {
						$objProduct->stock += $intTotalQuantity;
						$objProduct->save();
					}
				}
			}
		} // e.g. cancelled -> new => decrease the stock after validation
		elseif (in_array($objOrder->order_status, $arrStockIncreaseOrderStates) && !in_array($objNewStatus->id, $arrStockIncreaseOrderStates)) {
			foreach ($objOrder->getItems() as $objItem) {
				if (($objProduct = $objItem->getProduct()) !== null) {
					$blnSkipValidation = static::getOverridableStockProperty('skipStockValidation', $objProduct);

					// watch out: also in backend the current set quantity is used for validation!
					if (!$blnSkipValidation && !static::validateQuantity($objProduct, $objItem->quantity)) {
						// if the validation breaks for only one product collection item -> cancel the order status transition
						return true;
					}
				}
			}

			foreach ($objOrder->getItems() as $objItem) {
				if (($objProduct = $objItem->getProduct()) !== null) {
					$intTotalQuantity = static::getTotalStockQuantity($objItem->quantity, $objProduct);

					if ($intTotalQuantity) {
						$objProduct->stock -= $intTotalQuantity;

						if ($objProduct->stock <= 0
							&& !static::getOverridableStockProperty('skipExemptionFromShippingWhenStockEmpty', $objProduct)
						) {
							$objProduct->shipping_exempt = true;
						}

						$objProduct->save();
					}
				}
			}
		}

		// don't cancel
		return false;
	}

}