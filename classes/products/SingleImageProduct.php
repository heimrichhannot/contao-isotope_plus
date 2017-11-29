<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.10.17
 * Time: 13:52
 */

namespace HeimrichHannot\IsotopePlus;



use Ghostscript\Transcoder;
use HeimrichHannot\Haste\Dca\General;
use HeimrichHannot\Haste\Util\Files;
use HeimrichHannot\MultiFileUpload\FormMultiFileUpload;
use Isotope\Backend\Product\Category;
use Isotope\Backend\Product\Price;
use Isotope\Model\ProductModel;
use PHPExif\Reader\Reader;

class SingleImageProduct extends ProductEditor
{
	protected static $convertFileType = 'png';
	
	/**
	 * @return bool
	 */
	protected function createImageProduct()
	{
		foreach ($this->submission->uploadedFiles as $index => $upload) {
			if (!$this->checkFile($upload)) {
				continue;
			}
			
			$this->updateProductDataBeforeSave($index);
			
			$this->exifData = $this->getExifData();
			
			$this->prepareProductImages($upload);
			
			$product = $this->create();
			
			$this->afterCreate($product);
			
			$this->createDownloadItems($product);
		}
		
		// delete submission since for all products an new model was created
		$this->submission->delete();
		
		return true;
	}
	
	/**
	 * add index to name and alias for multiple images
	 *
	 * @param $index
	 */
	protected function updateProductDataBeforeSave($index)
	{
		if (strtolower($this->file->extension) == 'pdf') {
			$this->preparePdfPreview();
			$this->productData['isPdfProduct'] = true;
		}
		
		if ($this->imageCount > 1) {
			$version = $index + 1;
			
			$this->productData['name']  = $this->originalName . ' ' . $version;
			$this->productData['alias'] =
			$this->productData['sku'] = General::generateAlias('', $this->submission->id, 'tl_iso_product', $this->originalName) . '-' . $version;
		}
	}
	
	/**
	 * convert pdf to png and use first converted png as main image
	 */
	protected function preparePdfPreview()
	{
		// copy original pdf to user folder to keep it as download element
		$originalName = $this->file->name;
		
		$uploadFolder = Files::getFolderFromDca($GLOBALS['TL_DCA']['tl_iso_product']['fields']['uploadedFiles']['eval']['uploadFolder'], $this->dc);
		
		// create new File to enable moving the pdf to user folder
		$pdfFile = new \File($this->file->path);
		$pdfFile->close();
		$strTarget = $uploadFolder . '/' . $originalName;
		$strTarget = Files::getUniqueFileNameWithinTarget($strTarget, FormMultiFileUpload::UNIQID_PREFIX);
		
		// move pdf to user folder
		$pdfFile->renameTo($strTarget);
		$this->productData['downloadPdf'] = clone $this->file;
		
		$completePath = $uploadFolder . '/' . $this->getPreviewFromPdf($uploadFolder);
		
		// replace $this->file with the preview image of the pdf
		if (file_exists($completePath)) {
			$this->file = \Dbafs::addResource(urldecode($completePath));
		}
	}
	
	/**
	 * convert pdf to png and return only first page/image
	 * delete the other png files
	 *
	 * @param $uploadFolder string
	 *
	 * @return string name of preview file
	 */
	protected function getPreviewFromPdf($uploadFolder)
	{
		$destinationFileName = 'preview-' . str_replace('.pdf', '', $this->file->name) . '.' . static::$convertFileType;
		
		// ghostscript
		$transcoder = Transcoder::create();
		$transcoder->toImage($this->file->path,$uploadFolder . '/' . $destinationFileName);
		
		$search = str_replace('.'.static::$convertFileType,'',$destinationFileName);
		$files = preg_grep('~^'.$search.'.*\.'.static::$convertFileType.'$~', scandir($uploadFolder));

		return reset($files);
	}
	
	
	/**
	 * get exif/iptc data from image
	 *
	 * @return array
	 */
	protected function getExifData()
	{
		$exifReader = Reader::factory(Reader::TYPE_NATIVE);
		$exifData   = $exifReader->read(TL_ROOT . '/' . $this->file->path);
		
		if ($exifData === null || $exifData === false) {
			return [];
		}
		
		return $exifData->getData();
	}
	
	/**
	 * check if file exists in filesystem
	 *
	 * @param $uuid
	 */
	protected function prepareProductImages($uuid)
	{
		// need to move file now -> download items would otherwise create different sizes in tmp folder
		FormMultiFileUpload::moveFiles($this->dc);
		
		$this->productData['uploadedFiles'] = $this->file->uuid;
	}
	
	/**
	 * @param $product ProductModel
	 *
	 * @return bool
	 */
	protected function createDownloadItems($product)
	{
		if (!$this->module->iso_useUploadsAsDownload) {
			return false;
		}
		
		$this->cleanDownloadItems($product->id);
		
		if ($this->productData['isPdfProduct']) {
			$size = ['name' => $GLOBALS['TL_LANG']['MSC']['downloadPdfItem']];
			ProductHelper::createDownloadItem($product->id, $this->productData['downloadPdf'], $size, $this->productData['downloadPdf']);
			
			return true;
		}
		
		$size = $this->getOriginalImageSize();
		
		ProductHelper::createDownloadItem($product->id, $this->file, $size);
		
		if (!$this->module->iso_addImageSizes || strtolower($this->file->path) == 'pdf') {
			return true;
		}
		
		$this->createDownloadItemsForSizes($product->id);
		
		return true;
	}
	
	
	/**
	 * category and price need to be saved with their own models
	 * otherwise these values are not displayed in backend
	 *
	 * @param $product
	 */
	protected function afterCreate($product)
	{
		// save exif data in tl_files
		if (!empty($this->exifData)) {
			$this->file->exif = $this->exifData;
			$this->file->save();
		}
		
		// set intId to save category and price on correct id
		$this->dc->intId = $product->id;
		
		// add product categories to isotope category table
		Category::save(deserialize($this->module->orderPages, true), $this->dc);
		
		// add price to product and isotope price table
		Price::save(['value' => '0.00', 'unit' => 0], $this->dc);
		
		// clear product cache
		\Isotope\Backend::truncateProductCache();
	}
}