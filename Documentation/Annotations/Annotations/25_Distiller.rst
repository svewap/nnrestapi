.. include:: ../Includes.txt

.. _annotations_distiller:

============
@Api\\Distiller
============

Dehydrate the JSON-result before returning it to the client
---------

By default, any Array, Object, Model or ObjectStorage returned by your endpoint method will 
be recursively converted to an array and then sent as JSON response to the client.

In certain cases you might want to remove certain fields from the JSON, e.g. to protect 
sensitive data to be passed to the frontend or to reduce the complexitiy or depth of the returned
JSON.

Writing a custom Distiller
~~~~~~~~

By defining a custom ``Distiller`` this is pretty simple. The basic syntax is:

.. code-block:: php

   @Api\Distiller( \My\Extension\Distiller\Name::class )

Let's write an example Distiller that removes the password from the resulting JSON before sending it
to the frontend. 

First, set a Distiller in your REST Api Endpoint using the ``@Api\Distiller()`` annotation:

.. code-block:: php

   <?php

   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;

   class Example
   {
      /**
       * @Api\Distiller( \My\Extension\Distiller\RemovePassword::class )
       * @Api\Access("public")
       *
       * @return array
       */
      public function getIndexAction() 
      {
         $user = $this->getUserExample();
         return $user;
      }

   }

The write your custom distiller. Note that your custom distiller should extend the ``Nng\Nnrestapi\Distiller\AbstractDistiller``.
By default, the method ``process`` will be called and the ``$data`` passed as reference. The ``process`` method can manipulate
the data by setting or removing elements or keys from the array:

.. code-block:: php

   <?php

   namespace My\Extension\Distiller;

   use Nng\Nnrestapi\Distiller\AbstractDistiller;

   class RemovePassword extends AbstractDistiller {

      /**
       * @return void
       */
      public function process( &$data = [] ) {
         unset($data['password']);
      }

   }


How to only keep a few keys
~~~~~~~~

If you are removing more keys from the JSON than keeping them, consider simply using the ``$keysToKeep`` property
which you can set in your custom Distiller. The ``Nng\Nnrestapi\Distiller\AbstractDistiller`` will check, if
a Distiller has this property set and then autoatically remove alle keys from the JSON that are not defined in the
array.

.. code-block:: php

   <?php

   namespace My\Extension\Distiller;

   use Nng\Nnrestapi\Distiller\AbstractDistiller;

   class RemoveAlmostEverything extends AbstractDistiller {

      /**
       * @var array
       */
      public $keysToKeep = ['uid', 'username'];

   }


Defining Global Distillers - by the Model-type
~~~~~~~~

In many cases you will probably want to define a Distiller based on the Model-type.
An example could be: You want to pass the `publicUrl` of a `SysFileReference`, but don't
need the fields ``crop``, ``uidLocal`` etc. in your frontend.

This can be accomplished by defining a per-model Distiller in the TypoScript setup for
``globalDistillers``. Use the classname of the Model as a key and define how the Model
should be parsed:

.. code-block:: typoscript

   plugin.tx_nnrestapi.settings {

      # Fields to remove from Model when converting to array
      globalDistillers {

         My\Extension\Extbase\Domain\Model\Example {
            exclude = parent, mktime, crdate
         }

         TYPO3\CMS\Extbase\Domain\Model\FileReference {
            exclude = uidLocal, crop, publicUrl, type
         }
      }
   }

Here is an overview of the available options for every class

Excluding certain fields
""""""""

Use ``exclude`` to define fields that show be removed from the JSON for the Model:

.. code-block:: typoscript

   plugin.tx_nnrestapi.settings.globalDistillers {
      My\Extension\Extbase\Domain\Model\Example {
         exclude = parent, mktime, crdate
      }
   }

Only including certain fields
""""""""

If you have more fields you want to remove than include, simply use ``include`` to define 
all fields that show **NOT** be removed from the JSON. All other fields will be removed
automatically.

.. code-block:: typoscript

   plugin.tx_nnrestapi.settings.globalDistillers {
      My\Extension\Extbase\Domain\Model\Example {
         include = uid, title, image
      }
   }

.. tip::

   When you ``PUT``, ``POST`` or ``PATCH`` your Model to the TYPO3 RestApi, you don't have to pass the
   complete object with all fields. The RestApi will automatically merge the fields passed in the request
   with the existing properties of the Model. This is why it is fine, to only include fields that you
   really need to edit or modify in your frontend-application.

   **Example**: If you only want to change the ``title`` of an existing Model, it would be enough to
   only pass the title in the payload. All other properties and relations will stay untouched when
   the data from the JSON is merged in the existing Model:

   .. code-block:: json

      // PUT or PATCH to /api/entry/{uid}
      {"title":"New title"}



Flattening SysFileReferences (FAL)
""""""""

By default, a FAL will be converted to an array containing fields like ``publicUrl``, ``title``, ``description``,
``crop`` etc. If you only need the path to the SysFile and not all these fields, you can set ``flattenFileReferences = 1``
on the top level of the destiller configuration for your Model. It will be recursively applied to all child-relations.

.. code-block:: typoscript

   plugin.tx_nnrestapi.settings.globalDistillers {
      My\Extension\Extbase\Domain\Model\Example {
         flattenFileReferences = 1
      }
   }

Without the above configuration, a ``sys_file_reference`` would be converted to this in the JSON:

.. code-block:: json

   {"image":{"publicUrl":"path/to/file.jpg", "title":"...", "crop":"..."}}

By setting ``flattenFileReferences = 1`` it deflates the FileReference and only returns the ``publicUrl``:

.. code-block:: json

   {"image":"path/to/file.jpg"}

**Note:** In both cases, if there is no ``sys_file_reference`` attached to the Model you will get a ``NULL``
in the JSON:

.. code-block:: json

   {"image":NULL}