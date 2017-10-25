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


$arrDca['palettes']['__selector__'][] = 'iso_useFieldsForTags';
$arrDca['palettes']['__selector__'][] = 'iso_addImageSizes';

/**
 * Palettes
 */

$arrDca['palettes']['iso_productfilterplus'] =
	'{title_legend},name,headline,type;{config_legend},iso_category_scope,iso_list_where,iso_enableLimit,iso_filterFields,iso_filterHideSingle,iso_searchFields,iso_searchAutocomplete,iso_sortingFields,iso_listingSortField,iso_listingSortDirection;{template_legend},customTpl,iso_filterTpl,iso_includeMessages,iso_hide_list;{redirect_legend},jumpTo;{reference_legend:hide},defineRoot;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrDca['palettes']['iso_productlistplus'] =
	'{title_legend},name,headline,type;{config_legend},iso_description,numberOfItems,perPage,iso_category_scope,iso_list_where,iso_filterModules,iso_price_filter,iso_newFilter,iso_producttype_filter,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_addProductJumpTo,iso_jump_first;{reference_legend:hide},defineRoot;{template_legend:hide},customTpl,iso_list_layout,iso_gallery,iso_cols,iso_use_quantity,iso_hide_list,iso_includeMessages,iso_emptyMessage,iso_emptyFilter,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrDca['palettes']['iso_productlist'] = str_replace('{config_legend}', '{config_legend},iso_description', $arrDca['palettes']['iso_productlist']);

$arrDca['palettes']['iso_productreader'] = str_replace(
	'iso_buttons;',
	'iso_buttons;{creator_legend},addEditCol,addDeleteCol,addPublishCol,addCreateButton;{bookings_legend},bp_months;',
	$arrDca['palettes']['iso_productreader']
);


$arrDca['palettes']['iso_cart_link'] =
	'{title_legend},name,headline,type;{config_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrDca['palettes']['iso_direct_checkout'] = '{title_legend},name,headline,type;'
											 . '{config_legend},jumpTo,formHybridAsync,formHybridResetAfterSubmission,iso_direct_checkout_product_mode,iso_direct_checkout_products,nc_notification,iso_shipping_modules,iso_use_notes;'
											 . '{template_legend},formHybridTemplate;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrDca['palettes']['iso_product_ranking'] =
	'{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$arrDca['palettes']['iso_orderhistory_plus'] =
	str_replace('iso_config_ids', 'iso_config_ids,iso_show_all_orders', $arrDca['palettes']['iso_orderhistory']);
$arrDca['palettes']['iso_orderdetails_plus'] =
	str_replace('iso_loginRequired', 'iso_loginRequired,iso_show_all_orders', $arrDca['palettes']['iso_orderdetails']);

$arrDca['palettes']['iso_product_frontend_editor'] = '{title_legend},name,headline,type;'
													  . '{creator_legend},formHybridDataContainer,formHybridPalette,formHybridEditable,formHybridAddEditableRequired,formHybridAddReadOnly,formHybridAddPermanentFields;'
													  . '{product_legend},iso_editableCategories,iso_productCategory,iso_exifMapping,formHybridAddDefaultValues,iso_useFieldsForTags,iso_tagFields,iso_addImageSizes,iso_useUploadsAsDownload,iso_creatorFallbackUser,iso_uploadFolder;'
													  . '{action_legend},formHybridAllowIdAsGetParameter,noIdBehavior,disableSessionCheck,disableAuthorCheck,addUpdateConditions,allowDelete,formHybridAsync,deactivateTokens;'
													  . '{misc_legend},formHybridSuccessMessage,formHybridSkipScrollingToSuccessMessage,formHybridCustomSubmit,setPageTitle,addClientsideValidation;'
													  . '{template_legend},formHybridTemplate,modalTpl,customTpl;'
													  . '{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


$arrDca['subpalettes']['iso_useFieldsForTags'] = 'iso_tagField';
$arrDca['subpalettes']['iso_addImageSizes']    = 'iso_imageSizes';

