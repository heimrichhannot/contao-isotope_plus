# Change Log
All notable changes to this project will be documented in this file.

## [1.7.0] 2017-01-12

### Added
- support own uploadField for download items
- `licence`, `addedBy` and `copyright` are added to tl_files from submission

## [1.6.8] 2017-11-29

### Added
- field dependend uploadFolder 

### Fixed
- refactor pdf preview generation and file handling

## [1.6.7] 2017-11-29

### Changed
- using Ghostscript to create PDF preview 
- switched filetype for preview to png (better display of font)

## [1.6.6] 2017-11-24

### Added
- support for PDF products
- hook to display pdf preview in template (`parseItems` hook from formhybrid_list ModuleReader)

## [1.6.5] 2017-11-09

### Changed
- refactored product editor

## [1.6.4] 2017-11-06

### Added
- directcheckout supports shipping method type `groups`

### Changed
- refactored product editor

## [1.6.3] 2017-10-25

### Added
- added AGB and consent field to directcheckout


## [1.6.2] 2017-10-25

### Fixed
- check if file has valid type

## [1.6.1] 2017-10-25

### Changed
- deleted unnecessary files

## [1.6.0] 2017-10-25

### Added
- refactored product edit module

## [1.5.10] 2017-05-29

### Fixed
- OrderHistoryPlus Address Model not found

## [1.5.9] 2017-04-26

### Added
- basic support for frontend product creation

## [1.5.8] 2016-11-22

### Added
- handling of product fields for direct checkout according to the product_mode and the set configuration
