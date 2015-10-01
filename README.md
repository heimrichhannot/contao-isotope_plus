# Contao Isotope Plus

A module that brings some new features to the isotope shopsystem


## Features

### Set and stock

- if these attributes have values, adding products to the cart or checking out is constrained by stock left (depending on "set" if set)
- the stock validation can be configured in the shop config, the product type and the product (shop config has the lowest priority, product the highest)
- when removing an order or setting it to a certain status (configurable in the shop config)

### Order report & Stock report

- enables to show orders and orderdetails in the front end
- enables to show the product stock in the front end

### ProductFilterPlus

- enables to filter for keywords or by status "shipping_exempt"
- enables sorting in alphabetical order and reverse alphabetical order

### ProductListPlus

- modifies the list, that it can show the filter and sorting results

### CartLink

- a link to the current cart containing a badge showig the current quantity

### DirectCheckout

- a module for directly checking out a certain quantity of some product specified in the module configuration (no need for logging in)

### Misc

- adds new possible attributes to products: stock, initialStock, set, maxOrderSize, releaseDate
- adds a new attribute type: youtube

## Known issues/missing stuff

- stock isn't validated product variants at the moment (products only)
- direct checkout may currently not make use of all of isotope's features and hooks