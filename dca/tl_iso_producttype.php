<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_iso_producttype'];

/**
 * Palettes
 */
$arrDca['palettes']['standard'] = str_replace('{description_legend:hide}', '{email_legend},sendOrderNotification;{description_legend:hide}', $arrDca['palettes']['standard']);
$arrDca['palettes']['__selector__'][] = 'sendOrderNotification';

/**
 * Subpalettes
 */
$arrDca['subpalettes']['sendOrderNotification'] = 'orderNotification,removeOtherProducts';

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