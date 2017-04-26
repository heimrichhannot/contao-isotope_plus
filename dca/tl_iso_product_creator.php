<?php

$GLOBALS['TL_DCA']['tl_iso_product_creator'] = array(
    // Config
    'config' => array(
        'dataContainer'    => 'Table',
        'enableVersioning' => false,
        'sql'              => array(
            'keys' => array(
                'id' => 'primary',
            ),
        ),
        'onsubmit_callback' => array(
            array('HeimrichHannot\IsotopePlus\Callbacks', 'createProductsFromFrontendCreator')
        ),
    ),

    // Fields
    'fields' => array(
        'id'              => array(
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ),
        'tstamp'          => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'type'            => array(
            'sql' => "varchar(32) NOT NULL default ''",
        ),
        'author'          => array(
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'createdProducts' => array(
            'sql' => "blob NULL",
        ),
        'uploadedFiles'   => array(
            'label'     => &$GLOBALS['TL_LANG']['tl_iso_product_creator']['uploadedFiles'],
            'exclude'   => true,
            'inputType' => TL_MODE == 'BE' ? 'fileTree' : 'multifileupload',
            'eval'      => array(
                'tl_class'           => 'clr',
                'extensions'         => \Config::get('validImageTypes'),
                'filesOnly'          => true,
                'fieldType'          => 'checkbox',
                'maxImageWidth'      => \Config::get('gdMaxImgWidth'),
                'maxImageHeight'     => \Config::get('gdMaxImgHeight'),
                'skipPrepareForSave' => true,
                'uploadFolder'       => array('HeimrichHannot\IsotopePlus\Callbacks', 'getUploadFolder'),
                'addRemoveLinks'     => true,
                'multipleFiles'      => 25,
                'maxUploadSize'      => \Config::get('maxFileSize'),
            ),
            'sql'       => "blob NULL",
        ),
        'errors'          => array(
            'sql' => "blob NULL",
        ),
    ),
);

class tl_iso_product_creation
{
}