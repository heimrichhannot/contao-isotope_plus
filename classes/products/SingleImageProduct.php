<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 20.10.17
 * Time: 13:52
 */

namespace HeimrichHannot\IsotopePlus;


use HeimrichHannot\FileCredit\FilesModel;
use HeimrichHannot\Haste\Dca\General;
use HeimrichHannot\MultiFileUpload\FormMultiFileUpload;
use Isotope\Backend\Product\Category;
use Isotope\Backend\Product\Price;
use Isotope\Model\Download;
use Isotope\Model\ProductModel;
use PHPExif\Reader\Reader;

class SingleImageProduct extends ProductEditor
{
	public function generateImageData()
	{
		foreach($this->submission->uploadedFiles as $key => $upload)
		{
			if($this->checkFile($upload))
			{
				$this->updateBeforeSafe($key);
				
				$this->getImageData();
				
				$this->setImages();
				
				$this->createProduct();
				
				$this->setDownloadItems();
				
				$this->additionalTasks();
				
				$this->updateAfterSave($key);
			}
		}
	}
	
	/**
	 * add index to name and alias for multiple images
	 *
	 * @param $index
	 */
	protected function updateBeforeSafe($index)
	{
		if($this->imageCount > 1)
		{
			$version = $index + 1;
			
			$this->creatorData['name'] = $this->originalName . ' ' . $version;
			$this->creatorData['alias'] = $this->creatorData['sku'] = General::generateAlias('', $this->submission->id, 'tl_iso_product', $this->originalName) . '-' . $version;
		}
	}
	
	/**
	 * Get exif/iptc data from image
	 */
	public function getImageData()
	{
		$objExifReader = Reader::factory(Reader::TYPE_NATIVE);
		$objExifData   = $objExifReader->read(TL_ROOT . '/' . $this->objFile->path);
		
		
		if ($objExifData === null || $objExifData === false) {
			return;
		}
		
		$this->exifData = $objExifData->getData();
	}
	
	public function setImages()
	{
		if (file_exists($this->objFile->path)) {
			// need to move file now -> download items would otherwise create different sizes in tmp folder
			FormMultiFileUpload::moveFiles($this->dc);
			
			$this->creatorData['uploadedFiles'] = $this->objFile->uuid;
		}
	}
	
	/**
	 * add download elements to product for each set imageSizes and original size
	 */
	public function setDownloadItems()
	{
		if ($this->config->iso_useUploadsAsDownload) {
			
			$id = $this->creatorData['id'] ? $this->creatorData['id'] : $this->submission->id;
			
			if ($this->config->iso_addImageSizes) {
				if(($productDownloads = Download::findBy('pid', $id)) !== null)
				{
					// clean downloads before adding new ones
					while($productDownloads->next())
					{
						$productDownloads->delete();
					}
				}
				
				foreach (deserialize($this->config->iso_imageSizes) as $size) {
					ProductHelper::createDownloadItem($id, $this->objFile, $size);
				}
			}
			
			// add original image to download items
			$size = [
				'size' => [
					$this->exifData['width'],
					$this->exifData['height'],
					'center-center'
				],
				'name' => $GLOBALS['TL_LANG']['MSC']['originalSize']
			];
			ProductHelper::createDownloadItem($id, $this->objFile, $size);
		}
	}
	
	/**
	 * reset dc for multiple images
	 *
	 * @param $index
	 */
	protected function updateAfterSave($index)
	{
		if($index < $this->imageCount - 1)
		{
			$this->creatorData['id'] = $this->creatorData['id'] ? $this->creatorData['id'] + 1 : $this->submission->id + 1;
			
			if(!isset($this->dc->activeRecord))
			{
				if(($this->dc->activeRecord = ProductModel::findByPk($this->creatorData['id'])) === null)
					$this->dc->activeRecord = new ProductModel();

				$arrData = array_merge($this->submission->row(), $this->creatorData);
				$this->dc->activeRecord->setRow($arrData);
			}
			
			$this->dc->intId = $this->creatorData['id'];
		}
	}
	
	/**
	 * save price and category for product
	 */
	
	public function additionalTasks()
	{
		// save exif data in tl_files
		if (!empty($this->exifData)) {
			$this->objFile->exif = $this->exifData;
			$this->objFile->save();
		}
		
		// add product categories to isotope category table
		Category::save(deserialize($this->config->orderPages), $this->dc);
		
		// add price to product and isotope price table
		Price::save(['value' => '0.00', 'unit' => 0], $this->dc);
		
		// clear product cache
		\Isotope\Backend::truncateProductCache();
	}
}