if (in_array('slick', \ModuleLoader::getActive())) {
	$arrDca['palettes']['iso_productlistslick'] =
		str_replace('iso_description', 'slickConfig,iso_description', $arrDca['palettes']['iso_productlistplus']);
}

/**
 * Callbacks
 */
$arrDca['config']['onload_callback'][] = ['tl_module_isotope_plus', 'modifyPalette'];

/**
 * Fields
 */
$arrDca['fields']['iso_productCategory'] = [
	'label'      => &$GLOBALS['TL_LANG']['tl_module']['iso_productCategory'],
	'inputType'  => 'pageTree',
	'foreignKey' => 'tl_page.title',
	'eval'       => [
		'doNotSaveEmpty' => true,
		'multiple'       => true,
		'fieldType'      => 'checkbox',
		'orderField'     => 'orderPages',
		'tl_class'       => 'clr hide_sort_hint'
	],
	'relation'   => ['type' => 'hasMany', 'load' => 'lazy'],
	'sql'        => "blob NULL",
];

$arrDca['fields']['iso_exifMapping'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_exifMapping'],
	'inputType' => 'multiColumnEditor',
	'eval'      => [
		'tl_class'          => 'clr',
		'multiColumnEditor' => [
			'class'       => 'exif-map',
			'minRowCount' => 0,
			'maxRowCount' => 50, // 26 possible tags from php-exif (v0.6.3)
			'fields'      => [
				'exifTag'    => [
					'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_exifMapping']['iso_exifMapping_exifTag'],
					'inputType'        => 'select',
					'options_callback' => ['HeimrichHannot\Haste\Image\Image', 'getExifTagsAsOptions'],
					'eval'             => ['chosen' => true, 'mandatory' => true, 'style' => 'width:200px', 'includeBlankOption' => true],
				],
				'customTag'  => [
					'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_exifMapping']['iso_exifMapping_customTag'],
					'inputType' => 'text',
					'eval'      => ['maxLength' => 100, 'style' => 'width:200px'],
				],
				'tableField' => [
					'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_exifMapping']['iso_exifMapping_tableField'],
					'inputType'        => 'select',
					'options_callback' => ['HeimrichHannot\IsotopePlus\Callbacks', 'getProductTableFieldsAsOptions'],
					'eval'             => ['chosen' => true, 'mandatory' => true, 'style' => 'width:300px', 'includeBlankOption' => true],
				],
			],
		],
	],
	'sql'       => "blob NULL",
];

$arrDca['fields']['iso_filterTpl'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_filterTpl'],
	'exclude'          => true,
	'default'          => 'iso_filter_default',
	'inputType'        => 'select',
	'options_callback' => ['Isotope\Backend\Module\Callback', 'getFilterTemplates'],
	'eval'             => ['mandatory' => true, 'tl_class' => 'w50', 'chosen' => true],
	'sql'              => "varchar(64) NOT NULL default ''",
];

$arrDca['fields']['iso_hide_list'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_hide_list'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['tl_class' => 'w50'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_show_all_orders'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_show_all_orders'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['tl_class' => 'w50'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_category_scope'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope'],
	'exclude'   => true,
	'inputType' => 'radio',
	'default'   => 'current_category',
	'options'   => [
		'current_category',
		'current_and_first_child',
		'current_and_all_children',
		'parent',
		'product',
		'article',
		'global',
	],
	'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref'],
	'eval'      => ['tl_class' => 'clr w50 w50h', 'helpwizard' => true],
	'sql'       => "varchar(64) NOT NULL default ''",
];

