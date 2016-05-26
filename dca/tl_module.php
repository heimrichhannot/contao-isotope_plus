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
	= '{title_legend},name,headline,type;{config_legend},iso_description,numberOfItems,perPage,iso_category_scope,iso_list_where,iso_filterModules,iso_price_filter,iso_newFilter,iso_producttype_filter,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_addProductJumpTo,iso_jump_first;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_cols,iso_use_quantity,iso_hide_list,iso_includeMessages,iso_emptyMessage,iso_emptyFilter,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrDca['palettes']['iso_productlist'] = str_replace('{config_legend}', '{config_legend},iso_description', $arrDca['palettes']['iso_productlist']);

$arrDca['palettes']['iso_productreader'] = str_replace(
	'iso_buttons;', 'iso_buttons;{bookings_legend},bp_months;', $arrDca['palettes']['iso_productreader']
);

$arrDca['palettes']['iso_cart_link']
	= '{title_legend},name,headline,type;{config_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrDca['palettes']['iso_direct_checkout']
	= '{title_legend},name,headline,type;{config_legend},jumpTo,iso_use_quantity,iso_direct_checkout_product_mode,iso_direct_checkout_product,nc_notification,iso_shipping_modules,iso_use_notes;{template_legend},formHybridTemplate;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrDca['palettes']['iso_product_ranking']
		= '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrDca['palettes']['iso_orderhistory_plus'] = str_replace('iso_config_ids', 'iso_config_ids,iso_show_all_orders', $arrDca['palettes']['iso_orderhistory']);
$arrDca['palettes']['iso_orderdetails_plus'] = str_replace('iso_loginRequired', 'iso_loginRequired,iso_show_all_orders', $arrDca['palettes']['iso_orderdetails']);


if (in_array('slick', \ModuleLoader::getActive()))
{
	$arrDca['palettes']['iso_productlistslick'] = str_replace('iso_description', 'slickConfig,iso_description', $arrDca['palettes']['iso_productlistplus']);
}

/**
 * Callbacks
 */
$arrDca['config']['onload_callback'][] = array('tl_module_isotope_plus', 'modifyPalette');

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

$arrDca['fields']['iso_show_all_orders'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_show_all_orders'],
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

$arrDca['fields']['iso_price_filter'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_price_filter'],
	'exclude'   => true,
	'inputType' => 'select',
	'options'   => array('paid', 'free'),
	'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_price_filter'],
	'eval'      => array('tl_class' => 'w50 clr', 'includeBlankOption' => true),
	'sql'       => "varchar(64) NOT NULL default ''"
);

$arrDca['fields']['iso_producttype_filter'] = array
(
		'label'      => &$GLOBALS['TL_LANG']['tl_module']['iso_producttype_filter'],
		'exclude'    => true,
		'inputType'  => 'select',
		'foreignKey' => 'tl_iso_producttype.name',
		'eval'       => array('tl_class' => 'clr', 'multiple' => true, 'chosen' => true, 'style' => 'width: 100%'),
		'sql'        => "blob NULL"
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

$arrDca['fields']['iso_description'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_description'],
	'exclude'   => true,
	'search'    => true,
	'inputType' => 'textarea',
	'eval'      => array('rte' => 'tinyMCE', 'tl_class' => 'clr'),
	'sql'       => "text NULL"
);

$arrDca['fields']['iso_direct_checkout_product_mode'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_product_mode'],
	'exclude'          => true,
	'inputType'        => 'select',
	'options'          => array('product', 'product_type'),
	'default'          => 'product',
	'reference'        => &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_product_mode'],
	'eval'             => array('mandatory' => true, 'tl_class' => 'w50 clr', 'submitOnChange' => true),
	'sql'              => "varchar(64) NOT NULL default ''",
);

$arrDca['fields']['iso_direct_checkout_product'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_product'],
	'exclude'          => true,
	'inputType'        => 'select',
	'options_callback' => array('tl_module_isotope_plus', 'getProducts'),
	'eval'             => array('mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true),
	'sql'              => "int(10) unsigned NOT NULL default '0'",
);

$arrDca['fields']['iso_direct_checkout_product_type'] = array
(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_product_type'],
	'exclude'          => true,
	'inputType'        => 'select',
	'foreignKey'       => 'tl_iso_producttype.name',
	'eval'             => array('mandatory' => true, 'tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true),
	'sql'              => "int(10) unsigned NOT NULL default '0'",
);



$arrDca['fields']['iso_use_notes'] = array
(
		'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_use_notes'],
		'exclude'   => true,
		'inputType' => 'checkbox',
		'eval'      => array('submitOnChange' => true, 'tl_class' => 'clr'),
		'sql'       => "char(1) NOT NULL default ''",
);

class tl_module_isotope_plus {

	public function modifyPalette($objDc)
	{
		$objModule = \ModuleModel::findByPk(\Input::get('id'));
		$arrDca = &$GLOBALS['TL_DCA']['tl_module'];

		switch ($objModule->type)
		{
			case 'iso_direct_checkout':
				if ($objModule->iso_direct_checkout_product_mode == 'product_type')
				{
					$arrDca['palettes']['iso_direct_checkout'] = str_replace(
						'iso_direct_checkout_product,', 'iso_direct_checkout_product_type,iso_listingSortField,iso_listingSortDirection,',
						$arrDca['palettes']['iso_direct_checkout']);

					// fix field labels
					$arrDca['fields']['iso_listingSortField']['label'] = &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_listingSortField'];
					$arrDca['fields']['iso_listingSortDirection']['label'] = &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_listingSortDirection'];
				}

				$arrDca['fields']['iso_shipping_modules']['inputType'] = 'select';
				$arrDca['fields']['iso_shipping_modules']['eval']['includeBlankOption'] = true;
				$arrDca['fields']['iso_shipping_modules']['eval']['multiple'] = false;
				$arrDca['fields']['iso_shipping_modules']['eval']['tl_class'] = 'w50';

				$arrDca['fields']['formHybridTemplate']['default'] = 'formhybrid_direct_checkout';
				break;
		}
	}

	public static function getProducts()
	{
		$objProducts = \Isotope\Model\Product::findPublished();

		$arrProductTypeLabels = array();
		$arrProducts = array();

		while ($objProducts->next())
		{
			// check for label cache
			if (isset($arrProductTypeLabels[$objProducts->type]))
			{
				$strProductTypeLabel = $arrProductTypeLabels[$objProducts->type];
			}
			else
			{
				if (($objProductType = \Isotope\Model\ProductType::findByPk($objProducts->type)) !== null)
				{
					$strProductTypeLabel = $objProductType->name;
					$arrProductTypeLabels[$objProductType->id] = $objProductType->name;
				}
			}

			$arrProducts[$objProducts->id] = $strProductTypeLabel . ' - ' . $objProducts->name;
		}

		asort($arrProducts);

		return $arrProducts;
	}

}