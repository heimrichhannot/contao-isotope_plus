<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Isotope',
	'HeimrichHannot',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Library
	'Isotope\Module\OrderHistoryPlus'                 => 'system/modules/isotope_plus/library/Isotope/Module/OrderHistoryPlus.php',
	'Isotope\Module\DirectCheckout'                   => 'system/modules/isotope_plus/library/Isotope/Module/DirectCheckout.php',
	'HeimrichHannot\IsotopePlus\Module\CartLink'      => 'system/modules/isotope_plus/library/Isotope/Module/CartLink.php',
	'Isotope\Module\ProductFrontendCreator'           => 'system/modules/isotope_plus/library/Isotope/Module/ProductFrontendCreator.php',
	'Isotope\Module\OrderDetailsPlus'                 => 'system/modules/isotope_plus/library/Isotope/Module/OrderDetailsPlus.php',
	'Isotope\Module\ProductListPlus'                  => 'system/modules/isotope_plus/library/Isotope/Module/ProductListPlus.php',
	'Isotope\Module\ProductRanking'                   => 'system/modules/isotope_plus/library/Isotope/Module/ProductRanking.php',
	'Isotope\Module\ProductListSlick'                 => 'system/modules/isotope_plus/library/Isotope/Module/ProductListSlick.php',
	'Isotope\Module\ProductFrontendEditor'            => 'system/modules/isotope_plus/library/Isotope/Module/ProductFrontendEditor.php',
	'Isotope\Module\ModuleStockReport'                => 'system/modules/isotope_plus/library/Isotope/Module/ModuleStockReport.php',
	'Isotope\Module\ProductFilterPlus'                => 'system/modules/isotope_plus/library/Isotope/Module/ProductFilterPlus.php',
	'Isotope\Form\ProductFrontendCreatorForm'         => 'system/modules/isotope_plus/library/Isotope/Form/ProductFrontendCreatorForm.php',
	'Isotope\Form\ProductFrontendEditorForm'          => 'system/modules/isotope_plus/library/Isotope/Form/ProductFrontendEditorForm.php',
	'Isotope\Form\DirectCheckoutForm'                 => 'system/modules/isotope_plus/library/Isotope/Form/DirectCheckoutForm.php',
	'Isotope\Model\CreatorProduct'                    => 'system/modules/isotope_plus/library/Isotope/Model/CreatorProduct.php',
	'Isotope\Model\Attribute\Youtube'                 => 'system/modules/isotope_plus/library/Isotope/Model/Attribute/Youtube.php',
	'Isotope\Model\RequestCacheOrFilter'              => 'system/modules/isotope_plus/library/Isotope/Model/RequestCacheOrFilter.php',
	'Isotope\Model\ProductCreator'                    => 'system/modules/isotope_plus/library/Isotope/Model/ProductCreator.php',

	// Classes
	'HeimrichHannot\IsotopePlus\DownloadHelper'       => 'system/modules/isotope_plus/classes/helper/DownloadHelper.php',
	'HeimrichHannot\IsotopePlus\CreatorProductHelper' => 'system/modules/isotope_plus/classes/helper/CreatorProductHelper.php',
	'HeimrichHannot\IsotopePlus\Callbacks'            => 'system/modules/isotope_plus/classes/helper/Callbacks.php',
	'HeimrichHannot\IsotopePlus\IsotopePlus'          => 'system/modules/isotope_plus/classes/IsotopePlus.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'iso_payment_paybyway'                      => 'system/modules/isotope_plus/templates/payment',
	'mod_stockReport'                           => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_orderhistoryplus'                  => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_cart_link'                         => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_product_ranking'                   => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_direct_checkout'                   => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_productlist_slider'                => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_productlist_caching'               => 'system/modules/isotope_plus/templates/modules',
	'mod_iso_checkout'                          => 'system/modules/isotope_plus/templates/modules',
	'iso_filter_default'                        => 'system/modules/isotope_plus/templates/isotope',
	'iso_scripts'                               => 'system/modules/isotope_plus/templates/isotope',
	'formhybrid_creator_editor_image'           => 'system/modules/isotope_plus/templates/editor',
	'frontendedit_list_item_table_product_list' => 'system/modules/isotope_plus/templates/editor',
	'isotope_download_from_attribute'           => 'system/modules/isotope_plus/templates/downloads',
	'formhybrid_direct_checkout'                => 'system/modules/isotope_plus/templates',
));