$arrDca['fields']['iso_list_where'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_list_where'],
	'exclude'   => true,
	'inputType' => 'text',
	'eval'      => ['preserveTags' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
	'sql'       => "varchar(255) NOT NULL default ''",
];

$arrDca['fields']['iso_filterFields'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_filterFields'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'options_callback' => ['Isotope\Backend\Module\Callback', 'getFilterFields'],
	'eval'             => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
	'sql'              => "blob NULL",
];

$arrDca['fields']['iso_filterHideSingle'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_filterHideSingle'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['tl_class' => 'w50 m12'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_searchFields'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_searchFields'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'options_callback' => ['Isotope\Backend\Module\Callback', 'getSearchFields'],
	'eval'             => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
	'sql'              => "blob NULL",
];

$arrDca['fields']['iso_searchAutocomplete'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_searchAutocomplete'],
	'exclude'          => true,
	'inputType'        => 'select',
	'options_callback' => ['Isotope\Backend\Module\Callback', 'getAutocompleteFields'],
	'eval'             => ['tl_class' => 'w50', 'includeBlankOption' => true],
	'sql'              => "varchar(255) NOT NULL default ''",
];

$arrDca['fields']['iso_sortingFields'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_sortingFields'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'options_callback' => ['Isotope\Backend\Module\Callback', 'getSortingFields'],
	'eval'             => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
	'sql'              => "blob NULL",
];

$arrDca['fields']['iso_enableLimit'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_enableLimit'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr w50 m12'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_listingSortField'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_listingSortField'],
	'exclude'          => true,
	'inputType'        => 'select',
	'options_callback' => ['Isotope\Backend\Module\Callback', 'getSortingFields'],
	'eval'             => ['includeBlankOption' => true, 'tl_class' => 'clr w50'],
	'sql'              => "varchar(255) NOT NULL default ''",
	'save_callback'    => [
		['Isotope\Backend', 'truncateProductCache'],
	],
];

$arrDca['fields']['iso_listingSortDirection'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_listingSortDirection'],
	'exclude'   => true,
	'default'   => 'DESC',
	'inputType' => 'select',
	'options'   => ['DESC', 'ASC'],
	'reference' => &$GLOBALS['TL_LANG']['tl_module']['sortingDirection'],
	'eval'      => ['tl_class' => 'w50'],
	'sql'       => "varchar(8) NOT NULL default ''",
];

$arrDca['fields']['iso_includeMessages'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_includeMessages'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['doNotCopy' => true, 'tl_class' => 'w50'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_perPage'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_perPage'],
	'exclude'   => true,
	'default'   => '8,12,32,64',
	'inputType' => 'text',
	'eval'      => ['mandatory' => true, 'maxlength' => 64, 'rgxp' => 'extnd', 'tl_class' => 'w50'],
	'sql'       => "varchar(64) NOT NULL default ''",
];

$arrDca['fields']['iso_filterModules'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_filterModules'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'foreignKey'       => 'tl_module.name',
	'options_callback' => ['Isotope\Backend\Module\Callback', 'getFilterModules'],
	'eval'             => ['multiple' => true, 'tl_class' => 'clr w50 w50h'],
	'sql'              => "blob NULL",
	'relation'         => ['type' => 'hasMany', 'load' => 'lazy'],
];

$arrDca['fields']['iso_newFilter'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_newFilter'],
	'exclude'   => true,
	'inputType' => 'select',
	'default'   => 'show_all',
	'options'   => ['show_all', 'show_new', 'show_old'],
	'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_newFilter'],
	'eval'      => ['tl_class' => 'w50'],
	'sql'       => "varchar(8) NOT NULL default ''",
];

$arrDca['fields']['iso_price_filter'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_price_filter'],
	'exclude'   => true,
	'inputType' => 'select',
	'options'   => ['paid', 'free'],
	'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_price_filter'],
	'eval'      => ['tl_class' => 'w50 clr', 'includeBlankOption' => true],
	'sql'       => "varchar(64) NOT NULL default ''",
];

