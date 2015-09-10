<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_iso_product'];

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

$arrDca['fields']['sendEmailAfterOrder'] = array
(
	'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product']['sendEmailAfterOrder'],
	'exclude'               => true,
	'inputType'             => 'checkbox',
	'eval'                  => array('doNotCopy'=>true, 'tl_class'=>'w50'),
	'attributes'            => array('legend'=>'email_legend'),
	'sql'                   => "char(1) NOT NULL default ''"
);

$arrDca['fields']['emailAfterOrderRecipients'] = array
(
	'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product']['emailAfterOrderRecipients'],
	'inputType'             => 'text',
	'eval'                  => array('tl_class'=>'w50'),
	'attributes'            => array('legend'=>'email_legend'),
	'sql'                   => "varchar(255) NOT NULL default ''",
);