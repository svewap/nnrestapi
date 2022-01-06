.. include:: ../Includes.txt

.. _configuration_typoscript:

============
TypoScript Setup
============

Configuring the TYPO3 Rest Api in the TypoScript Setup
-------------------------------

All of the following settings are configured at 

.. code-block:: typoscript

    plugin.tx_nnrestapi {
        settings {
            // ... HERE!
        }
    }

accessGroups
""""""""""""""
.. container:: table-row

   Property
        accessGroups
   Data type
        array
   Description
        Allows the centralized definition of access groups to be used with the ``@Api\Access("config[name]")`` annotation. 
        Can be a comma separated list of users, see :ref:`this section <access>` for more examples.
        ::

            plugin.tx_nnrestapi.settings.accessGroups {

                // use @Api\Access("config[example1]")
                example1 = fe_users[3,2]

                // use @Api\Access("config[example2]")
                example1 = fe_users[david], fe_groups[1], ip_users[5.10]
            }

   Default
        no groups defined

apiController
""""""""""""""
.. container:: table-row

   Property
        apiController
   Data type
        string
   Description
        Defines which Controller takes care of resolving the class and method that should be called.
        If you want to take complete control of the delegation logic, dependency injection etc., 
        replace the default class with your own class.

        Your class should extend the ``Nng\Nnrestapi\Controller\AbstractApiController``. It will be instanciated 
        by the PageResolver-MiddleWare ``Nng\Nnrestapi\Middleware\PageResolver`` which will call the
        ``indexAction`` in your class. 

        ::

            plugin.tx_nnrestapi.settings.apiController = My\Extension\Controller\ApiController

   Default
        Nng\Nnrestapi\Controller\ApiController


.. _configuration_fileuploads:

fileUploads
""""""""""""""
.. container:: table-row

   Property
        fileUploads
   Data type
        array
   Description
        Define upload-paths that can be used in the ``@Api\Upload("config[key]")`` annotation.

        Read :ref:`how to use this annotation <annotations_upload>` or find out how to 
        :ref:`create a custom path resolver <upload_custom_pathresolver>` to dynamically set 
        the upload path.

        ::

            plugin.tx_nnrestapi.settings.fileUploads {

                // use @Api\Access("config[myUploadConfig]") at your method
                myUploadConfig {

                    // the path to use, if no other criteria below meet
                    defaultStoragePath = 1:/myfolder/

                    // optional: custom method to resolve the upload-path
                    pathFinderClass = My\Extension\Helper\UploadPathHelper::getUploadPath

                    // target-path for file, file-0, file-1, ... from multipart/form-data
                    file = 1:/myfolder/files/

                    // target-path for image, image-0, image-1, ... from multipart/form-data
                    image = 1:/myfolder/images/
                }
            }

   Default
        Nng\Nnrestapi\Controller\ApiController


globalDistillers
""""""""""""""
.. container:: table-row

   Property
        globalDistillers
   Data type
        array
   Description
        The main purpose of a "Distiller" is to reduce the amount of data returned to the frontend
        by removing certain field from a model after it is converted to a JSON. 

        This can be done :ref:`on a per-method basis <annotations_distiller>` or globally for 
        a certain Model type by setting the ``globalDistillers`` in the TypoScript.

        Find out :ref:`how to use global distillers <annotations_distiller_global>`.

        ::

            plugin.tx_nnrestapi.settings.globalDistillers {

                // use the model class name as a key here
                My\Extension\Domain\Model\Name {
                    
                    // "exclude" will keep all fields EXCEPT the ones listed here
                    exclude = pid, other_field

                    // "include" will remove ALL fields EXCEPT the ones listed here
                    include = uid, title, bodytext

                    // "flattenFileReferences" will reduce the FALs to their publicUrl
                    flattenFileReferences = 1
                }
            }

   Default
        parent is excluded for TYPO3\CMS\Extbase\Domain\Model\Category


insertDefaultValues
""""""""""""""
.. container:: table-row

   Property
        insertDefaultValues
   Data type
        array
   Description
        Define default values to be set when a new model is created and passed to your endpoint.
        
        This is very useful, when you are using dependency injection (DI) to automatically create
        a new Model in your ``POST``-method as :ref:`described in this section <examples_article_newmodel>`

        Use-case would be: Every new entry created in the frontend should be inserted in a given 
        SysFolder in the backend. This can be accomplished by setting a default ``pid`` for the 
        model in the TypoScript:

        These values will be overridden, if the frontend sets a value for the field.

        ::

            plugin.tx_nnrestapi.settings.insertDefaultValues {

                // use the model-name as a key
                My\Extension\Domain\Model\Name {

                    // define default value for a new model
                    pid = 6

                    // you can even set default SysCategories
                    categories {
                        0 = 1
                        1 = 2
                    }
                }
            }

   Default
       none defined

