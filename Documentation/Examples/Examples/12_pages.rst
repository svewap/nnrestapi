.. include:: ../Includes.txt

.. _examples_pages:

============
Rendering a page
============

Render all content-elements from a page
------------

As a variation of the example :ref:`how to render a single content element <examples_contentelements>`, we will
now render all content-elements that were placed in a certain column ("colPos") of a page.

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
       * @Api\Route("GET column/{pageUid}/{colPos}");
       * @Api\Access("public")
       * @Api\Localize()
       * 
       * @param int $pageUid
       * @param int $colPos
       * @return array
       */
      public function contentFromColumn( int $pageUid = null, int $colPos = null )
      {
         $html = \nn\t3::Content()->column( $colPos, $pageUid );
         return ['html'=>$html];
      }
   }
   
Here are some examples of how to retrieve the rendered content for a given page-uid and colPos:

.. code-block:: php

   // GET all content-elements from page 123 and column 0
   https://www.mysite.com/api/content/column/123/0

   // GET all content-elements from page 99 and column 110
   https://www.mysite.com/api/content/column/99/110

