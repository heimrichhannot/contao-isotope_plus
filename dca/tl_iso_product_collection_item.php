<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_iso_product_collection_item'];

/**
 * Fields
 */
$arrDca['fields']['setQuantity'] = array
(
	'label'                 => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['setQuantity'],
	'inputType'             => 'text',
	'eval'                  => array('tl_class'=>'w50', 'rgxp' => 'digit'),
	'sql'                   => "varchar(255) NOT NULL default ''",
);