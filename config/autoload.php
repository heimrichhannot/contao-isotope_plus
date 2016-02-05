<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'HeimrichHannot',
	'Isotope',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'HeimrichHannot\IsotopePlus\DownloadHelper'  => 'system/modules/isotope_plus/classes/helper/DownloadHelper.php',
	'HeimrichHannot\IsotopePlus\IsotopePlus'     => 'system/modules/isotope_plus/classes/IsotopePlus.php',

	// Library
	'Isotope\Form\DirectCheckoutForm'            => 'system/modules/isotope_plus/library/Isotope/Form/DirectCheckoutForm.php',
	'Isotope\Model\RequestCacheOrFilter'         => 'system/modules/isotope_plus/library/Isotope/Model/RequestCacheOrFilter.php',
	'Isotope\Model\Attribute\Youtube'            => 'system/modules/isotope_plus/library/Isotope/Model/Attribute/Youtube.php',
	'Isotope\Module\ProductListPlus'             => 'system/modules/isotope_plus/library/Isotope/Module/ProductListPlus.php',
	'HeimrichHannot\IsotopePlus\Module\CartLink' => 'system/modules/isotope_plus/library/Isotope/Module/CartLink.php',
	'Isotope\Module\ProductListSlick'            => 'system/modules/isotope_plus/library/Isotope/Module/ProductListSlick.php',
	'Isotope\Module\OrderDetailsPlus'            => 'system/modules/isotope_plus/library/Isotope/Module/OrderDetailsPlus.php',
	'Isotope\Module\ProductFilterPlus'           => 'system/modules/isotope_plus/library/Isotope/Module/ProductFilterPlus.php',
	'Isotope\Module\DirectCheckout'              => 'system/modules/isotope_plus/library/Isotope/Module/DirectCheckout.php',
	'Isotope\Module\OrderHistoryPlus'            => 'system/modules/isotope_plus/library/Isotope/Module/OrderHistoryPlus.php',
	'Isotope\Module\ModuleStockReport'           => 'system/modules/isotope_plus/library/Isotope/Module/ModuleStockReport.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'iso_payment_paybyway'            => 'system/modules/isotope_plus/templates/payment',
	'formhybrid_direct_checkout'      => 'system/modules/isotope_plus/templates',
	'iso_scripts'                     => 'system/modules/isotope_plus/templates/isotope',
	'iso_filter_default'              => 'system/modules/isotope_plus/templates/isotope',
	'mod_iso_direct_checkout'         => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_productlist_caching'     => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_checkout'                => 'system/modules/isotope_plus/templates/modules',
	'mod_stockReport'                 => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_orderhistoryplus'        => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_cart_link'               => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_productlist_slider'      => 'system/modules/isotope_plus/templates/modules',
	'isotope_download_from_attribute' => 'system/modules/isotope_plus/templates/downloads',
));
