<?php

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_product']['initialStock']            = ['Anfangsbestand', 'Geben Sie hier den Anfangsbestand des Produkts ein.'];
$GLOBALS['TL_LANG']['tl_iso_product']['stock']                   = ['Bestand', 'Geben Sie hier den Bestand des Produkts ein.'];
$GLOBALS['TL_LANG']['tl_iso_product']['releaseDate']             = ['Erscheinungsdatum', 'Geben Sie hier Erscheinungsdatum des Produkts ein.'];
$GLOBALS['TL_LANG']['tl_iso_product']['maxOrderSize']            = ['Maximale Bestellmenge', 'Geben Sie hier die maximale Bestellmenge ein.'];
$GLOBALS['TL_LANG']['tl_iso_product']['setQuantity']             = ['Set', 'Geben Sie hier ein, wie viele Artikel zusammen im Set verkauft werden.'];
$GLOBALS['TL_LANG']['tl_iso_product']['overrideStockShopConfig'] = [
	'Bestandskonfiguration überschreiben',
	'Wählen Sie diese Option, um die Konfiguration des Bestands, die Sie im Produkttyp bzw. in der aktuellen Shop-Konfiguration gesetzt haben, zu überschreiben.'
];
$GLOBALS['TL_LANG']['tl_iso_product']['jumpTo']                  = ['Weiterleitungsseite', 'Wählen Sie hier die Weiterleitungsseite aus.'];
$GLOBALS['TL_LANG']['tl_iso_product']['addedBy']                 = ['Hinzugefügt durch', 'Tragen Sie hier ein, wer den Artikel hochgeladen hat.'];
$GLOBALS['TL_LANG']['tl_iso_product']['tag']                     =
	['Schlagworte', 'Geben Sie bitte die Begriffe einzeln ein. (Kommas dienen NICHT zur Trennung der Begriffe.)'];
$GLOBALS['TL_LANG']['tl_iso_product']['createMultiImageProduct'] = [
	'Alle Bilder zu einem Produkt hinzufügen',
	'Wählen Sie diese Option, wenn alle Bilder aus dem Bildupload zu einem Produkt hinzugefügt werden sollen.'
];
$GLOBALS['TL_LANG']['tl_iso_product']['downloadCount']           = ['Downloads', ''];
$GLOBALS['TL_LANG']['tl_iso_product']['relevance']               = ['Beliebtheit', ''];
$GLOBALS['TL_LANG']['tl_iso_product']['licence']                 = [
	'Lizenz',
	'Wählen Sie hier die Lizenz aus, die für die Aufnahme gilt.',
	\HeimrichHannot\IsotopePlus\ProductHelper::ISO_LICENCE_FREE      => 'frei',
	\HeimrichHannot\IsotopePlus\ProductHelper::ISO_LICENCE_COPYRIGHT => 'Copyright angeben',
	\HeimrichHannot\IsotopePlus\ProductHelper::ISO_LICENCE_LOCKED    => 'geschützt (lizenzpflichtig)',
];
$arrLang['copyright']                                            = ['Copyright', 'Bitte geben Sie einen Copyright an.'];
$arrLang['uploadedFiles'] = [
	'Bild hochladen',
	'Fügen Sie hier Bilder hinzu, die für den Upload genutzt werden sollen. Wenn Sie mehrere Bilder auswählen, wird für jedes Bild ein eigener Artikel erstellt. Die Artikel besitzen die gleichen Attribute.',
	'Datei(en) auswählen'
];

$arrLang['uploadedDownloadFiles'] = [
	'Downloadelemente hochladen',
	'Fügen Sie hier Dateien hinzu, die als Downloadelemente für den Artikel genutzt werden sollen.',
	'Datei(en) auswählen'
];