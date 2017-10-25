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
use Isotope\Model\Download;

class MultiImageProduct extends ProductEditor
{
	public function generateImageData()
	{
		if($this->checkMultipleFiles())
		{
			$this->getImageData();
			
			$this->setImages();
			
			$this->setDownloadItems();
			
			$this->createProduct();
			
			$this->additionalTasks();
		}
	}
	
	protected function checkMultipleFiles()
	{
		$filesLegit = true;
		foreach($this->submission->uploadedFiles as $upload)
		{
			$filesLegit = $this->checkFile($upload);
		}
		
		return $filesLegit;
	}
	
	public function getImageData(){}
	
	/**
	 * check each file on existence
	 */
	public function setImages()
	{
		foreach($this->submission->uploadedFiles as $upload)
		{
			if (!file_exists(FilesModel::findByUuid($upload)->path)) {
				unset($this->creatorData['uploadedFiles'][$upload]);
			}
		}
	}
	
	/**
	 * add download elements to product for each set imageSizes and original size
	 */
	public function setDownloadItems()
	{
		if ($this->config->iso_useUploadsAsDownload) {
			
			foreach($this->submission->uploadedFiles as $key => $upload)
			{
				$file = FilesModel::findByUuid($upload);
				
				if ($this->config->iso_addImageSizes) {
					if(($productDownloads = Download::findBy('pid', $this->submission->id)) !== null)
					{
						// clean downloads before adding new ones
						while($productDownloads->next())
						{
							$productDownloads->delete();
						}
					}
					
					foreach (deserialize($this->config->iso_imageSizes) as $size) {
						$size['name'] = $size['name'] . ' ' . ($key + 1);
						ProductHelper::createDownloadItem($this->submission->id, $file, $size);
					}
				}
				
				// add original image to download items
				$size = [
					'size' => [
						$this->exifData['width'],
						$this->exifData['height'],
						'center-center'
					],
					'name' => $GLOBALS['TL_LANG']['MSC']['originalSize'] . ' ' . ($key + 1)
				];
				ProductHelper::createDownloadItem($this->submission->id, $file, $size);
			}
		}
	}
	
	/**
	 * save price and category for product
	 */
	
	public function additionalTasks()
	{
		// add product categories to isotope category table
		Category::save(deserialize($this->config->orderPages), $this->dc);
		
		// add price to product and isotope price table
		Price::save(['value' => '0.00', 'unit' => 0], $this->dc);
		
		// clear product cache
		\Isotope\Backend::truncateProductCache();
	}
}