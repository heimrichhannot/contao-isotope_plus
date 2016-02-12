<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_iso_product'];

/**
 * Labels in Backend
 */
$arrDca['list']['label']['fields'] = array('images', 'name', 'sku', 'price', 'stock', 'initialStock', 'jumpTo'); // added stock and initialstock to product overview

/**
 * Fields
 */
$arrDca['fields']['shipping_exempt']['attributes']['fe_filter'] = true;

$arrDca['fields']['initialStock'] = array
(
	'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product']['initialStock'],
	'inputType'             => 'text',
	'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50', 'rgxp' => 'digit'),
	'attributes'            => array('legend'=>'inventory_legend'),
	'sql'                   => "varchar(255) NOT NULL default ''",
);

$arrDca['fields']['stock'] = array
(
	'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product']['stock'],
	'inputType'             => 'text',
	'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50', 'rgxp' => 'digit'),
	'attributes'            => array('legend'=>'inventory_legend', 'fe_sorting'=>true),
	'sql'                   => "varchar(255) NOT NULL default ''",
);

$arrDca['fields']['setQuantity'] = array
(
	'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product']['setQuantity'],
	'inputType'             => 'text',
	'eval'                  => array('mandatory'=>true, 'tl_class'=>'w50', 'rgxp' => 'digit'),
	'attributes'            => array('legend'=>'inventory_legend', 'fe_sorting'=>true),
	'sql'                   => "varchar(255) NOT NULL default ''",
);

$arrDca['fields']['releaseDate'] = array
(
	'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product']['releaseDate'],
	'exclude'               => true,
	'inputType'             => 'text',
	'default'               => time(),
	'eval'                  => array('rgxp'=>'date', 'datepicker'=>true, 'tl_class'=>'w50 wizard'),
	'attributes'            => array('legend'=>'publish_legend', 'fe_sorting'=>true),
	'sql'                   => "varchar(10) NOT NULL default ''",
);

$arrDca['fields']['maxOrderSize'] = array
(
	'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product']['maxOrderSize'],
	'inputType'             => 'text',
	'eval'                  => array('tl_class'=>'w50', 'rgxp' => 'digit'),
	'attributes'            => array('legend'=>'inventory_legend'),
	'sql'                   => "varchar(255) NOT NULL default ''",
);

$arrDca['fields']['overrideStockShopConfig'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_product']['overrideStockShopConfig'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'attributes' => array('legend'=>'shipping_legend'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['jumpTo'] = array
(
	'label'      => &$GLOBALS['TL_LANG']['tl_iso_product']['jumpTo'],
	'exclude'    => true,
	'inputType'  => 'pageTree',
	'foreignKey' => 'tl_page.title',
	'eval'       => array('fieldType' => 'radio'),
	'sql'        => "int(10) unsigned NOT NULL default '0'",
	'attributes' => array('legend'=>'general_legend'),
	'relation'   => array('type' => 'belongsTo', 'load' => 'lazy')
);

\Controller::loadDataContainer('tl_iso_config');
\System::loadLanguageFile('tl_iso_config');
\Controller::loadDataContainer('tl_iso_producttype');
\System::loadLanguageFile('tl_iso_producttype');

// arrays are always copied by value (not by reference) in php
$arrDca['fields']['skipStockValidation'] = $GLOBALS['TL_DCA']['tl_iso_config']['fields']['skipStockValidation'];
$arrDca['fields']['skipStockValidation']['attributes'] = array('legend'=>'shipping_legend');
$arrDca['fields']['skipExemptionFromShippingWhenStockEmpty'] = $GLOBALS['TL_DCA']['tl_iso_config']['fields']['skipExemptionFromShippingWhenStockEmpty'];
$arrDca['fields']['skipExemptionFromShippingWhenStockEmpty']['attributes'] = array('legend'=>'shipping_legend');