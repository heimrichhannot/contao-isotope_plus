<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.10.17
 * Time: 12:29
 */

namespace HeimrichHannot\IsotopePlus;


use HeimrichHannot\Haste\Dca\General;
use HeimrichHannot\Haste\Util\FormSubmission;
use Isotope\Model\Product;
use Isotope\Model\ProductModel;
use Isotope\Model\ProductType;

abstract class ProductEditor
{
	const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'tiff', 'png'];
	protected $creatorData = [];
	protected $exifData    = [];
	protected $objFile;
	
	protected static $strTable = 'tl_iso_product';
	
	public function __construct($config, $submission, $dc)
	{
		$this->config       = $config;
		$this->submission   = $submission;
		$this->imageCount   = count($submission->uploadedFiles);
		$this->originalName = $submission->name;
		$this->dc           = $dc;
	}
	
	protected function createProduct()
	{
		if (!empty($this->creatorData)) {
			
			$arrData = array_merge($this->submission->row(), $this->creatorData);
			$product = $this->submission;
			
			if ($this->creatorData['id'] || (($pCheck = ProductModel::findByPk($product->id)) !== null && $pCheck->tstamp != 0)) {
				if(($product = ProductModel::findByPk($this->creatorData['id'])) === null)
					$product = new ProductModel();
			}
			
			$product->setRow($arrData);
			// set arrModified -> otherwise save() thinks the db-entity is already up to date
			
			
			foreach ($this->creatorData as $key => $modifiedField) {
				$product->markModified($key);
			}
			
			$product->save();
		}
	}
	
	public function generateProduct()
	{
		if (!empty($this->submission->uploadedFiles)) {
			$this->generateCommonData();
			
			$this->generateImageData();
		}
	}
	
	
	public function generateCommonData()
	{
		$this->setBasicData();
		
		$this->setDataFromModule();
		
		$this->setDataFromExifData();
		
		$this->setAdditionalData();
		
		$this->setDataFromForm();
		
		$this->setTagData();
	}
	
	/**
	 * Check varFile input for allowed exif extensions
	 *
	 * @param $varFile
	 *
	 * @return bool
	 */
	public function checkFile($file)
	{
		if ($file != '' && $file !== null) {
			// check if input is a FilesModel, an uuid or a path
			if (!($file instanceof \FilesModel)) {
				if (\Validator::isUuid($file)) {
					if (($objFile = \FilesModel::findByUuid($file)) === null) {
						return false;
					}
				} else {
					if (($objFile = \FilesModel::findByPath($file)) === null) {
						return false;
					}
				}
			} else {
				$objFile = $file;
			}
			
			$arrPathInfo = pathinfo(TL_ROOT . '/' . $objFile->path);
			if (in_array($arrPathInfo['extension'], self::ALLOWED_IMAGE_EXTENSIONS)) {
				$this->objFile = $objFile;
				
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Map the exif tags to database fields
	 *
	 * @param $objModule
	 */
	public function setDataFromExifData()
	{
		$arrMappings = deserialize($this->config->iso_exifMapping, true);
		
		foreach ($arrMappings as $arrMapping) {
			$arrTableFields = explode('.', $arrMapping['tableField']);
			$strValue       = '';
			
			if (!empty($arrTableFields) && ($strTableField = array_pop($arrTableFields)) != '') {
				
				switch ($arrMapping['exifTag']) {
					case \PHPExif\Exif::CREATION_DATE :
						$strValue = ProductHelper::prepareExifDataForSave(\PHPExif\Exif::CREATION_DATE, $this->exifData);
						break;
					case \PHPExif\Exif::KEYWORDS :
						$strValue = ProductHelper::prepareExifDataForSave(\PHPExif\Exif::KEYWORDS, $this->exifData);
						break;
					case 'custom' :
						$strValue = $this->exifData[$arrMapping['customTag']];
						break;
					
					case \PHPExif\Exif::APERTURE :
					case \PHPExif\Exif::AUTHOR :
					case \PHPExif\Exif::CAMERA :
					case \PHPExif\Exif::CAPTION :
					case \PHPExif\Exif::COLORSPACE :
					case \PHPExif\Exif::COPYRIGHT :
					case \PHPExif\Exif::CREDIT :
					case \PHPExif\Exif::EXPOSURE :
					case \PHPExif\Exif::FILESIZE :
					case \PHPExif\Exif::FOCAL_LENGTH :
					case \PHPExif\Exif::FOCAL_DISTANCE :
					case \PHPExif\Exif::HEADLINE :
					case \PHPExif\Exif::HEIGHT :
					case \PHPExif\Exif::HORIZONTAL_RESOLUTION :
					case \PHPExif\Exif::ISO :
					case \PHPExif\Exif::JOB_TITLE :
					case \PHPExif\Exif::MIMETYPE :
					case \PHPExif\Exif::ORIENTATION :
					case \PHPExif\Exif::SOFTWARE :
					case \PHPExif\Exif::SOURCE :
					case \PHPExif\Exif::TITLE :
					case \PHPExif\Exif::VERTICAL_RESOLUTION :
					case \PHPExif\Exif::WIDTH :
					case \PHPExif\Exif::GPS :
						$strValue = $this->exifData[$arrMapping['exifTag']];
						break;
					
					default :
						break;
				}
				
				// Hook : handle exif tags
				if (isset($GLOBALS['TL_HOOKS']['creatorProduct']['handleExifTags'])
					&& is_array(
						$GLOBALS['TL_HOOKS']['creatorProduct']['handleExifTags']
					)
				) {
					foreach ($GLOBALS['TL_HOOKS']['creatorProduct']['handleExifTags'] as $arrCallback) {
						$objClass = \Controller::importStatic($arrCallback[0]);
						$strValue = $objClass->{$arrCallback[1]}($arrMapping['exifTag'], $arrMapping, $strValue);
					}
				}
				
				if ($strValue !== null) {
					$this->creatorData[$strTableField] = $strValue;
				}
			}
		}
	}
	
	public function setDataFromModule()
	{
		if (count(deserialize($this->config->iso_editableCategories)) > 1) {
			$this->creatorData['type'] = $this->submission->type;
		} else {
			$this->creatorData['type'] = deserialize($this->config->iso_editableCategories)[0];
		}
		
		$this->creatorData['orderPages'] = $this->config->orderPages;
		
		if ($this->config->formHybridAddDefaultValues) {
			$this->setDataFromDefaultValues();
		}
	}
	
	public function setDataFromDefaultValues()
	{
		$arrDcaFields     = \HeimrichHannot\Haste\Dca\General::getFields(static::$strTable, false);
		$arrDefaultValues = deserialize($this->config->formHybridDefaultValues, true);
		
		foreach ($arrDefaultValues as $arrValue) {
			if (in_array($arrValue['field'], $arrDcaFields)) {
				$this->creatorData[$arrValue['field']] = $arrValue['value'];
			}
		}
	}
	
	
	public function setAdditionalData()
	{
		// Hook : modify the product data
		if (isset($GLOBALS['TL_HOOKS']['creatorProduct']['modifyData']) && is_array($GLOBALS['TL_HOOKS']['creatorProduct']['modifyData'])) {
			foreach ($GLOBALS['TL_HOOKS']['creatorProduct']['modifyData'] as $arrCallback) {
				$objClass = \Controller::importStatic($arrCallback[0]);
				$objClass->{$arrCallback[1]}($this->config, $this->creatorData, $this->submission);
			}
		}
	}
	
	public function setDataFromForm()
	{
		foreach (deserialize($this->config->formHybridEditable) as $value) {
			if (!array_key_exists($value, $this->creatorData)) {
				$this->creatorData[$value] = $this->submission->{$value};
			}
		}
	}
	
	
	public function setTagData()
	{
		\Controller::loadDataContainer(static::$strTable);
		$data = $this->creatorData;
		
		if ($this->config->iso_useFieldsForTags) {
			$tags = [];
			foreach (deserialize($this->config->iso_tagFields) as $tagValueField) {
				if ($tagValueField == 'type') {
					$data[$tagValueField] = ProductType::findByPk($this->submission->type)->name;
				}
				
				$tags[] = FormSubmission::prepareSpecialValueForPrint(
					$data[$tagValueField],
					$GLOBALS['TL_DCA']['tl_iso_product']['fields'][$tagValueField],
					'tl_iso_product',
					$this->dc
				);
			}
			
			// add tags from form-field
			array_merge($this->submission->{$this->config->iso_tagField}, $tags);
			
			
			// Hook : modify the product data
			if (isset($GLOBALS['TL_HOOKS']['creatorProduct']['modifyTagData']) && is_array($GLOBALS['TL_HOOKS']['creatorProduct']['modifyTagData'])) {
				foreach ($GLOBALS['TL_HOOKS']['creatorProduct']['modifyTagData'] as $arrCallback) {
					$objClass = \Controller::importStatic($arrCallback[0]);
					$tags     = $objClass->{$arrCallback[1]}($tags, $this);
				}
			}
			
			
			// add tag-array to field
			$this->creatorData[$this->config->iso_tagField] = serialize($tags);
		}
	}
	
	/**
	 * set basic values for product
	 */
	public function setBasicData()
	{
		$this->creatorData['dateAdded'] = time();
		$this->creatorData['tstamp']    = time();
		
		$this->creatorData['alias'] = General::generateAlias('', $this->submission->id, 'tl_iso_product', $this->submission->name);
		$this->creatorData['sku']   = $this->creatorData['alias'];
		
		// add user reference to product
		if(FE_USER_LOGGED_IN)
		{
			$objUser                      = \FrontendUser::getInstance();
			$this->creatorData['addedBy'] = $objUser->id;
		}
		
		if (!$this->creatorData['addedBy']) {
			$this->creatorData['addedBy'] = \Config::get('iso_creatorFallbackMember');
		}
	}
	
	abstract public function generateImageData();
	
	abstract public function getImageData();
	
	abstract public function setImages();
	
	abstract public function setDownloadItems();
	
	abstract public function additionalTasks();
}