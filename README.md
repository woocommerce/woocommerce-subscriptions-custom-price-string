# WooCommerce Subscriptions - Custom Price String

WooCommerce Subscriptions - Custom Price String is an experimental mini-extension for [WooCommerce Subscriptions](http://woocommerce.com/products/woocommerce-subscriptions/) that allows you to customize the following default strings from WooCommerce and WooCommerce Subscriptions:

- Simple product "price string"

- Variable product "price string"

- Subscription product "price string"

- Subscription Variation "price string"

- Variable product "From string"


## Installation

To install:

1. Download the latest version of the plugin [here](https://github.com/Prospress/woocommerce-subscriptions-custom-pricestring/archive/master.zip)
1. Go to **Plugins > Add New > Upload** administration screen on your WordPress site
1. Select the ZIP file you just downloaded
1. Click **Install Now**
1. Click **Activate**

## How-to

### Set Product Custom Pice String for a simple (subscription or not) product

Edit the product and go to its General tab. There you'll find a new field named "Custom price string". By default it will display the current price string (f.e: "$10 on the 1st of each month with a 30-day free trial"). Use this field to change this string to whatever you want (f.e: "$10 each month").

![Custom simple Product price string](https://github.com/Prospress/woocommerce-subscriptions-custom-pricestring/raw/master/includes/imgs/custom_pricestring_simple.png)

### Set Product Custom Pice String for a variable (subscription or not) product

Edit the product and go to its Variations tab. Expand any of the existing variations and there you'll find the "Custom price string". Again, you can use this field to change this string to whatever you want (f.e: "$10 each month"). Each variation can have a different custom price string.

![Custom variation price string](https://github.com/Prospress/woocommerce-subscriptions-custom-pricestring/raw/master/includes/imgs/custom_pricestring_variable.png)

### Set Product Custom From String for a variable (subscription or not) product

By default, on each variable product page (frontend), a "From" sting is displayed, showing the minimal starting price of the product. If you edit a variable product and go to its Variations tab, you'll find a new field named "Custom Fom String" that you can can use to customize this string (f.e: "Starting at $10"). 

![Custom variable product From string](https://raw.githubusercontent.com/Prospress/woocommerce-subscriptions-custom-pricestring/master/includes/imgs/custom_from_string.png)
* _NOTE: The 'From:' custom price string is located in the 'Advanced' tab for WC versions 3.3.5 and before._

![Custom variable product From string WC 3.4+](https://raw.githubusercontent.com/Prospress/woocommerce-subscriptions-custom-pricestring/master/includes/imgs/custom_from_string_wc-3.4+.png)
* _NOTE: The 'From:' custom price string is located in the 'Variations' tab for WC versions 3.4+._

### Result on the Frontend
![Product with custom From and Price string](https://raw.githubusercontent.com/Prospress/woocommerce-subscriptions-custom-pricestring/master/includes/imgs/frontend.png)

### Updates

To keep the plugin up-to-date, use the [GitHub Updater](https://github.com/afragen/github-updater).

## Reporting Issues

If you find an problem or would like to request this plugin be extended, please [open a new Issue](https://github.com/Prospress/woocommerce-subscriptions-custom-pricestring/issues/new).

---

<p align="center">
	<a href="https://prospress.com/">
		<img src="https://cloud.githubusercontent.com/assets/235523/11986380/bb6a0958-a983-11e5-8e9b-b9781d37c64a.png" width="160">
	</a>
</p>
