# Very Basic RestApi Extension

This example extension comes with a very simple ``Entry`` Domain Model and an Endpoint that
can create, read, update and delete Entries.

It is a great base to get started "from scratch" when developing your own TYPO3 RestApi.

## Installation

* In the Kickstarter: Enter a Vendor-Name and Extension-Name
* Hover over the package and click "Download". The browser will download the extension as a ZIP file.
* Install the extension in TYPO3 using the Extension Manager. If you are not in the composer mode, you can upload the ZIP-file directly in the backend.
* Include the static TypoScript templates in your site root
* Switch to the RestApi backend module to test the new endpoints

## Using a composer installation?

Here are the steps to install an extension locally without needing to create a repository and packagist entry:

* Navigate to the root-level of your TYPO3 installation (the place where the ``composer.json`` and ``public``-folder are)
* Create a folder named ``extensions``
* Copy your TYPO3-extension in to the folder ``extensions``
* Modify the ``composer.json`` and add these lines:
  ```
  {
  	"repositories": [
		{
			"type": "path",
			"url": "extensions/*",
			"options": {
			  "symlink": true
			}
		}
	],
	"minimum-stability": "dev",
	"prefer-stable" : true,
	...
  }
  ```
* Install your extension using ``composer req vendorname/extname``
