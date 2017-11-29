<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.10.17
 * Time: 13:52
 */

namespace HeimrichHannot\IsotopePlus;


use Ghostscript\Transcoder;
use HeimrichHannot\FileCredit\FilesModel;
use HeimrichHannot\Haste\Util\Files;
use HeimrichHannot\MultiFileUpload\FormMultiFileUpload;
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
			$this->prepareProductImages($this->submission->uploadedFiles);
			
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
	protected function prepareProductImages($uuids)
	{
		foreach($uuids as $key => $upload)
		{
			$file = \FilesModel::findByUuid($upload);
			
			$this->moveFile($file, $this->getUploadFolder($this->dc));
			
			if(strtolower($file->extension) != 'pdf')
			{
				continue;
			}
			
			$this->productData['uploadedFiles'][$key] = $this->preparePdfPreview($file);
			$this->productData['isPdfProduct'] = true;
		}
		
		unset($GLOBALS['TL_DCA']['tl_iso_product']['config']['onsubmit_callback']['multifileupload_moveFiles']);
	}
	
	
	protected function preparePdfPreview($file)
	{
		// copy original pdf to user folder to keep it as download element
		$uploadFolder = $this->getUploadFolder($this->dc);
		$this->moveFile($file, $uploadFolder);
		
		$this->productData['downloadPdf'][] = $file;
		
		$completePath = $uploadFolder . '/' . $this->getPreviewFromPdf($file, $uploadFolder);
		
		// replace $this->file with the preview image of the pdf
		if (file_exists($completePath)) {
			return \Dbafs::addResource(urldecode($completePath))->uuid;
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
		
		if($this->productData['isPdfProduct'] && !empty($this->productData['downloadPdf']))
		{
			foreach($this->productData['downloadPdf'] as $pdf)
			{
				$size = ['name' => sprintf($GLOBALS['TL_LANG']['MSC']['downloadPdfItem'],$pdf->name)];
				ProductHelper::createDownloadItem($product->id, $pdf, $size, $pdf);
			}
			
			return true;
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
		// set intId to save category and price on correct id
		$this->dc->intId = $product->id;
		
		// add product categories to isotope category table
		Category::save(deserialize($this->module->orderPages,true), $this->dc);
		
		// add price to product and isotope price table
		Price::save(['value' => '0.00', 'unit' => 0], $this->dc);
		
		// clear product cache
		\Isotope\Backend::truncateProductCache();
	}
}