<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_iso_download'];

/**
 * Palettes
 */
$arrDca['palettes']['default'] = '{title_legend},title;' . $arrDca['palettes']['default'];

/**
 * Fields
 */
$arrDca['fields']['title'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_download']['title'],
	'exclude'   => true,
	'search'    => true,
	'sorting'   => true,
	'flag'      => 1,
	'inputType' => 'text',
	'eval'      => array('maxlength' => 255),
	'sql'       => "varchar(255) NOT NULL default ''"
);