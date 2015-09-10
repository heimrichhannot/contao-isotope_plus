<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
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
	'Isotope\Module\ProductListSlick'           => 'system/modules/isotope_plus/library/Isotope/Module/ProductListSlick.php',
	'Isotope\Module\ProductListPlus'            => 'system/modules/isotope_plus/library/Isotope/Module/ProductListPlus.php',
	'Isotope\Module\ModuleOrderReport'          => 'system/modules/isotope_plus/library/Isotope/Module/ModuleOrderReport.php',
	'Isotope\Module\ProductFilterPlus'          => 'system/modules/isotope_plus/library/Isotope/Module/ProductFilterPlus.php',
	'Isotope\Module\ModuleStockReport'          => 'system/modules/isotope_plus/library/Isotope/Module/ModuleStockReport.php',
	'Isotope\Model\RequestCacheOrFilter'        => 'system/modules/isotope_plus/library/Isotope/Model/RequestCacheOrFilter.php',
	'Isotope\Model\Attribute\Youtube'           => 'system/modules/isotope_plus/library/Isotope/Model/Attribute/Youtube.php',

	// Classes
	'HeimrichHannot\IsotopePlus\DownloadHelper' => 'system/modules/isotope_plus/classes/helper/DownloadHelper.php',
	'HeimrichHannot\IsotopePlus\IsotopePlus'    => 'system/modules/isotope_plus/classes/IsotopePlus.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_iso_productlist_slider'      => 'system/modules/isotope_plus/templates',
	'mod_stockReport'                 => 'system/modules/isotope_plus/templates',
	'mod_orderReport_details'         => 'system/modules/isotope_plus/templates',
	'mod_orderReport'                 => 'system/modules/isotope_plus/templates',
	'isotope_download_from_attribute' => 'system/modules/isotope_plus/templates/downloads',
));
