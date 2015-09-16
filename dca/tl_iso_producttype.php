<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_iso_producttype'];

/**
 * Palettes
 */
$arrDca['palettes']['standard'] = str_replace('{description_legend:hide}', '{email_legend},orderNotification;{description_legend:hide}', $arrDca['palettes']['standard']);

/**
 * Fields
 */
$arrDca['fields']['orderNotification'] = array
(
	'label'                 => &$GLOBALS['TL_LANG']['tl_iso_producttype']['orderNotification'],
	'exclude'               => true,
	'inputType'             => 'select',
	'options_callback'      => array('NotificationCenter\tl_module', 'getNotificationChoices'),
	'eval'                  => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
	'sql'                   => "int(10) unsigned NOT NULL default '0'"
);