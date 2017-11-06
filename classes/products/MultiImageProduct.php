<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.10.17
 * Time: 13:52
 */

namespace HeimrichHannot\IsotopePlus;


use HeimrichHannot\FileCredit\FilesModel;
use Isotope\Backend\Product\Category;
use Isotope\Backend\Product\Price;
use Isotope\Model\ProductModel;

class MultiImageProduct extends ProductEditor
{
	
	/**
	 * @return bool
	 */
	protected function createImageProduct()
	{
		if($this->checkFiles($this->submission->uploadedFiles))
		{
			$this->setProductImages($this->submission->uploadedFiles);
			
			$product = $this->create();
			
			$this->createDownloadItems($product);
			
			$this->afterCreate($product);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * @param $files array
	 *
	 * @return bool
	 */
	protected function checkFiles($files)
	{
		$filesLegit = true;
		foreach($files as $file)
		{
			$filesLegit = $this->checkFile($file);
		}
		
		return $filesLegit;
	}
	
	// TODO store exif data for multiple files
	protected function getExifData(){}
	
	/**
	 * @param $uuids array
	 */
	protected function setProductImages($uuids)
	{
		foreach($uuids as $upload)
		{
			if (!file_exists(FilesModel::findByUuid($upload)->path)) {
				unset($this->productData['uploadedFiles'][$upload]);
			}
		}
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
		
		foreach($this->submission->uploadedFiles as $key => $value)
		{
			$size = $this->getOriginalImageSize($key);
			ProductHelper::createDownloadItem($product->id, $this->file, $size);
			
			if (!$this->module->iso_addImageSizes) {
				continue;
			}
			
			$this->createDownloadItemsForSizes($product->id, $key);
		}
		
		return true;
	}
	
	/**
	 * save price and category for product
	 * @param $product
	 */
	protected function afterCreate($product)
	{
		// add product categories to isotope category table
		Category::save(deserialize($this->module->orderPages), $this->dc);
		
		// add price to product and isotope price table
		Price::save(['value' => '0.00', 'unit' => 0], $this->dc);
		
		// clear product cache
		\Isotope\Backend::truncateProductCache();
	}
}