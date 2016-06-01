<?php

/**
* Fields
*/
$GLOBALS['TL_LANG']['tl_iso_config']['skipStockValidation'] = array('Bestand nicht validieren', 'Wählen Sie diese Option, wenn der Bestand (sofern am konkreten Produkt vorhanden) NICHT bei verschiedenen Aktionen validiert werden soll (z. B. Hinzufügen zum Warenkorb, Bestellung, ...). Sie können diese Option an einem Produkttyp oder einem Produkt überschreiben.');
$GLOBALS['TL_LANG']['tl_iso_config']['skipStockEdit'] = array('Bestand bei Bestellung nicht verändern', 'Wählen Sie diese Option, wenn der Bestand (sofern am konkreten Produkt vorhanden) NICHT beim Bestellen nicht verändert werden soll. Sie können diese Option an einem Produkttyp oder einem Produkt überschreiben.');
$GLOBALS['TL_LANG']['tl_iso_config']['skipSets'] = array('Sets ignorieren', 'Wählen Sie diese Option, wenn die Berechnung der Artikelmenge, die bei Bestellung vom Bestand abgezogen wird, NICHT auf Basis eines vergebenen Sets passieren soll.');
$GLOBALS['TL_LANG']['tl_iso_config']['skipExemptionFromShippingWhenStockEmpty'] = array('Produkte mit leerem Bestand nicht vom Versand ausschließen', 'Wählen Sie diese Option, wenn ein Produkt, dessen Bestand auf 0 oder niedriger sinkt, NICHT vom Versand ausgeschlossen werden soll. Sie können diese Option an einem Produkttyp oder einem Produkt überschreiben.');
$GLOBALS['TL_LANG']['tl_iso_config']['stockIncreaseOrderStates'] = array('Bestandserhöhende Bestellstatus (nur Backend)', 'Wählen Sie hier die Status aus, deren Aktivierung zu einer Erhöhung des Bestands führen (bspw. Neu -> Storniert). Diese Einstellung wird nur im Backend ausgewertet.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_config']['stock_legend'] = 'Bestand';