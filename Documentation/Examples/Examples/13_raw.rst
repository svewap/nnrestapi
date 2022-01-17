.. include:: ../Includes.txt

.. _examples_raw:

============
Raw Content data
============

Retrieving the raw, unrendered data of content-elements
------------

In contrast to the example of :ref:`how to retrieve rendered content elements <examples_contentelements>` 
let's create an endpoint in our TYPO3 Restful Api that returns the "raw" data from the table ``tt_content`` 
for a given ``uid``:

.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   /**
    * @Api\Endpoint()
    */
   class Content extends AbstractApi 
   {
     /**
      * @Api\Access("public")
      * @Api\Localize()
      * 
      * @param int $uid
      * @return array
      */
      public function getRawAction( int $uid = null )
      {
         // Get raw data from table tt_content and include FAL-relations
         $data = \nn\t3::Content()->get( $uid, true );
         return $data;
      }
   }
   
To see the results, send a ``GET`` request to:

.. code-block:: php

   https://www.mysite.com/api/content/raw/{uid}


**Example result** of what you get:

.. code-block:: json

   {
      "uid": 1,
      "pid": 2,
      "header": "My title",
      "bodytext": "<p>This is <a href=\"t3://page?uid=6\">link to a page</a></p>",
      "assets": [
         "uid": 14,
         "publicUrl": "fileadmin/path/to/image.jpg"
      ],
      ...
   }

Parsing t3://page TypoLinks in the bodytext
------------

If you take a close look at the field ``bodytext`` you will notice, that the typolink was not rendered.
This usually happens in the fluid template when using the ``f:format.html``-ViewHelper.

To parse the links and convert the ``t3://page?uid=...`` syntax in to "real" URLs you can add this to 
your method. You will have to repeat this for every field using the RTE (Rich Text Editor / ckeditor):

.. code-block:: php

   // Get raw data from table tt_content and include FAL-relations
   $data = \nn\t3::Content()->get( $uid, true );

   // Parse links in bodytext, convert t3://page to normal link
   $data['bodytext'] = \nn\t3::Tsfe()->cObj()->parseFunc($data['bodytext'], [], '< lib.parseFunc_RTE');


Now the result will look like this:

.. code-block:: json

   {
      "bodytext": "<p>This is <a href=\"/thepage">linked to a page</a></p>",
      ...
   }


Creating absolute links for TypoLinks in the bodytext
------------

Let's guess: Probably the next thing you want to do is create **absolute URLs in the bodytext**, right?
This is especially helpful, when you are developing a cross-domain application that needs to link to 
content on an external server.

To get this done, the nnrestapi comes with a special ``lib.parseFunc_nnrestapi`` configuration. Simply
replace ``lib.parseFunc_RTE`` with ``lib.parseFunc_nnrestapi``:

.. code-block:: php

   // Get raw data from table tt_content and include FAL-relations
   $data = \nn\t3::Content()->get( $uid, true );

   // Parse links in bodytext, create absolute links
   $data['bodytext'] = \nn\t3::Tsfe()->cObj()->parseFunc($data['bodytext'], [], '< lib.parseFunc_nnrestapi');

Now the result will have **absolute URLs** instead of relative paths and look like this:

.. code-block:: json

   {
      "bodytext": "<p>This is <a href=\"https://www.mysite.com/thepage">linked to a page</a></p>",
      ...
   }


.. hint::

   There is no real magic to this. Here is a peek at the TypoScript behind ``lib.parseFunc_nnrestapi``.
   We are simply inheriting all settings from ``lib.parseFunc_RTE`` and modifying it to force absolute URLs
   in links and typolinks.

   .. code-block:: typoscript

      lib.parseFunc_nnrestapi < lib.parseFunc_RTE
      lib.parseFunc_nnrestapi.tags {
         link.typolink.forceAbsoluteUrl = 1
         a.typolink.forceAbsoluteUrl = 1
      }