<?php

namespace Isotope\Form;

use HeimrichHannot\FormHybrid\Form;

class ProductFrontendEditorForm extends Form
{
    protected $strMethod   = FORMHYBRID_METHOD_POST;
    protected $arrProducts = array();
    protected $strTable    = 'tl_iso_product';

    public function __construct($objModule = null, $instanceId = 0)
    {
        $this->objCheckoutModule = $objModule;
        parent::__construct($objModule, $instanceId);
    }

    protected function compile()
    {
        if (empty($this->arrProducts))
        {
            $this->Template->error = $GLOBALS['TL_LANG']['MSC']['productNotFound'];
        }
    }

    public function modifyDC(&$arrDca = null)
    {
        // changed inputtype needed to display field in FE
        $arrDca['fields']['images']['inputType'] = 'text';
    }
}

