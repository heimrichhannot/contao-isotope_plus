<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_files'];

/**
 * Config
 */
$arrDca['config']['onload_callback'][] = array('tl_files_isotope_plus', 'modifyFolderPalette');


/**
 * Palettes
 */
$arrDca['palettes']['__selector__'][]        = 'addProductCreation';
$arrDca['subpalettes']['addProductCreation'] = 'name';


/**
 * Fields
 */
$arrDca['fields']['exif'] = array(
    'sql' => "blob NULL",
);

$arrDca['fields']['addProductCreation'] = array(
    'label'            => &$GLOBALS['TL_LANG']['tl_files']['addProductCreation'],
    'inputType'        => 'select',
    'options_callback' => array('tl_files_isotope_plus', 'getModulesAsOptions'),
    'eval'             => array(
        'includeBlankOption' => true,
        'tl_class'           => 'clr',
    ),
    'sql'              => "int(10) unsigned NOT NULL default '0'",
);


class tl_files_isotope_plus extends tl_files
{
    /**
     * Only show the product creation fields for folders
     *
     * @param DataContainer $dc
     */
    public function modifyFolderPalette(DataContainer $dc)
    {
        if (!$dc->id)
        {
            return;
        }

        if (is_dir(TL_ROOT . '/' . $dc->id))
        {
            $GLOBALS['TL_DCA'][$dc->table]['palettes'] = str_replace('protected', 'protected,addProductCreation', $GLOBALS['TL_DCA'][$dc->table]['palettes']);
        }
    }


    /**
     * Find all product creator modules
     *
     * @return array
     */
    public function getModulesAsOptions()
    {
        $arrReturn  = array();
        $objModules = ModuleModel::findByType('iso_product_frontend_creator');

        if ($objModules === null)
        {
            return $arrReturn;
        }

        foreach ($objModules as $objModule)
        {
            $arrReturn[$objModule->id] = $objModule->name;
        }

        return $arrReturn;
    }
}