<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_files'];

$arrDca['fields']['addedBy'] = [
	'label'      => &$GLOBALS['TL_LANG']['tl_files']['addedBy'],
	'inputType'  => 'select',
	'exclude'    => true,
	'search'     => true,
	'default'    => FE_USER_LOGGED_IN ? FrontendUser::getInstance()->id : '',
	'foreignKey' => 'tl_member.username',
	'eval'       => ['mandatory' => true, 'tl_class' => 'w50'],
	'sql'        => "int(10) unsigned NOT NULL default '0'",
];

$arrDca['fields']['licence'] = [
	'label'            => &$GLOBALS['TL_LANG']['tl_files']['licence'],
	'inputType'        => 'select',
	'exclude'          => true,
	'search'           => true,
	'options_callback' => ['HeimrichHannot\IsotopePlus\ProductHelper', 'getLicenceTitle'],
	'eval'             => ['tl_class' => 'w50'],
	'sql'              => "varchar(25) NOT NULL default ''",
];


