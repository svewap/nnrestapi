.. include:: ../Includes.txt

.. _kickstarter:

==============
Kickstarter
==============

The fastest way to start your TYPO3 RestApi project 
--------------

The backend-module of nnrestapi offers "Kickstarter" templates, which you can customize and download with a single click.

The idea was to create a helper similar to the TYPO3 "Extension Builder": You can define the vendor- and extension-name, 
click "export" to download the customized extension and then install it as a starting point for your own
extension development.


Creating own kickstarter templates
--------------

You can add your own "Kickstarter"-templates to the backend module.
It would be great to add as many examples and templates as possible in the future, e.g. for a frontend in VueJS, React or Angular.

**So: Please, please...** if you are developing an application that connects to a backend and are using technologies 
like Swift, Kotlin, VueJS, React, Angular or other frameworks: Consider sharing your knowledge and contribute a "Kickstarter"-template
for the community to reduce development time and get started faster.

Creating your own kickstarter-templates is extremely simple:

.. rst-class:: bignums

1.  Build something

    Create a web app, app, plugin, extension, pwa or whatever - in the language and framework you prefer working in.
    Make it connect to your Api.

2.  Make your code customizable 

    If necessary, replace keywords like the vendor name, extension name etc. with the following placeholders. The kickstarter 
    will automatically replace the placeholders with the custom variable set kickstarter form of the backend module:

    You can use the placeholders in the code and your filenames:

    +---------------------------+-------------------+-----------------------------------------------------------+
    | placeholder               | example           | description                                               |
    +===========================+===================+===========================================================+
    | [#ext-ucc#]               | MyExtension       | The UpperCamelCase version von the **extension name**     |
    +---------------------------+-------------------+-----------------------------------------------------------+
    | [#ext-lower#]             | my_extension      | The lower_underscore version of the **extension name**    |
    +---------------------------+-------------------+-----------------------------------------------------------+
    | [#vendor-ucc#]            | Company           | The UpperCamelCase version of the **vendor name**         |
    +---------------------------+-------------------+-----------------------------------------------------------+
    | [#vendor-lower#]          | company           | The lower_underscore version of the **vendor name**       |
    +---------------------------+-------------------+-----------------------------------------------------------+

    You can use these placeholders in your PHP, JavaScript or any other code – and even the filename.
    
    Let's imagine, the user enters ``Acme`` as a vendor-name and ``Foobar`` as extension-name.
    If the user exports an kickstarter-template and your PHP code looks like this:

    .. code-block:: php

        <?php

        namespace [#vendor-ucc#]\[#ext-ucc#]\Domain\Model;

        class Thing extends \[#vendor-ucc#]\[#ext-ucc#]\AbstractSomeThing 
        {
            ...
        }

    Then he will get this result in the downloaded zip-file:

    .. code-block:: php

        <?php

        namespace Acme\Foobar\Domain\Model;

        class Thing extends \Acme\Foobar\AbstractSomeThing 
        {
            ...
        }

    If you don't want to modify your code, but still want certain parts of the code to be customizable,
    you can also define a **list of replacements** in the TypoScript settings. Here is an example for the "VeryBasic" 
    kickstarter template:

    .. code-block:: typoscript

        plugin.tx_nnrestapi.settings.kickstarts {
            verybasic {
                ...
                replace {
                    nng/apitest = [#vendor-lower#]/[#ext-lower#]
                    Nng\\Apitest\\ = [#vendor-ucc#]\\[#ext-ucc#]\\
                    Nng\Apitest\ = [#vendor-ucc#]\[#ext-ucc#]\
                    # Special characters in the key can be encoded with \x-syntax and the hexcode
                    \x22apitest\x22 = "[#ext-lower#]"
                }
            }
        }

3.  ZIP it

    Create a zip-archive of your project that has all folders and files needed to get started. Don't include libraries that get loaded
    during the installation-process, e.g. the ``node_modules`` folder if you are working in VueJS.

4.  Register your kickstarter template

    | To make the template available in the backend module, register the path to your zip or folder.
    **Note:** The zip must be somewhere inside the an extension folder - or the fileadmin. 

    .. code-block:: typoscript

        plugin.tx_nnrestapi.settings.kickstarts {
            mytemplate {
                title = The title
                icon = fas fa-box
                description = The description goes here
                path = EXT:yourext/Resources/Private/Kickstarts/yourpackage.zip
                replace {
                    some_custom_placeholder = [#vendor-lower#]_[#ext-lower#]_sometext
                }
            }
        }


Installing an TYPO3 extension locally when in composer-mode
--------------

If you have installed TYPO3 in composer mode and try to activate an extension which you have installed locally you probably will
get the following error message on the command line:

..  warning::
    Could not find a matching version of package vendorname/extname. Check the package spelling, your version constraint and that the package is available in a stability which matches your minimum-stability (stable).

The reason for this error is that by default composer will always try to find extensions marked as ``stable``. Because your extension is only
installed locally and has no git / version / tag, composer can not determine the state of the extension. 

To solve this problem, you can modify your ``composer.json`` and also allow extensions that are in ``dev`` state. 
Simply add this line to the root of the composer.json:
``"minimum-stability": "dev"`` 

Next, make sure that `IF` a stable version exists, the stable version will be prefered. Without this line, running
``composer update`` the next time will load all TYPO3-extensions in ``dev``-state which can result in many other problems.
Add this line to the composer.json:
``"prefer-stable" : true``


Step-by-step:
================

Here are the steps to install an extension locally without needing to create a repository and registering the extension on packagist:

* Navigate to the root-level of your TYPO3 installation (the place where the ``composer.json`` and ``public``-folder are)
* Create a new folder named ``extensions``
* Copy your TYPO3-extension in to the folder ``extensions``
* Open the ``composer.json`` and add these lines:
  
  .. code-block:: javascript

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
  

* Install your extension using ``composer req vendorname/extname``
