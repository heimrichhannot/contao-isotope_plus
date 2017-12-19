<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.10.17
 * Time: 12:29
 */

namespace HeimrichHannot\IsotopePlus;


use Ghostscript\Transcoder;
use HeimrichHannot\FileCredit\FilesModel;
use HeimrichHannot\Haste\Dca\General;
use HeimrichHannot\Haste\Util\FormSubmission;
use HeimrichHannot\HastePlus\Files;
use HeimrichHannot\MultiFileUpload\FormMultiFileUpload;
use Isotope\Model\Download;
use Isotope\Model\ProductModel;
use Isotope\Model\ProductType;
use PhpImap\Exception;

abstract class ProductEditor
{
	protected static $convertFileType = 'png';
	
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
		
		$product = clone $this->submission;
		$product->mergeRow($this->productData);
		
		return $product->save();
	}
	
	/**
	 * @return bool
	 */
	public function generateProduct()
	{
		if (empty($this->submission->uploadedFiles)) {
			return false;
		}
		
		// common data
		$this->prepareBasicData();
		
		$this->prepareDataFromModule();
		
		$this->prepareDataFromExifData();
		
		$this->prepareDataFromForm();
		
		$this->modifyData();
		
		$this->prepareTagData();
		
		// image data
		$this->createImageProduct();
		
		// delete submission since for all products an new model was created
		$this->submission->delete();
		
		
		return true;
	}
	
	/**
	 * set basic values for product
	 */
	protected function prepareBasicData()
	{
		$this->productData['dateAdded'] = $this->submission->dateAdded ? $this->submission->dateAdded : time();
		
		$this->productData['tstamp'] = time();
		
		$this->productData['alias'] = $this->submission->alias
			? $this->submission->alias
			: General::generateAlias(
				'',
				$this->submission->id,
				'tl_iso_product',
				$this->submission->name
			);
		
		$this->productData['sku'] = $this->productData['alias'];
		
		$this->productData['addedBy'] = \Contao\Config::get('iso_creatorFallbackMember');
		
		// add user reference to product
		if (FE_USER_LOGGED_IN) {
			$objUser                      = \FrontendUser::getInstance();
			$this->productData['addedBy'] = $objUser->id;
		}
	}
	
	/**
	 * set productData from module configuration
	 */
	protected function prepareDataFromModule()
	{
		$pages = deserialize($this->module->orderPages,true);
		
		
		if(null !== $this->submission->orderPages)
		{
			foreach(deserialize($this->submission->orderPages, true) as $page)
			{
				$pages[] = $page;
			}
		}
		
		$this->productData['orderPages'] = serialize($pages);
		
		$this->setDataFromDefaultValues();
	}
	
	/**
	 * map exif data according to module settings
	 */
	protected function prepareDataFromExifData()
	{
		$mappings = deserialize($this->module->iso_exifMapping, true);
		
		if (empty($mappings)) {
			return;
		}
		
		foreach ($mappings as $mapping) {
			$arrTableFields = explode('.', $mapping['tableField']);
			
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
					
					default :
						$strValue = $this->exifData[$mapping['exifTag']];
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
				
				if ($strValue) {
					$this->productData[$strTableField] = $strValue;
				}
			}
		}
	}
	
	/**
	 * set productData values from submission
	 */
	protected function prepareDataFromForm()
	{
		foreach (deserialize($this->module->formHybridEditable, true) as $value) {
			if ($this->productData[$value]) {
				continue;
			}
			$this->productData[$value] = $this->submission->{$value};
		}
	}
	
	/**
	 * join fields from submission into tag field (has to be set in module)
	 */
	protected function prepareTagData()
	{
		if (!$this->module->iso_useFieldsForTags) {
			return;
		}
		
		$data = $this->productData;
		$tags = [];
		
		foreach (deserialize($this->module->iso_tagFields, true) as $tagValueField) {
//			if ($tagValueField == 'type') {
//				$data[$tagValueField] = ProductType::findByPk($this->submission->type)->name;
//			}
			
			if('' == $data[$tagValueField])
				continue;
			
			$tags[] = FormSubmission::prepareSpecialValueForPrint(
				$data[$tagValueField],
				$GLOBALS['TL_DCA']['tl_iso_product']['fields'][$tagValueField],
				'tl_iso_product',
				$this->dc
			);
		}
		
		// add tags from form-field
		$tags = array_merge(deserialize($this->submission->{$this->module->iso_tagField}, true), $tags);
		
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
		if (isset($GLOBALS['TL_HOOKS']['editProduct_modifyData']) && is_array($GLOBALS['TL_HOOKS']['editProduct_modifyData'])) {
			foreach ($GLOBALS['TL_HOOKS']['editProduct_modifyData'] as $arrCallback) {
				$objClass = \Controller::importStatic($arrCallback[0]);
				list($this->module,$this->productData,$this->submission) = $objClass->{$arrCallback[1]}($this->module, $this->productData, $this->submission);
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
			if (!in_array($value['field'], $dcaFields)) {
				continue;
			}
			
			$this->productData[$value['field']] = $value['value'];
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
	 * create download element for each set size of isotope product image
	 *
	 * @param $id   int
	 * @param $file object
	 * @param $size array
	 */
	public function createDownloadItem($product, $file, $size,$uploadFolder = null)
	{
		$name = ProductHelper::getFileName($file, $size);
		$path = ProductHelper::getFilePath($file, $name);
		
		
		if (!file_exists($path) && in_array($file->extension,['mp4','mp3','html','tif','eps'])) {
			$path = \Image::get($file->path, $size['size'][0], $size['size'][1], $size['size'][2], $path);
		}
		
		if (($downloadFile = \FilesModel::findByPath($path)) === null) {
			$downloadFile = \Dbafs::addResource(urldecode($path));
		}
		
		$this->saveCopyrightForFile($downloadFile, $product);
		
		// create Isotope download
		$objDownload            = new Download();
		
		if('pdf' == $file->extension)
		{
			$objDownload->download_thumbnail = serialize([$this->getPDFThumbnail($file,$uploadFolder)]);
		}
		else {
			$objDownload->download_thumbnail = serialize([$file->uuid]);
		}
		
		$objDownload->pid       = $product->id;
		$objDownload->tstamp    = time();
		$objDownload->title     = $size['name'];
		$objDownload->singleSRC = $downloadFile->uuid;
		$objDownload->published = 1;
		
		
		$objDownload->save();
	}
	
	protected function getPDFThumbnail($file,$uploadFolder = null)
	{
		if(null === $uploadFolder)
		{
			$uploadFolder = $this->getUploadFolder($this->dc);
		}
		
		$completePath = $uploadFolder . '/' . $this->getPreviewFromPdf($file, $uploadFolder);
		if (file_exists($completePath)) {
			$completePath = \Dbafs::addResource(urldecode($completePath));
		}
		
		
		if($completePath->uuid)
		{
			return $completePath->uuid;
		}
		
		else {
			return \FilesModel::findByPath($completePath)->uuid;
		}
	}
	
	
	/**
	 * @param $product ProductModel
	 */
	protected function createDownloadItemsFromUploadedDownloadFiles($product)
	{
		$downloadUploads = deserialize($product->uploadedDownloadFiles,true);
		
		if(empty($downloadUploads))
			return;
		
		foreach($downloadUploads as $downloadUpload)
		{
			$file = \FilesModel::findByUuid($downloadUpload);
			
			$this->moveFile($file, $this->getUploadFolder($this->dc));
			
			$size = ['name' => sprintf($GLOBALS['TL_LANG']['MSC']['downloadItem'],str_replace(['_', '-','.'.$file->extension], [' ',' ',''], $file->name))];

//			$size = ['name' => $GLOBALS['TL_LANG']['MSC']['downloadItem']];
			$this->createDownloadItem($product, $file, $size);
		}
	}
	
	/**
	 *
	 *
	 * @param $file    \FilesModel
	 * @param $product ProductModel
	 */
	public function saveCopyrightForFile($file, $product)
	{
		$file->licence   = $product->licence;
		$file->addedBy   = $product->addedBy;
		$file->copyright = $product->copyright;
		
		$file->save();
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
		
		foreach (deserialize($this->module->iso_imageSizes, true) as $size) {
			$size['name'] = $size['name'] . $suffix;
			ProductEditor::createDownloadItem($id, $this->file, $size);
		}
	}
	
	/**
	 * move file to destination
	 *
	 * @param $file   \FilesModel
	 * @param $folder string
	 */
	public function moveFile($file, $folder)
	{
		// create new File to enable moving the pdf to user folder
		$moveFile = new \File($file->path);
		$moveFile->close();
		$strTarget = $folder . '/' . $file->name;
		$strTarget = Files::getUniqueFileNameWithinTarget($strTarget, FormMultiFileUpload::UNIQID_PREFIX);
		
		// move file to upload folder
		$moveFile->renameTo($strTarget);
	}
	
	
	/**
	 * @param $dc \DataContainer
	 *
	 * @return string
	 */
	public function getUploadFolder($dc)
	{
		$uploadFolder = Callbacks::getUploadFolder($dc);
		
		if ($this->module->iso_useFieldDependendUploadFolder) {
			$uploadFolder .= '/' . $this->productData[$this->module->iso_fieldForUploadFolder];
		}
		
		return $uploadFolder;
	}
	
	/**
	 * convert pdf to png and return only first page/image
	 * delete the other png files
	 *
	 * @param $uploadFolder string
	 *
	 * @return string name of preview file
	 */
	public function getPreviewFromPdf($file, $uploadFolder)
	{
		$destinationFileName = 'preview-' . str_replace('.pdf', '', $file->name) . '.' . static::$convertFileType;
		
		// ghostscript
		$transcoder = Transcoder::create();
		
		$transcoder->toImage(TL_ROOT . DIRECTORY_SEPARATOR . $file->path,
							 TL_ROOT . DIRECTORY_SEPARATOR . $uploadFolder . '/' . $destinationFileName
		);
		
		$search = str_replace('.' . static::$convertFileType, '', $destinationFileName);
		$files  = preg_grep('~^' . $search . '.*\.' . static::$convertFileType . '$~', scandir(TL_ROOT . DIRECTORY_SEPARATOR .$uploadFolder));
		
		return reset($files);
		
	}
	
	abstract protected function createImageProduct();
	
	abstract protected function getExifData();
	
	abstract protected function prepareProductImages($uuid);
	
	abstract protected function createDownloadItemsFromProductImage($product);
	
	abstract protected function afterCreate($product);
	
	
}