<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_iso_producttype'];

/**
 * Palettes
 */
$arrDca['palettes']['standard'] = str_replace(
	array('{description_legend:hide}', 'shipping_exempt'),
	array('{email_legend},sendOrderNotification;{description_legend:hide}', 'shipping_exempt,overrideStockShopConfig'),
	$arrDca['palettes']['standard']
);

$arrDca['palettes']['__selector__'][] = 'sendOrderNotification';
$arrDca['palettes']['__selector__'][] = 'overrideStockShopConfig';

/**
 * Subpalettes
 */
$arrDca['subpalettes']['sendOrderNotification'] = 'orderNotification,removeOtherProducts';
$arrDca['subpalettes']['overrideStockShopConfig'] = 'skipStockValidation,skipStockEdit,skipExemptionFromShippingWhenStockEmpty';

/**
 * Fields
 */
$arrDca['fields']['sendOrderNotification'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_producttype']['sendOrderNotification'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50', 'submitOnChange' => true),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['orderNotification'] = array
(
	'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['orderNotification'],
	'exclude'               => true,
	'inputType'             => 'select',
	'options_callback'      => array('NotificationCenter\tl_module', 'getNotificationChoices'),
	'eval'                  => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50', 'mandatory' => true),
	'sql'                   => "int(10) unsigned NOT NULL default '0'"
);

$arrDca['fields']['removeOtherProducts'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_producttype']['removeOtherProducts'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['overrideStockShopConfig'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_producttype']['overrideStockShopConfig'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50', 'submitOnChange' => true),
	'sql'       => "char(1) NOT NULL default ''",
);

\Controller::loadDataContainer('tl_iso_config');
\System::loadLanguageFile('tl_iso_config');

$arrDca['fields']['skipStockValidation'] = $GLOBALS['TL_DCA']['tl_iso_config']['fields']['skipStockValidation'];
$arrDca['fields']['skipStockEdit'] = $GLOBALS['TL_DCA']['tl_iso_config']['fields']['skipStockEdit'];
$arrDca['fields']['skipExemptionFromShippingWhenStockEmpty'] = $GLOBALS['TL_DCA']['tl_iso_config']['fields']['skipExemptionFromShippingWhenStockEmpty'];