kickstarts
""""""""""""""
.. container:: table-row

   Property
        kickstarts
   Data type
        array
   Description
        Allows adding templates to the REST Api kickstarter-examples.

        These can be accessed in the "RestApi" backend module by clicking on the tab "Kickstarter".

        Find out :ref:`how to create your own templates <kickstarter>` for the Kickstarter and 
        replace / customize variables in the templates during the download.

        ::

            plugin.tx_nnrestapi.settings.kickstarts {
                myexample {

                    // title and description for the list view
                    title = A frontend in React
                    description = Example React frontend connection with the TYPO3 Rest Api

                    // path can be a zip or a folder. Must be inside an EXT-folder or fileadmin!
                    path = EXT:myextension/Resources/Private/Kickstarts/react.zip

                    // list of texts to replace in source-codes
                    replace {
                        my/extname = [#vendor-lower#]/[#ext-lower#]
                    }
                }
            }

   Default
        see TypoScript
        

.. _configuration_locaization:

localization
""""""""""""""
.. container:: table-row

   Property
        localization
   Data type
        array
   Description
        Controls how to handle translations when retrieving data from the database. 
        By default, localization is **NOT** enabled. This can be changed by setting 
        ``enabled = 1``.

        While checking, which language was requested, the nnrestapi will evaluate the
        URL path (e.g. ``../en/api/endpoint``), the ``?L=...`` parameter in the URL and 
        the header sent by the frontend-application. Use ``languageHeader`` to define
        which headers of the request to take into consideration. 

        Read :ref:`how localization is handled <localization>` in the TYPO3 Rest Api.

        ::

            plugin.tx_nnrestapi.settings.localization {
                
                // enable the localization (default is 0 / off)
                enabled = 1

                // which headers to check for language requested by frontend
                languageHeader = x-locale, accept-language
            }

   Default
        | enabled = 0
        languageHeader = x-locale, accept-language


response.headers
""""""""""""""
.. container:: table-row

   Property
        response.headers
   Data type
        array
   Description
        Allows you to add, modify or remove the default headers sent by the TYPO3 Restful Api.

        You can define simple key/value pairs here that will be sent with every response.
        All headers are sent without parsing or modification, with one exception: The 
        header for ``Access-Control-Allow-Origin``:

        As the ``Access-Control-Allow-Origin`` sent by PHP usually can not handle wildcards in
        parts of the URL (e.g. ``*.mysite.com``), the list of URLs for this header are parsed
        by the nnrestapi.

        If one of the given patterns matches the ``HTTP_ORIGIN`` or ``HTTP_REFERER``, the header
        will be set to the exact domain that the request was sent from. This allows setting 
        ``Access-Control-Allow-Credentials: true`` which can be useful in cross-domain requests.

        Find out, which :ref:`Default headers <response_headers_default>` are sent and how to 
        :ref:`modify and add response headers <responses_headers>`.

        ::

            plugin.tx_nnrestapi.settings.response.headers {
                
                // Restrict CORS to certain domains
                Access-Control-Allow-Origin = localhost:8090, *.mysite.com, https://www.otherdomain.de
            }

        Here is an example list of patterns:

        =============================== =================================== ==========================
        pattern                         Example ORIGIN / REFERER            matched?
        =============================== =================================== ==========================
        :samp:`localhost`               | http://localhost:8090             | 🟢 yes
                                        | http://localhost                  | 🟢 yes
                                        https://localhost                   🟢 yes
        :samp:`localhost:*`             | http://localhost:8090             | 🟢 yes
                                        http://localhost                    🟢 yes
        :samp:`localhost:8010`          | http://localhost:8010             | 🟢 yes
                                        | http://localhost:8090             | 🔴 no
                                        http://localhost                    🔴 no
        :samp:`*.mysite.com`            | http://www.mysite.com             | 🟢 yes
                                        | https://www.mysite.com            | 🟢 yes
                                        | http://api.mysite.com             | 🟢 yes
                                        http://api.mysite.de                🔴 no
        :samp:`https://*.mysite.com`    | http://www.mysite.com             | 🔴 no
                                        | https://www.mysite.com            | 🟢 yes
                                        | http://api.mysite.com             | 🔴 no
                                        https://api.mysite.com              🟢 yes
        :samp:`*`                       any                                 🟢 yes
        =============================== =================================== ==========================

   Default
        | Access-Control-Allow-Origin = *


timeZone
""""""""""""""
.. container:: table-row

   Property
        timeZone
   Data type
        string
   Description
        Override the time zone settings from TYPO3 or the server when processing the request.

        Try ``UTC`` or ``Europe/Berlin`` here, if you are experiencing a one-hour offset when using 
        JavaScript datepicker components in the frontend.

        If empty will use the time zone settings from the server or as defined in the LocalConfiguration 
        under ``[SYS][phpTimeZone]``.

        You can find a list of time zones `on this website <https://www.php.net/manual/de/timezones.php>`__.

        ::

            plugin.tx_nnrestapi.settings.timeZone = UTC

   Default
        empty
