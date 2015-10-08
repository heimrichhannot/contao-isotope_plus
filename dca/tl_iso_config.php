<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_iso_config'];

/**
 * Palettes
 */
$arrDca['palettes']['default'] = str_replace('{analytics_legend}', '{stock_legend},skipSets,skipStockValidation,skipExemptionFromShippingWhenStockEmpty;{analytics_legend}', $arrDca['palettes']['default']);

$arrDca['fields']['skipStockValidation'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_config']['skipStockValidation'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['skipExemptionFromShippingWhenStockEmpty'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_config']['skipExemptionFromShippingWhenStockEmpty'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['skipSets'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_config']['skipSets'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);