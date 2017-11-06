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
use Isotope\Model\Download;
use Isotope\Model\ProductModel;
use Isotope\Model\ProductType;

abstract class ProductEditor
{
	protected $productData = [];
	protected $exifData    = [];
	protected $file;
	
	protected static $strTable = 'tl_iso_product';
	
	public function __construct($module, $submission, $dc)
	{
		$this->module       = $module;
		$this->submission   = $submission;
		$this->imageCount   = count($submission->uploadedFiles);
		$this->originalName = $submission->name;
		$this->dc           = $dc;
	}
	
	/**
	 * @return bool
	 */
	protected function create()
	{
		if (empty($this->productData)) {
			return false;
		}
		
		$product = new ProductModel();
		$product->mergeRow($this->productData);
		
		return $product->save();
	}
	
	
	public function generateProduct()
	{
		if (empty($this->submission->uploadedFiles)) {
			return false;
		}
		
		// common data
		$this->setBasicData();
		
		$this->setDataFromModule();
		
		$this->setDataFromExifData();
		
		$this->setDataFromForm();
		
		$this->setTagData();
		
		$this->modifyData();
		
		// image data
		$this->createImageProduct();
		
		$this->submission->delete();
		
		return true;
	}
	
	/**
	 * set basic values for product
	 */
	protected function setBasicData()
	{
		$this->productData['dateAdded'] = time();
		$this->productData['tstamp']    = time();
		
		$this->productData['alias'] = General::generateAlias('', $this->submission->id, 'tl_iso_product', $this->submission->name);
		$this->productData['sku']   = $this->productData['alias'];
		
		$this->productData['addedBy'] = \Contao\Config::get('iso_creatorFallbackMember');
		
		// add user reference to product
		if (FE_USER_LOGGED_IN) {
			$objUser                      = \FrontendUser::getInstance();
			$this->productData['addedBy'] = $objUser->id;
		}
	}
	
	protected function setDataFromModule()
	{
		$this->productData['orderPages'] = $this->module->orderPages;
		
		$this->setDataFromDefaultValues();
	}
	
