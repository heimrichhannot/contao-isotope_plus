> This bundle abandoned. Please use [heimrichhannot/contao-isotope-extension-bundle](https://github.com/heimrichhannot/contao-isotope-extension-bundle) instead. 

# Contao Isotope Plus

A module that brings some new features to the isotope shopsystem


## Features

### jQuery instead of mootools
- isotope mootools scripts has been removed from templates and replaced by jQuery if possible

### SetQuantity and stock

- if these attributes have values, adding products to the cart or checking out is constrained by the stock left (depending on "setQuantity" if set)
- if the stock reaches 0, shipping_exempt on the product is set to true
- the stock validation (including setting of shipping_exempt) and the usage of sets can be configured in the shop config, the product type and the product (shop config has the lowest priority, product the highest)
- the usage of sets when computing the quantity to remove from the stock of a product can be configured in the shop config
- when removing an order or setting it to a certain status, the stock is decreased (configurable in the shop config)

### Order report & Stock report

- enables to show orders and orderdetails in the front end
- enables to show the product stock in the front end

### ProductFilterPlus

- enables to filter for keywords or by status "shipping_exempt"
- enables sorting in alphabetical order and reverse alphabetical order

### ProductListPlus

- modifies the list, that it can show the filter and sorting results

### ProductListSlick

- render products inside a slick content slider

### CartLink

- a link to the current cart containing a badge showig the current quantity

### DirectCheckout

- a module for directly checking out a certain quantity of some product specified in the module configuration (no need for logging in)

### ProductRanking

- a module for visualizing the development of sales for certain products

### Misc

- adds new possible attributes to products: stock, initialStock, setQuantity, maxOrderSize, releaseDate
- adds a new attribute type: youtube

## Known issues/missing stuff

- stock isn't validated product variants at the moment (products only)
- direct checkout may currently not make use of all of isotope's features and hooks

## ProductEditor
- create and edit isotope products in frontend
- single and multi image products supported
- single image product -> if multiple images are added to upload a product is created for each of the images that shares all other set attributes
- when pdf is uploaded jpg preview is made and set as product image, pdf is set as download element
- for product reader a pdf preview is generated with pdf.js (inserted via hook `parseItems` as `pdfViewer`)
