<?php

$arrDca = &$GLOBALS['TL_DCA']['tl_page'];

$arrDca['palettes']['regular'] = str_replace('iso_setReaderJumpTo', 'iso_setReaderJumpTo,iso_config', $arrDca['palettes']['regular']);