$arrDca['fields']['iso_producttype_filter'] = [
	'label'      => &$GLOBALS['TL_LANG']['tl_module']['iso_producttype_filter'],
	'exclude'    => true,
	'inputType'  => 'select',
	'foreignKey' => 'tl_iso_producttype.name',
	'eval'       => ['tl_class' => 'clr', 'multiple' => true, 'chosen' => true, 'style' => 'width: 100%'],
	'sql'        => "blob NULL",
];

$arrDca['fields']['iso_addProductJumpTo'] = [
	'label'       => &$GLOBALS['TL_LANG']['tl_module']['iso_addProductJumpTo'],
	'exclude'     => true,
	'inputType'   => 'pageTree',
	'foreignKey'  => 'tl_page.title',
	'eval'        => ['fieldType' => 'radio', 'tl_class' => 'clr'],
	'explanation' => 'jumpTo',
	'sql'         => "int(10) unsigned NOT NULL default '0'",
	'relation'    => ['type' => 'hasOne', 'load' => 'lazy'],
];

$arrDca['fields']['iso_jump_first'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_jump_first'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['tl_class' => 'w50'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_list_layout'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_list_layout'],
	'exclude'          => true,
	'inputType'        => 'select',
	'options_callback' => function (\DataContainer $arrDca) {
		return \Isotope\Backend::getTemplates('iso_list_');
	},
	'eval'             => ['includeBlankOption' => true, 'tl_class' => 'w50', 'chosen' => true],
	'sql'              => "varchar(64) NOT NULL default ''",
];

$arrDca['fields']['iso_gallery'] = [
	'label'      => &$GLOBALS['TL_LANG']['tl_module']['iso_gallery'],
	'exclude'    => true,
	'inputType'  => 'select',
	'foreignKey' => \Isotope\Model\Gallery::getTable() . '.name',
	'eval'       => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
	'sql'        => "int(10) unsigned NOT NULL default '0'",
];

$arrDca['fields']['iso_cols'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_cols'],
	'exclude'   => true,
	'default'   => 1,
	'inputType' => 'text',
	'eval'      => ['maxlength' => 1, 'rgxp' => 'digit', 'tl_class' => 'w50'],
	'sql'       => "int(1) unsigned NOT NULL default '1'",
];

$arrDca['fields']['iso_use_quantity'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_use_quantity'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['tl_class' => 'w50'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_emptyMessage'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_emptyMessage'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_emptyFilter'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_emptyFilter'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_buttons'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_buttons'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'default'          => ['add_to_cart'],
	'options_callback' => ['Isotope\Backend\Module\Callback', 'getButtons'],
	'eval'             => ['multiple' => true, 'tl_class' => 'clr'],
	'sql'              => "blob NULL",
];

$arrDca['fields']['iso_description'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_description'],
	'exclude'   => true,
	'search'    => true,
	'inputType' => 'textarea',
	'eval'      => ['rte' => 'tinyMCE', 'tl_class' => 'clr'],
	'sql'       => "text NULL",
];

$arrDca['fields']['iso_direct_checkout_product_mode'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_product_mode'],
	'exclude'   => true,
	'inputType' => 'select',
	'options'   => ['product', 'product_type'],
	'default'   => 'product',
	'reference' => &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_product_mode'],
	'eval'      => ['mandatory' => true, 'tl_class' => 'w50 clr', 'submitOnChange' => true],
	'sql'       => "varchar(64) NOT NULL default ''",
];

