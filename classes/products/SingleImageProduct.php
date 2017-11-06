<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.10.17
 * Time: 13:52
 */

namespace HeimrichHannot\IsotopePlus;


use HeimrichHannot\Haste\Dca\General;
use HeimrichHannot\MultiFileUpload\FormMultiFileUpload;
use Isotope\Backend\Product\Category;
use Isotope\Backend\Product\Price;
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
			
			$this->setProductImages($upload);
			
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
		if ($this->imageCount > 1) {
			$version = $index + 1;
			
			$this->productData['name']  = $this->originalName . ' ' . $version;
			$this->productData['alias'] =
			$this->productData['sku'] = General::generateAlias('', $this->submission->id, 'tl_iso_product', $this->originalName) . '-' . $version;
		}
	}
	
	/**
	 * get exif/iptc data from image
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
	protected function setProductImages($uuid)
	{
		// need to move file now -> download items would otherwise create different sizes in tmp folder
		FormMultiFileUpload::moveFiles($this->dc);
		
		$this->productData['uploadedFiles'] = $uuid;
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
		
		$size = $this->getOriginalImageSize();
		
		ProductHelper::createDownloadItem($product->id, $this->file, $size);
		
		if (!$this->module->iso_addImageSizes) {
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
		Category::save(deserialize($this->module->orderPages), $this->dc);

		// add price to product and isotope price table
		Price::save(['value' => '0.00', 'unit' => 0], $this->dc);

		// clear product cache
		\Isotope\Backend::truncateProductCache();
	}
}