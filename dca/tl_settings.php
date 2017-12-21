<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 19.10.17
 * Time: 15:48
 */

$arrDca = &$GLOBALS['TL_DCA']['tl_settings'];


$arrDca['fields']['iso_creatorFallbackMember'] = [
	'label'      => &$GLOBALS['TL_LANG']['tl_settings']['iso_creatorFallbackMember'],
	'exclude'    => true,
	'inputType'  => 'select',
	'foreignKey' => 'tl_member.username',
	'eval'       => ['tl_class' => 'clr', 'mandatory' => true, 'includeBlankOption' => true],
];


$arrDca['palettes']['default'] = str_replace(
	'shareExpirationInterval;',
	'shareExpirationInterval;{iso_product_editor_legend},iso_creatorFallbackMember;',
	$arrDca['palettes']['default']
);

