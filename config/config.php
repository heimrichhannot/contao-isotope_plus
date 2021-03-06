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

define('ISO_PRODUCT_CREATOR_SINGLE_IMAGE_PRODUCT', 'HeimrichHannot\IsotopePlus\SingleImageProduct');
define('ISO_PRODUCT_CREATOR_MULTI_IMAGE_PRODUCT', 'HeimrichHannot\IsotopePlus\MultiImageProduct');

$GLOBALS['ISO_HOOKS']['generateProduct'][]                                       = ['HeimrichHannot\IsotopePlus\IsotopePlus', 'generateProductHook'];
$GLOBALS['ISO_HOOKS']['addProductToCollection']['validateStockCollectionAdd']    =
	['HeimrichHannot\IsotopePlus\IsotopePlus', 'validateStockCollectionAdd'];
$GLOBALS['ISO_HOOKS']['preCheckout']['validateStockCheckout']                    =
	['HeimrichHannot\IsotopePlus\IsotopePlus', 'validateStockPreCheckout'];
$GLOBALS['ISO_HOOKS']['postCheckout']['validateStockCheckout']                   =
	['HeimrichHannot\IsotopePlus\IsotopePlus', 'validateStockPostCheckout'];
$GLOBALS['ISO_HOOKS']['postCheckout']['sendOrderNotification']                   =
	['HeimrichHannot\IsotopePlus\IsotopePlus', 'sendOrderNotification'];
$GLOBALS['ISO_HOOKS']['postCheckout']['setSetQuantity']                          = ['HeimrichHannot\IsotopePlus\IsotopePlus', 'setSetQuantity'];
$GLOBALS['ISO_HOOKS']['updateItemInCollection']['validateStockCollectionUpdate'] =
	['HeimrichHannot\IsotopePlus\IsotopePlus', 'validateStockCollectionUpdate'];
$GLOBALS['ISO_HOOKS']['buttons'][]                                               =
	['HeimrichHannot\IsotopePlus\IsotopePlus', 'addDownloadSingleProductButton'];
$GLOBALS['ISO_HOOKS']['preOrderStatusUpdate']['updateStock']                     = ['HeimrichHannot\IsotopePlus\IsotopePlus', 'updateStock'];

$GLOBALS['TL_HOOKS']['replaceDynamicScriptTags'][]             = ['HeimrichHannot\IsotopePlus\IsotopePlus', 'hookReplaceDynamicScriptTags'];
$GLOBALS['ISO_HOOKS']['generateProduct']['updateTemplateData'] = ['\HeimrichHannot\IsotopePlus\IsotopePlus', 'updateTemplateData'];
$GLOBALS['TL_HOOKS']['postDownload']['downloadCounter']        = ['HeimrichHannot\IsotopePlus\IsotopePlus', 'updateDownloadCounter'];
$GLOBALS['TL_HOOKS']['parseItems']['addPdfViewerToTemplate']   = ['HeimrichHannot\IsotopePlus\ProductHelper', 'addPdfViewerToTemplate'];


/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotopeplus'] = [
	'iso_productfilterplus'       => 'Isotope\Module\ProductFilterPlus',
	'iso_productlistplus'         => 'Isotope\Module\ProductListPlus',
	'iso_stockreport'             => 'Isotope\Module\ModuleStockReport',
	'iso_orderreport'             => 'Isotope\Module\ModuleOrderReport',
	'iso_cart_link'               => 'HeimrichHannot\IsotopePlus\Module\CartLink',
	'iso_orderhistory_plus'       => 'Isotope\Module\OrderHistoryPlus',
	'iso_orderdetails_plus'       => 'Isotope\Module\OrderDetailsPlus',
	'iso_direct_checkout'         => 'Isotope\Module\DirectCheckout',
	'iso_product_ranking'         => 'Isotope\Module\ProductRanking',
	'iso_product_frontend_editor' => 'Isotope\Module\ProductFrontendEditor',
];

if (in_array('slick', \ModuleLoader::getActive())) {
	$GLOBALS['FE_MOD']['isotopeplus']['iso_productlistslick'] = 'Isotope\Module\ProductListSlick';
}


/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_iso_product'] = 'Isotope\Model\ProductModel';

/**
 * Notification Center Notification Types
 */
if (in_array('notification_center_plus', \ModuleLoader::getActive())) {
	$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'][] = 'salutation_user';
	$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'][] = 'salutation_form';
	$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'][] = 'salutation_billing_address';
}

/**
 * Attributes
 */
\Isotope\Model\Attribute::registerModelType('youtube', 'Isotope\Model\Attribute\Youtube');

/**
 * JS
 */
if (TL_MODE == 'FE') {
	$GLOBALS['TL_JAVASCRIPT']['tablesorter']  = 'assets/components/tablesorter/js/tablesorter.min.js|static';
	$GLOBALS['TL_JAVASCRIPT']['isotope_plus'] =
		'system/modules/isotope_plus/assets/js/isotope_plus' . (!$GLOBALS['TL_CONFIG']['debugMode'] ? '.min' : '') . '.js|static';
}