$arrDca['fields']['iso_direct_checkout_products'] = [
	'label'        => &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_products'],
	'exclude'      => true,
	'inputType'    => 'fieldpalette',
	'foreignKey'   => 'tl_fieldpalette.id',
	'relation'     => ['type' => 'hasMany', 'load' => 'eager'],
	'sql'          => "blob NULL",
	'eval'         => ['tl_class' => 'clr'],
	'fieldpalette' => [
		'config'   => [
			'hidePublished' => true,
		],
		'list'     => [
			'label' => [
				'fields' => ['iso_direct_checkout_product'],
				'format' => '%s',
			],
		],
		'palettes' => [
			'default' => 'iso_direct_checkout_product,iso_use_quantity',
		],
		'fields'   => [
			'iso_direct_checkout_product' => [
				'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_product'],
				'exclude'          => true,
				'inputType'        => 'select',
				'options_callback' => ['tl_module_isotope_plus', 'getProducts'],
				'eval'             => [
					'mandatory'          => true,
					'tl_class'           => 'long clr',
					'style'              => 'width: 97%',
					'chosen'             => true,
					'includeBlankOption' => true,
				],
				'sql'              => "int(10) unsigned NOT NULL default '0'",
			],
			'iso_use_quantity'            => $arrDca['fields']['iso_use_quantity'],
		],
	],
];

$arrDca['fields']['iso_direct_checkout_product_types'] = [
	'label'        => &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_product_types'],
	'exclude'      => true,
	'inputType'    => 'fieldpalette',
	'foreignKey'   => 'tl_fieldpalette.id',
	'relation'     => ['type' => 'hasMany', 'load' => 'eager'],
	'sql'          => "blob NULL",
	'eval'         => ['tl_class' => 'clr'],
	'fieldpalette' => [
		'config'   => [
			'hidePublished' => true,
		],
		'list'     => [
			'label' => [
				'fields' => ['iso_direct_checkout_product_type'],
				'format' => '%s',
			],
		],
		'palettes' => [
			'default' => 'iso_direct_checkout_product_type,iso_use_quantity',
		],
		'fields'   => [
			'iso_direct_checkout_product_type' => [
				'label'      => &$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_product_type'],
				'exclude'    => true,
				'inputType'  => 'select',
				'foreignKey' => 'tl_iso_producttype.name',
				'eval'       => [
					'mandatory'          => true,
					'tl_class'           => 'long clr',
					'style'              => 'width: 97%',
					'chosen'             => true,
					'includeBlankOption' => true,
				],
				'sql'        => "int(10) unsigned NOT NULL default '0'",
			],
			'iso_use_quantity'                 => $arrDca['fields']['iso_use_quantity'],
		],
	],
];

$arrDca['fields']['iso_use_notes'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_use_notes'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_useFieldsForTags'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_useFieldsForTags'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_tagField']  = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_useFieldsForTags'],
	'exclude'          => true,
	'inputType'        => 'select',
	'options_callback' => ['tl_module_isotope_plus', 'getTagFields'],
	'eval'             => ['tl_class' => 'clr'],
	'sql'              => "varchar(32) NOT NULL default ''",
];
$arrDca['fields']['iso_tagFields'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_tagFields'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'options_callback' => ['HeimrichHannot\FormHybrid\Backend\Module', 'getEditable'],
	'eval'             => ['multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight clr'],
	'sql'              => "blob NULL"
];

$arrDca['fields']['iso_editableCategories'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['iso_editableCategories'],
	'exclude'          => true,
	'inputType'        => 'checkboxWizard',
	'options_callback' => ['Isotope\Backend\ProductType\Callback', 'getOptions'],
	'eval'             => ['mandatory' => true, 'multiple' => true, 'includeBlankOption' => true, 'tl_class' => 'w50 autoheight clr'],
	'sql'              => "blob NULL"
];

$arrDca['fields']['iso_addImageSizes'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_addImageSizes'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_imageSizes'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_imageSizes'],
	'exclude'   => true,
	'inputType' => 'multiColumnWizard',
	'eval'      => [
		'columnFields' => [
			'size' => [
				'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_imageSizes']['size'],
				'exclude'   => true,
				'inputType' => 'imageSize',
				'options'   => System::getImageSizes(),
				'reference' => &$GLOBALS['TL_LANG']['MSC'],
				'eval'      => [
					'style' => 'width: 100px;'
				]
			],
			'name' => [
				'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_sizeName'],
				'exclude'   => true,
				'inputType' => 'text',
				'eval'      => ['style' => 'width: 100px;'],
				'sql'       => "varchar(32) NOT NULL default ''",
			]
		],
		'tl_class'     => 'clr long',
	],
	'sql'       => "blob NULL",
];

