<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_module'];

/**
 * Fields
 */
$arrLang['iso_description']                                  =
	['Beschreibung', 'Geben Sie hier eine Beschreibung des Moduls ein.'];
$arrLang['addSlick']                                         =
	['Slick-Slider verwenden', 'Wählen Sie diese Option, um die Produktliste als Slick-Slider anzuzeigen.'];
$arrLang['iso_show_all_orders']                              = [
	'Alle Bestellungen anzeigen',
	'Wählen Sie diese Option, wenn die anzuzeigenden Bestellungen nicht nur auf die des aktuell eingeloggten Mitglieds beschränkt sein sollen.',
];
$arrLang['iso_direct_checkout_product_mode']                 =
	['Produktmodus', 'Wählen Sie aus, ob ein bestimmtes Produkt oder ein Produkt eines speziellen Produkttyps auswählbar sein soll.'];
$arrLang['iso_direct_checkout_product_mode']['product']      = 'Produkt';
$arrLang['iso_direct_checkout_product_mode']['product_type'] = 'Produkttyp';
$arrLang['iso_direct_checkout_products']                     =
	['Produkte', 'Wählen Sie die Produkte aus, die über die Direktbestellung bestellt werden können.'];
$arrLang['iso_direct_checkout_product']                      =
	['Produkt', 'Wählen Sie ein Produkt aus, das über die Direktbestellung bestellt werden kann.'];
$arrLang['iso_direct_checkout_product_types']                = [
	'Produkttypen',
	'Wählen Sie die Produkttypen aus, denen ein Produkt jeweils zugeordnet sein muss, um über die Direktbestellung bestellt werden zu können.',
];
$arrLang['iso_direct_checkout_product_type']                 =
	['Produkttyp', 'Wählen Sie den Produkttypen aus, dem ein Produkt zugeordnet sein muss, um über die Direktbestellung bestellt werden zu können.'];
$arrLang['iso_direct_checkout_listingSortField']             =
	['Auswahlfeld', 'Wählen Sie hier aus, welches Feld zur Auswahl eines Produkt aus des gewählten Produkttyps'];
$arrLang['iso_direct_checkout_listingSortDirection']         =
	['Sortierrichtung', 'Wählen Sie Sortierrichtung aus, die für das Auswahlfeld gilt.'];
$arrLang['iso_price_filter']                                 =
	['Produktpreisfilter', 'Wählen Sie eine Option aus, um eine Filterung bzgl. des Produktpreises zu aktivieren.'];
$arrLang['iso_price_filter']['paid']                         = 'Nur kostenpflichtig';
$arrLang['iso_price_filter']['free']                         = 'Nur kostenlos';
$arrLang['iso_producttype_filter']                           =
	['Produkttypfilter', 'Wählen Sie eine Option aus, um eine Filterung bzgl. des Produkttyps zu aktivieren.'];
$arrLang['iso_use_notes']                                    =
	['Bemerkungsfeld hinzufügen', 'Wählen Sie eine Option aus, um dem Bestellformular ein Bemerkungsfeld hinzuzufügen.'];
$arrLang['iso_note']                                         = ['Bemerkungen', ''];
$arrLang['iso_editableCategories']                                  =
	['Produkttyp', 'Wählen Sie hier den Produkttypen aus, der dem erstellten Produkt zugewiesen wird.'];
$arrLang['iso_productCategory']                              =
	['Kategorien', 'Wählen Sie hier die Kategorien aus, der dem erstellten Produkt zugewiesen werden.'];
$arrLang['iso_exifMapping']                                  = [
	'EXIF-Werte zuweisen',
	'Verknüpfen Sie hier, welche EXIF-Werte in welchem Datenbankfeld gespeichert werden sollen.',
	'iso_exifMapping_exifTag'    => ['EXIF-Tag', ''],
	'iso_exifMapping_customTag'  => ['Weiterer EXIF-Tag', ''],
	'iso_exifMapping_tableField' => ['Tabellenfeld', ''],
];


$arrLang['iso_useAgb'] = ['Agb-Feld verwenden', 'Wählen Sie diese Option, dem Formular ein Feld für die Bestätigung der AGB hinzugefügt werden soll.'];
$arrLang['iso_agbText'] = ['Agb-Text', 'Tragen Sie hier den Text ein, der für das Agb-Feld genutzt werden soll.'];

$arrLang['iso_useConsent'] = ['Einverständniserklärung-Feld verwenden', 'Wählen Sie diese Option, dem Formular ein Feld für die Bestätigung der Einverständniserklärung hinzugefügt werden soll.'];
$arrLang['iso_consentText'] = ['Einverständniserklärung-Text', 'Tragen Sie hier den Text ein, der für das Einverständniserklärung-Feld genutzt werden soll.'];

$arrLang['iso_useFieldsForTags'] = ['Felder in Tag-Feld zusammenfassen', 'Wählen Sie diese Option, wenn verschiedene Felder zu einem tagsinput-Feld hinzugefügt werden sollen.'];
$arrLang['iso_tagField'] = ['Tag-Feld', 'Wählen Sie hier das tagsinput-Feld zu dem die verschiedenen Felder hinzugefügt werden sollen.'];
$arrLang['iso_tagFields'] = ['Felder', 'Wählen Sie hier die Felder, die dem tagsinput-Feld hinzugefügt werden sollen.'];


$arrLang['iso_addImageSizes'] = ['Bildgrößen angeben', 'Wählen Sie diese Option, wenn das Artikelbild in verschiedenen Größen abgespeichert werden soll.'];
$arrLang['iso_imageSizes'] = ['Bildgrößen', 'Geben Sie hier die verschiedenen Bildgrößen des Artikelbildes an.'];
$arrLang['iso_imageSizes']['width'] = ['Weite', 'Geben Sie hier die Bildweite an.'];
$arrLang['iso_imageSizes']['height'] = ['Höhe', 'Geben Sie hier die Bildhöhe an.'];
$arrLang['iso_useUploadsAsDownload'] = ['Artikelbild als Download verwenden', 'Wählen Sie diese Option, wenn die Artikelbilder als Download in der Detailansicht ausgegeben werden sollen.'];
$arrLang['iso_uploadFolder'] = ['Upload-Ordner', 'Wählen Sie hier den Ordner aus, in den die hochgeladenen Bilder abgespeichert werden sollen.'];

$arrLang['iso_useFieldDependendUploadFolder'] = ['Feldabhängiger Upload-Ordner', 'Wählen Sie diese Option, wenn der Upload-Ordner von einem Feld-Wert im Formular abhängen soll.'];
$arrLang['iso_fieldForUploadFolder'] = ['Feld', 'Wählen Sie hier das Feld, das zur Bestimmung des Upload-Ordners genutzt werden soll.'];

/**
 * Legends
 */
$arrLang['creator_legend'] = 'Produktersteller';
$arrLang['product_legend'] = 'Produkteinstellungen';


/**
 * Misc
 */
$arrLang['guestOrder']         = 'Gastbestellung';
$arrLang['notExistingAnyMore'] = 'Existiert nicht mehr';