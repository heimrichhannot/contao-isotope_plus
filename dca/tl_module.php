<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


$arrDca = &$GLOBALS['TL_DCA']['tl_module'];


/**
 * Palettes
 */

$arrDca['palettes']['iso_productfilterplus']
	= '{title_legend},name,headline,type;{config_legend},iso_category_scope,iso_list_where,iso_enableLimit,iso_filterFields,iso_filterHideSingle,iso_searchFields,iso_searchAutocomplete,iso_sortingFields,iso_listingSortField,iso_listingSortDirection;{template_legend},customTpl,iso_filterTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo;{reference_legend:hide},defineRoot;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrDca['palettes']['iso_productlistplus']
	= '{title_legend},name,headline,type;{config_legend},numberOfItems,perPage,iso_category_scope,iso_list_where,iso_filterModules,iso_newFilter,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_addProductJumpTo,iso_jump_first;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_cols,iso_use_quantity,iso_hide_list,iso_includeMessages,iso_emptyMessage,iso_emptyFilter,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrDca['palettes']['iso_productlist'] = str_replace('{config_legend}', '{config_legend},description', $arrDca['palettes']['iso_productlist']);

$arrDca['palettes']['iso_productreader'] = str_replace(
	'iso_buttons;', 'iso_buttons;{bookings_legend},bp_months;', $arrDca['palettes']['iso_productreader']
);

if (in_array('slick', \ModuleLoader::getActive()))
{
	$arrDca['palettes']['iso_productlistslick'] = str_replace('description', 'slickConfig,description', $arrDca['palettes']['iso_productlist']);
}

/**
 * Fields
 */
$arrDca['fields']['iso_filterTpl'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_filterTpl'],
	'exclude'          => true,
	'default'          => 'iso_filter_default',
	'inputType'        => 'select',
	'options_callback' => array('Isotope\Backend\Module\Callback', 'getFilterTemplates'),
	'eval'             => array('mandatory' => true, 'tl_class' => 'w50', 'chosen' => true),
	'sql'              => "varchar(64) NOT NULL default ''",
);

$arrDca['fields']['iso_hide_list'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_hide_list'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['iso_category_scope'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope'],
	'exclude'   => true,
	'inputType' => 'radio',
	'default'   => 'current_category',
	'options'   => array('current_category', 'current_and_first_child', 'current_and_all_children', 'parent', 'product',
						 'article', 'global'),
	'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref'],
	'eval'      => array('tl_class' => 'clr w50 w50h', 'helpwizard' => true),
	'sql'       => "varchar(64) NOT NULL default ''",
);

