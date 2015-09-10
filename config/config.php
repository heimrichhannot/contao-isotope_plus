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
$GLOBALS['ISO_HOOKS']['preCheckout']['isotope_plus_preCheckoutHook'] = array('HeimrichHannot\IsotopePlus\IsotopePlus', 'preCheckoutHook');
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
);

if (in_array('slick', \ModuleLoader::getActive()))
{
	$GLOBALS['FE_MOD']['isotopeplus']['iso_productlistslick']  = 'Isotope\Module\ProductListSlick';
}

/**
 * Attributes
 */
\Isotope\Model\Attribute::registerModelType('youtube', 'Isotope\Model\Attribute\Youtube');