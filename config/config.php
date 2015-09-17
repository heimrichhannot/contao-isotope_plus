<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package isotope_plus
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$GLOBALS['ISO_HOOKS']['generateProduct'][] = array('HeimrichHannot\IsotopePlus\IsotopePlus', 'generateProductHook');
$GLOBALS['ISO_HOOKS']['addProductToCollection'][] = array('HeimrichHannot\IsotopePlus\IsotopePlus', 'addProductToCollectionHook');
$GLOBALS['ISO_HOOKS']['preCheckout']['checkOrderForStock'] = array('HeimrichHannot\IsotopePlus\IsotopePlus', 'checkOrderForStock');
$GLOBALS['ISO_HOOKS']['postCheckout']['sendOrderNotification'] = array('HeimrichHannot\IsotopePlus\IsotopePlus', 'sendOrderNotification');
$GLOBALS['ISO_HOOKS']['updateItemInCollection'][] = array('HeimrichHannot\IsotopePlus\IsotopePlus', 'updateItemInCollectionHook');
$GLOBALS['ISO_HOOKS']['buttons'][] = array('HeimrichHannot\IsotopePlus\IsotopePlus', 'addDownloadSingleProductButton');

/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotopeplus'] = array
(
	'iso_productfilterplus' => 'Isotope\Module\ProductFilterPlus',
	'iso_productlistplus'   => 'Isotope\Module\ProductListPlus',
	'iso_stockreport'       => 'Isotope\Module\ModuleStockReport',
	'iso_orderreport'       => 'Isotope\Module\ModuleOrderReport',
	'iso_cart_link'         => 'HeimrichHannot\IsotopePlus\Module\CartLink',
);

if (in_array('slick', \ModuleLoader::getActive()))
{
	$GLOBALS['FE_MOD']['isotopeplus']['iso_productlistslick']  = 'Isotope\Module\ProductListSlick';
}

/**
 * Notification Center Notification Types
 */
if (in_array('notification_center_plus', \ModuleLoader::getActive()))
{
	$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'][] = 'salutation_user';
	$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'][] = 'salutation_form';
}

	/**
 * Attributes
 */
\Isotope\Model\Attribute::registerModelType('youtube', 'Isotope\Model\Attribute\Youtube');