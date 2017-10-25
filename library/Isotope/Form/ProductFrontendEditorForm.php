<?php

namespace Isotope\Form;

use HeimrichHannot\FormHybrid\Form;
use HeimrichHannot\Request\Request;
use Isotope\Model\ProductModel;

class ProductFrontendEditorForm extends Form
{
	protected $strMethod = FORMHYBRID_METHOD_POST;
	protected $strTable  = 'tl_iso_product';
	
	protected $strTemplate = 'iso_product_creator';
	
	public function __construct($objModule = null, $instanceId = 0)
	{
		parent::__construct($objModule, $instanceId);
	}
	
	protected function compile()
	{
	}
	
	public function modifyDC(&$arrDca = null)
	{
		foreach (deserialize($this->objModule->formHybridEditable) as $field) {
			$this->dca['palettes']['default'] .= ',' . $field;
		}
		
		// limit upload to one image for editing existing product
		if(($product = ProductModel::findByPk(Request::getGet('id'))) !== null && $product->tstamp != 0 && !$product->createMultiImageProduct)
		{
			$arrDca['fields']['uploadedFiles']['eval']['maxFiles'] = 1;
		}
		
		if (FE_USER_LOGGED_IN) {
			$user = \FrontendUser::getInstance();
			
			if (count($user->groups) == 1 && (in_array($user->groups[0], \MemberGroupModel::findBy('useForIsoProducts', 1)->fetchEach('id')))) {
				$arrDca['fields']['evu']['eval']['includeBlankOption'] = false;
			}
		}
	}
	
	public function onSubmitCallback(\DataContainer $dc)
	{
		$submission = $this->getSubmission();
		
		if(!empty($submission->uploadedFiles))
		{
			if($submission->createMultiImageProduct)
			{
				$strClass = ISO_PRODUCT_CREATOR_MULTI_IMAGE_PRODUCT;
			}
			else {
				$strClass = ISO_PRODUCT_CREATOR_SINGLE_IMAGE_PRODUCT;
			}
			
		}
		
		$product = new $strClass($this->objModule,$submission,$dc);
		
		$product->generateProduct();
		
	}
}