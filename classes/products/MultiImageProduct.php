<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.10.17
 * Time: 13:52
 */

namespace HeimrichHannot\IsotopePlus;


use HeimrichHannot\FileCredit\FilesModel;
use HeimrichHannot\Haste\Util\Files;
use HeimrichHannot\MultiFileUpload\FormMultiFileUpload;
use Isotope\Backend\Product\Category;
use Isotope\Backend\Product\Price;
use Isotope\Model\ProductModel;

class MultiImageProduct extends ProductEditor
{
	protected static $convertFileType = 'jpg';
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
			
			if(strtolower($file->extension) != 'pdf')
			{
				continue;
			}
			
			$this->productData['uploadedFiles'][$key] = $this->preparePdfPreview($file);
			$this->productData['isPdfProduct'] = true;
		}
	}
	
	
	protected function preparePdfPreview($file)
	{
		// copy original pdf to user folder to keep it as download element
		$originalName = $file->name;
		
		$uploadFolder = Files::getFolderFromDca($GLOBALS['TL_DCA']['tl_iso_product']['fields']['uploadedFiles']['eval']['uploadFolder'], $this->dc);
		
		// create new File to enable moving the pdf to user folder
		$pdfFile = new \File($file->path);
		$pdfFile->close();
		$strTarget = $uploadFolder . '/' . $originalName;
		$strTarget = Files::getUniqueFileNameWithinTarget($strTarget, FormMultiFileUpload::UNIQID_PREFIX);
		
		// move pdf to user folder
		$pdfFile->renameTo($strTarget);
		$this->productData['downloadPdf'][] = $pdfFile->getModel();
		
		$completePath = $uploadFolder . '/' . $this->getPreviewFromPdf($uploadFolder,$file);
		
		// replace $this->file with the preview image of the pdf
		if (file_exists($completePath)) {
			return \Dbafs::addResource(urldecode($completePath))->uuid;
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
	protected function getPreviewFromPdf($uploadFolder,$file)
	{
		$im                  = new \Orbitale\Component\ImageMagick\Command();
		$destinationFileName = 'preview-' . str_replace('.pdf', '', $file->name) . '.' . static::$convertFileType;
		
		$im->convert($file->path)->file($uploadFolder . '/' . $destinationFileName, false)->run();
		
		$search = str_replace('.'.static::$convertFileType,'',$destinationFileName);
		
		$files = preg_grep('~^'.$search.'.*\.'.static::$convertFileType.'$~', scandir($uploadFolder));
		
		$previewFile = reset($files);
		
		foreach ($files as $key => $fileVersion) {
			if ($fileVersion == $previewFile) {
				continue;
			}
			
			unlink($uploadFolder . '/' . $fileVersion);
		}
		
		return $previewFile;
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