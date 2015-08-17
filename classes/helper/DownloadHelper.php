<?php
/**
 * Contao Open Source CMS
 * 
 * Copyright (c) 2015 Heimrich & Hannot GmbH
 * @package isotope_plus
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Isotope;


class DownloadHelper extends \Isotope\Isotope
{
	public static function addDownloadsFromProductDownloadsToTemplate($objTemplate)
	{
		$arrDownloads = array();		// array for downloadfiles from db
		$arrFiles = array();			// contains queryresults from db
		$strTable = "tl_iso_download";	// name of download table

		global $objPage;

		$arrOptions = array('order' => 'sorting ASC');

		$arrFiles = \Isotope\Model\Download::findBy('pid', $objTemplate->id, $arrOptions);
		if($arrFiles === null) return $arrDownloads;

		while($arrFiles->next())
		{
			$objModel = \FilesModel::findByUuid($arrFiles->singleSRC);
			if ($objModel === null) {
				if (!\Validator::isUuid($arrFiles->singleSRC)) {
					$objTemplate->text = '<p class="error">'.$GLOBALS['TL_LANG']['ERR']['version2format'].'</p>';
				}
			}
			elseif (is_file(TL_ROOT . '/' . $objModel->path))
			{
				$objFile = new \File($objModel->path, true);

				$file = \Input::get('file', true);
				// Send the file to the browser and do not send a 404 header (see #4632)
				if ($file != '' && $file == $objFile->path) {
					\Controller::sendFileToBrowser($file);
				}

				$arrMeta = \Frontend::getMetaData($objModel->meta, $objPage->language);
				if(empty($arrMeta)) {
					if ($objPage->rootFallbackLanguage !== null) {
						$arrMeta = \Frontend::getMetaData($objModel->meta, $objPage->rootFallbackLanguage);
					}
				}

				$strHref = \Environment::get('request');
				// Remove an existing file parameter (see #5683)
				if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {
					$strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
				}
				$strHref .= ((\Config::get('disableAlias') || strpos($strHref, '?') !== false) ? '&amp;' : '?') . 'file=' . \System::urlEncode($objFile->path);

				$objDownload = new \stdClass();
				$objDownload->id = $objModel->id;
				$objDownload->uuid = $objModel->uuid;
				$objDownload->name = $objFile->basename;
				$objDownload->formedname = preg_replace(array('/_/', '/.\w+$/'), array(' ', ''), $objFile->basename);
				$objDownload->title = specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['download'], $objFile->basename));
				$objDownload->link = $arrMeta['title'];
				$objDownload->filesize = \System::getReadableSize($objFile->filesize, 1);
				$objDownload->icon = TL_ASSETS_URL . 'assets/contao/images/' . $objFile->icon;
				$objDownload->href = $strHref;
				$objDownload->mime = $objFile->mime;
				$objDownload->extension = $objFile->extension;
				$objDownload->path = $objFile->dirname;
				$objDownload->class = 'isotope-download isotope-download-file';

				$objT = new \FrontendTemplate('isotope_download_from_attribute');
				$objT->setData((array) $objDownload);
				$objDownload->output = $objT->parse();

				$arrDownloads[] = $objDownload;
			}
		}
		$objTemplate->downloads = $arrDownloads;
	}
}