$arrDca['fields']['iso_useUploadsAsDownload'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_useUploadsAsDownload'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => ['tl_class' => 'clr'],
	'sql'       => "char(1) NOT NULL default ''",
];

$arrDca['fields']['iso_uploadFolder'] = [
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['iso_uploadFolder'],
	'exclude'   => true,
	'inputType' => 'fileTree',
	'eval'      => ['fieldType' => 'radio', 'tl_class' => 'clr w50'],
	'sql'       => "binary(16) NULL",
];


class tl_module_isotope_plus
{
	public static function getEditable($objDc)
	{
		return \HeimrichHannot\FormHybrid\FormHelper::getEditableFields($objDc->activeRecord->formHybridDataContainer);
	}
	
	public function modifyPalette($objDc)
	{
		$objModule = \ModuleModel::findByPk(\Input::get('id'));
		$arrDca    = &$GLOBALS['TL_DCA']['tl_module'];
		
		switch ($objModule->type) {
			case 'iso_direct_checkout':
				if ($objModule->iso_direct_checkout_product_mode == 'product_type') {
					$arrDca['palettes']['iso_direct_checkout'] = str_replace(
						'iso_direct_checkout_products,',
						'iso_direct_checkout_product_types,iso_listingSortField,iso_listingSortDirection,',
						$arrDca['palettes']['iso_direct_checkout']
					);
					
					// fix field labels
					$arrDca['fields']['iso_listingSortField']['label']     = &
						$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_listingSortField'];
					$arrDca['fields']['iso_listingSortDirection']['label'] = &
						$GLOBALS['TL_LANG']['tl_module']['iso_direct_checkout_listingSortDirection'];
				}
				
				$arrDca['fields']['iso_shipping_modules']['inputType']                  = 'select';
				$arrDca['fields']['iso_shipping_modules']['eval']['includeBlankOption'] = true;
				$arrDca['fields']['iso_shipping_modules']['eval']['multiple']           = false;
				$arrDca['fields']['iso_shipping_modules']['eval']['tl_class']           = 'w50';
				
				$arrDca['fields']['formHybridTemplate']['default'] = 'formhybrid_direct_checkout';
				break;
			
			case 'iso_product_frontend_creator' :
				$arrDca['fields']['formHybridDefaultValues']['eval']['columnFields']['field']['options_callback'] =
					['HeimrichHannot\IsotopePlus\Callbacks', 'getDefaultValueFields'];
		}
	}
	
	public static function getProducts()
	{
		$objProducts = \Isotope\Model\Product::findPublished();
		
		$arrProductTypeLabels = [];
		$arrProducts          = [];
		
		while ($objProducts->next()) {
			// check for label cache
			if (isset($arrProductTypeLabels[$objProducts->type])) {
				$strProductTypeLabel = $arrProductTypeLabels[$objProducts->type];
			} else {
				if (($objProductType = \Isotope\Model\ProductType::findByPk($objProducts->type)) !== null) {
					$strProductTypeLabel                       = $objProductType->name;
					$arrProductTypeLabels[$objProductType->id] = $objProductType->name;
				}
			}
			
			$arrProducts[$objProducts->id] = $strProductTypeLabel . ' - ' . $objProducts->name;
		}
		
		asort($arrProducts);
		
		return $arrProducts;
	}
	
	public function getTagFields(\DataContainer $dc)
	{
		$names   = \Contao\Database::getInstance()->getFieldNames('tl_iso_product');
		$arrTags = [];
		
		foreach ($names as $name) {
			if ($GLOBALS['TL_DCA']['tl_iso_product']['fields'][$name]['inputType'] == 'tagsinput') {
				$arrTags[] = $name;
			}
		}
		
		return $arrTags;
	}
	
}