$arrDca['fields']['iso_list_where'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_list_where'],
	'exclude'   => true,
	'inputType' => 'text',
	'eval'      => array('preserveTags' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
	'sql'       => "varchar(255) NOT NULL default ''",
);

$arrDca['fields']['iso_filterFields'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_filterFields'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'options_callback' => array('Isotope\Backend\Module\Callback', 'getFilterFields'),
	'eval'             => array('multiple' => true, 'tl_class' => 'clr w50 w50h'),
	'sql'              => "blob NULL",
);

$arrDca['fields']['iso_filterHideSingle'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_filterHideSingle'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50 m12'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['iso_searchFields'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_searchFields'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'options_callback' => array('Isotope\Backend\Module\Callback', 'getSearchFields'),
	'eval'             => array('multiple' => true, 'tl_class' => 'clr w50 w50h'),
	'sql'              => "blob NULL",
);

$arrDca['fields']['iso_searchAutocomplete'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_searchAutocomplete'],
	'exclude'          => true,
	'inputType'        => 'select',
	'options_callback' => array('Isotope\Backend\Module\Callback', 'getAutocompleteFields'),
	'eval'             => array('tl_class' => 'w50', 'includeBlankOption' => true),
	'sql'              => "varchar(255) NOT NULL default ''",
);

$arrDca['fields']['iso_sortingFields'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_sortingFields'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'options_callback' => array('Isotope\Backend\Module\Callback', 'getSortingFields'),
	'eval'             => array('multiple' => true, 'tl_class' => 'clr w50 w50h'),
	'sql'              => "blob NULL",
);

$arrDca['fields']['iso_enableLimit'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_enableLimit'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('submitOnChange' => true, 'tl_class' => 'clr w50 m12'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['iso_listingSortField'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_listingSortField'],
	'exclude'          => true,
	'inputType'        => 'select',
	'options_callback' => array('Isotope\Backend\Module\Callback', 'getSortingFields'),
	'eval'             => array('includeBlankOption' => true, 'tl_class' => 'clr w50'),
	'sql'              => "varchar(255) NOT NULL default ''",
	'save_callback'    => array
	(
		array('Isotope\Backend', 'truncateProductCache'),
	),
);

$arrDca['fields']['iso_listingSortDirection'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_listingSortDirection'],
	'exclude'   => true,
	'default'   => 'DESC',
	'inputType' => 'select',
	'options'   => array('DESC', 'ASC'),
	'reference' => &$GLOBALS['TL_LANG']['tl_module']['sortingDirection'],
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "varchar(8) NOT NULL default ''",
);

$arrDca['fields']['iso_includeMessages'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_includeMessages'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('doNotCopy' => true, 'tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['iso_perPage'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_perPage'],
	'exclude'   => true,
	'default'   => '8,12,32,64',
	'inputType' => 'text',
	'eval'      => array('mandatory' => true, 'maxlength' => 64, 'rgxp' => 'extnd', 'tl_class' => 'w50'),
	'sql'       => "varchar(64) NOT NULL default ''",
);

$arrDca['fields']['iso_filterModules'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_filterModules'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'foreignKey'       => 'tl_module.name',
	'options_callback' => array('Isotope\Backend\Module\Callback', 'getFilterModules'),
	'eval'             => array('multiple' => true, 'tl_class' => 'clr w50 w50h'),
	'sql'              => "blob NULL",
	'relation'         => array('type' => 'hasMany', 'load' => 'lazy'),
);

$arrDca['fields']['iso_newFilter'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_newFilter'],
	'exclude'   => true,
	'inputType' => 'select',
	'default'   => 'show_all',
	'options'   => array('show_all', 'show_new', 'show_old'),
	'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_newFilter'],
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "varchar(8) NOT NULL default ''"
);

$arrDca['fields']['iso_addProductJumpTo'] = array
(
	'label'       => &$GLOBALS['TL_LANG']['tl_module']['iso_addProductJumpTo'],
	'exclude'     => true,
	'inputType'   => 'pageTree',
	'foreignKey'  => 'tl_page.title',
	'eval'        => array('fieldType' => 'radio', 'tl_class' => 'clr'),
	'explanation' => 'jumpTo',
	'sql'         => "int(10) unsigned NOT NULL default '0'",
	'relation'    => array('type' => 'hasOne', 'load' => 'lazy'),
);

$arrDca['fields']['iso_jump_first'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_jump_first'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['iso_list_layout'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_list_layout'],
	'exclude'          => true,
	'inputType'        => 'select',
	'options_callback' => function (\DataContainer $arrDca) {
		return \Isotope\Backend::getTemplates('iso_list_');
	},
	'eval'             => array('includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true),
	'sql'              => "varchar(64) NOT NULL default ''",
);

$arrDca['fields']['iso_gallery'] = array
(
	'label'      => &$GLOBALS['TL_LANG']['tl_module']['iso_gallery'],
	'exclude'    => true,
	'inputType'  => 'select',
	'foreignKey' => \Isotope\Model\Gallery::getTable() . '.name',
	'eval'       => array('includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'),
	'sql'        => "int(10) unsigned NOT NULL default '0'",
);

$arrDca['fields']['iso_cols'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_cols'],
	'exclude'   => true,
	'default'   => 1,
	'inputType' => 'text',
	'eval'      => array('maxlength' => 1, 'rgxp' => 'digit', 'tl_class' => 'w50'),
	'sql'       => "int(1) unsigned NOT NULL default '1'",
);

$arrDca['fields']['iso_use_quantity'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_use_quantity'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['iso_emptyMessage'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_emptyMessage'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('submitOnChange' => true, 'tl_class' => 'clr w50'),
	'sql'       => "char(1) NOT NULL default ''"
);

$arrDca['fields']['iso_emptyFilter'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_emptyFilter'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('submitOnChange' => true, 'tl_class' => 'clr'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['iso_buttons'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_buttons'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'default'          => array('add_to_cart'),
	'options_callback' => array('Isotope\Backend\Module\Callback', 'getButtons'),
	'eval'             => array('multiple' => true, 'tl_class' => 'clr'),
	'sql'              => "blob NULL",
);

$arrDca['fields']['description'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['description'],
	'exclude'   => true,
	'search'    => true,
	'inputType' => 'textarea',
	'eval'      => array('rte' => 'tinyMCE', 'tl_class' => 'clr'),
	'sql'       => "text NULL"
);