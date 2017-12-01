<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.10.17
 * Time: 13:52
 */

namespace HeimrichHannot\IsotopePlus;



use HeimrichHannot\Haste\Dca\General;
use Isotope\Backend\Product\Category;
use Isotope\Backend\Product\Price;
use Isotope\Model\Product;
use Isotope\Model\ProductModel;
use PHPExif\Reader\Reader;

class SingleImageProduct extends ProductEditor
{
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
			
			$this->createDownloadItemsFromProductImage($product);
			
			$this->createDownloadItemsFromUploadedDownloadFiles($product);
		}
		
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
		$uploadFolder = $this->getUploadFolder($this->dc);
		$this->moveFile($this->file, $uploadFolder);
		
		$this->productData['downloadPdf'] = clone $this->file;
		
		$completePath = $uploadFolder . '/' . $this->getPreviewFromPdf($this->file, $uploadFolder);
		
		// replace $this->file with the preview image of the pdf

		if (file_exists($completePath)) {
			$this->file = \Dbafs::addResource(urldecode($completePath));
		}
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
		$this->moveFile($this->file, $this->getUploadFolder($this->dc));
		
		// need to unset callback to prevent moving of file to uploadFolder disregarding field dependend uploadFolder
		unset($GLOBALS['TL_DCA']['tl_iso_product']['config']['onsubmit_callback']['multifileupload_moveFiles']);
		
		$this->productData['uploadedFiles'] = $this->file->uuid;
	}
	
	
	/**
	 * @param $product ProductModel
	 *
	 * @return bool
	 */
	protected function createDownloadItemsFromProductImage($product)
	{
		if (!$this->module->iso_useUploadsAsDownload) {
			return false;
		}
		
		if ($this->productData['isPdfProduct']) {
			$size = ['name' => $GLOBALS['TL_LANG']['MSC']['downloadPdfItem']];
			$this->createDownloadItem($product, $this->productData['downloadPdf'], $size);
			
			return true;
		}
		
		$size = $this->getOriginalImageSize();
		
		$this->createDownloadItem($product, $this->file, $size);
		
		if (!$this->module->iso_addImageSizes || strtolower($this->file->extension) == 'pdf') {
			return true;
		}
		
		$this->createDownloadItemsForSizes($product);
		
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
		
		// clean the download elements so only the ones according current configuration exist
		$this->cleanDownloadItems($product->id);
		
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