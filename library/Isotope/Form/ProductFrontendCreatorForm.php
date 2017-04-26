<?php

namespace Isotope\Form;

use HeimrichHannot\FormHybrid\Form;

class ProductFrontendCreatorForm extends Form
{
    protected $strMethod   = FORMHYBRID_METHOD_POST;
    protected $strTable    = 'tl_iso_product_creator';

    protected $strTemplate = 'iso_product_creator';

    public function __construct($objModule = null, $instanceId = 0)
    {
        parent::__construct($objModule, $instanceId);
    }

    protected function compile() {}

    public function modifyDC(&$arrDca = null)
    {
//        $arrDca['config']['onsubmit_callback'][] = array('HeimrichHannot\\IsotopePlus\\ProductFrontendEditorHelper', 'setFieldValuesFromModule');
//        $arrDca['config']['onsubmit_callback'][] = array('HeimrichHannot\\IsotopePlus\\ProductFrontendEditorHelper', 'setFieldValues');
    }
}

