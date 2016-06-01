<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_iso_config'];

/**
 * Palettes
 */
$arrDca['palettes']['default'] = str_replace('{analytics_legend}', '{stock_legend},skipSets,skipStockValidation,skipStockEdit,skipExemptionFromShippingWhenStockEmpty,stockIncreaseOrderStates;{analytics_legend}', $arrDca['palettes']['default']);

$arrDca['fields']['skipStockValidation'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_config']['skipStockValidation'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['skipStockEdit'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_config']['skipStockEdit'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['skipExemptionFromShippingWhenStockEmpty'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_config']['skipExemptionFromShippingWhenStockEmpty'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['skipSets'] = array
(
	'label'     => &$GLOBALS['TL_LANG']['tl_iso_config']['skipSets'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => 'w50'),
	'sql'       => "char(1) NOT NULL default ''",
);

$arrDca['fields']['stockIncreaseOrderStates'] = array
(
	'label'                 => &$GLOBALS['TL_LANG']['tl_iso_config']['stockIncreaseOrderStates'],
	'exclude'               => true,
	'inputType'             => 'select',
	'options_callback'      => array('tl_iso_config_isotope_plus', 'getOrderStates'),
	'eval'                  => array('chosen' => true, 'multiple' => true, 'tl_class' => 'w50'),
	'sql'                   => "blob NULL",
);

class tl_iso_config_isotope_plus {

	public static function getOrderStates() {
		$arrOptions = array();

		if (($objOrderStatus = \Isotope\Model\OrderStatus::findAll()) !== null)
		{
			while ($objOrderStatus->next())
			{
				$arrOptions[$objOrderStatus->id] = $objOrderStatus->name;
			}
		}

		return $arrOptions;
	}

}