<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package isotope_plus
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

$GLOBALS['ISO_HOOKS']['generateProduct'][] = array('HeimrichHannot\Isotope\Hooks', 'generateProductHook');
$GLOBALS['ISO_HOOKS']['addProductToCollection'][] = array('HeimrichHannot\Isotope\Hooks', 'addProductToCollectionHook');
$GLOBALS['ISO_HOOKS']['postAddProductToCollection'][] = array('HeimrichHannot\Isotope\Hooks', 'postAddProductToCollectionHook');
$GLOBALS['ISO_HOOKS']['preCheckout'][] = array('HeimrichHannot\Isotope\Hooks', 'preCheckoutHook');
$GLOBALS['ISO_HOOKS']['updateItemInCollection'][] = array('HeimrichHannot\Isotope\Hooks', 'updateItemInCollectionHook');

/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotopeplus'] = array
(
	'iso_productfilterplus' => 'Isotope\Module\ProductFilterPlus',
	'iso_productlistplus'   => 'Isotope\Module\ProductListPlus',
	'iso_stockreport'       => 'Isotope\Module\ModuleStockReport',
	'iso_orderreport'       => 'Isotope\Module\ModuleOrderReport',
);

/**
 * Attributes
 */
\Isotope\Model\Attribute::registerModelType('youtube', 'Isotope\Model\Attribute\Youtube');

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_belegungsplan_data'] = '\HeimrichHannot\Isotope\BookingDataModel';