	/**
	 *
	 */
	protected function setDataFromExifData()
	{
		$mappings = deserialize($this->module->iso_exifMapping, true);
		
		if (empty($mappings)) {
			return;
		}
		
		foreach ($mappings as $mapping) {
			$arrTableFields = explode('.', $mapping['tableField']);
			$strValue       = '';
			
			if (!empty($arrTableFields) && ($strTableField = array_pop($arrTableFields)) != '') {
				
				switch ($mapping['exifTag']) {
					case \PHPExif\Exif::CREATION_DATE :
						$strValue = ProductHelper::prepareExifDataForSave(\PHPExif\Exif::CREATION_DATE, $this->exifData);
						break;
					case \PHPExif\Exif::KEYWORDS :
						$strValue = ProductHelper::prepareExifDataForSave(\PHPExif\Exif::KEYWORDS, $this->exifData);
						break;
					case 'custom' :
						$strValue = $this->exifData[$mapping['customTag']];
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
						$strValue = $this->exifData[$mapping['exifTag']];
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
						$strValue = $objClass->{$arrCallback[1]}($mapping['exifTag'], $mapping, $strValue);
					}
				}
				
				if ($strValue !== null) {
					$this->productData[$strTableField] = $strValue;
				}
			}
		}
	}
	
	protected function setDataFromForm()
	{
		foreach (deserialize($this->module->formHybridEditable) as $value) {
			if (!$this->productData[$value]) {
				$this->productData[$value] = $this->submission->{$value};
			}
		}
	}
	
	protected function setTagData()
	{
		if (!$this->module->iso_useFieldsForTags) {
			return;
		}
		
		$data = $this->productData;
		$tags = [];
		
		foreach (deserialize($this->module->iso_tagFields) as $tagValueField) {
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
		array_merge($this->submission->{$this->module->iso_tagField}, $tags);
		
		
		// Hook : modify the product data
		if (isset($GLOBALS['TL_HOOKS']['creatorProduct']['modifyTagData']) && is_array($GLOBALS['TL_HOOKS']['creatorProduct']['modifyTagData'])) {
			foreach ($GLOBALS['TL_HOOKS']['creatorProduct']['modifyTagData'] as $arrCallback) {
				$objClass = \Controller::importStatic($arrCallback[0]);
				$tags     = $objClass->{$arrCallback[1]}($tags, $this);
			}
		}
		
		// add tag-array to field
		$this->productData[$this->module->iso_tagField] = serialize($tags);
	}
	
	/**
	 * hook to manipulate values before image product is created
	 *
	 * $this->module object
	 * $this->productData array
	 * $this-submission object
	 */
	protected function modifyData()
	{
		// Hook : modify the product data
		if (isset($GLOBALS['TL_HOOKS']['editProduct']['modifyData']) && is_array($GLOBALS['TL_HOOKS']['editProduct']['modifyData'])) {
			foreach ($GLOBALS['TL_HOOKS']['editProduct']['modifyData'] as $arrCallback) {
				$objClass = \Controller::importStatic($arrCallback[0]);
				$objClass->{$arrCallback[1]}($this->module, $this->productData, $this->submission);
			}
		}
	}
	
	
	/**
	 * global objFile is set when file exists
	 *
	 * @param $uuid
	 *
	 * @return bool
	 */
	protected function checkFile($uuid)
	{
		if (!\Validator::isUuid($uuid) || ($file = \Contao\FilesModel::findByUuid($uuid)) === null || !file_exists($file->path)) {
			return false;
		}
		
		$this->file = $file;
		
		return true;
	}
	
	/**
	 * set productData that was set default in module configuration
	 */
	protected function setDataFromDefaultValues()
	{
		if (!$this->module->formHybridAddDefaultValues) {
			return;
		}
		
		$dcaFields     = \HeimrichHannot\Haste\Dca\General::getFields(static::$strTable, false);
		$defaultValues = deserialize($this->module->formHybridDefaultValues, true);
		
		foreach ($defaultValues as $value) {
			if (in_array($value['field'], $dcaFields)) {
				$this->productData[$value['field']] = $value['value'];
			}
		}
	}
	
	
	
	/**
	 * delete all download items for a product before adding new ones
	 *
	 * @param $id
	 */
	protected function cleanDownloadItems($id)
	{
		if (($productDownloads = Download::findBy('pid', $id)) !== null) {
			// clean downloads before adding new ones
			while ($productDownloads->next()) {
				$productDownloads->delete();
			}
		}
	}
	
	/**
	 * @param $index int
	 *
	 * @return array
	 */
	protected function getOriginalImageSize($index = null)
	{
		$suffix = '';
		
		if (!$this->exifData['width'] && !$this->exifData['height']) {
			$orginalSize = getimagesize($this->file->path);
			
			$this->exifData['width']  = $orginalSize[0];
			$this->exifData['height'] = $orginalSize[1];
		}
		
		if ($index) {
			$suffix = ' ' . ($index + 1);
		}
		
		// add original image to download items
		return [
			'size' => [
				$this->exifData['width'],
				$this->exifData['height'],
				'center-center'
			],
			'name' => $GLOBALS['TL_LANG']['MSC']['originalSize'] . $suffix
		];
	}
	
	/**
	 * @param $id int
	 */
	protected function createDownloadItemsForSizes($id, $index = null)
	{
		$suffix = '';
		
		if ($index) {
			$suffix = ' ' . ($index + 1);
		}
		
		foreach (deserialize($this->module->iso_imageSizes) as $size) {
			$size['name'] = $size['name'] . $suffix;
			ProductHelper::createDownloadItem($id, $this->file, $size);
		}
	}
	
	abstract protected function createImageProduct();
	
	abstract protected function getExifData();
	
	abstract protected function setProductImages($uuid);
	
	abstract protected function createDownloadItems($product);
	
	abstract protected function afterCreate($product);
	
	
}