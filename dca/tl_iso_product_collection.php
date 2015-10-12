<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_iso_product_collection'];

/**
 * Callbacks
 */
$arrDca['config']['ondelete_callback'][] = array
(
	'tl_iso_product_collection_isotope_plus', 'increaseStock'
);

class tl_iso_product_collection_isotope_plus {

	public static function increaseStock(\DataContainer $objDc, $intInsertId)
	{
		if (($objOrder = \Isotope\Model\ProductCollection::findByPk($objDc->activeRecord->id)) !== null)
		{
			$objConfig = $objOrder->getRelated('config_id');

			// if the order had already been set to a stock increasing state, the stock doesn't need to be increased again
			if (in_array($objOrder->order_status, deserialize($objConfig->stockIncreaseOrderStates, true)))
				return;

			foreach ($objOrder->getItems() as $objItem) {
				if (($objProduct = $objItem->getProduct()) !== null)
				{
					$intTotalQuantity = \HeimrichHannot\IsotopePlus\IsotopePlus::getTotalStockQuantity($objItem->quantity, $objProduct, null, $objItem->setQuantity, $objConfig);

					if ($intTotalQuantity)
					{
						$objProduct->stock += $intTotalQuantity;
						$objProduct->save();
					}
				}
			}
		}
	}

}