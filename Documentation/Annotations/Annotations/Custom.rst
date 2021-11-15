.. include:: ../Includes.txt

.. _annotations_custom:

============
Adding custom annotations to your endpoints
============

How to add your own annotations and parse them
---------

If you would like to add custom annotations that get parsed and passed to the `endpoint`,
then follow these steps.

.. rst-class:: bignums

1. Create a class for the annotation

   Create a file in your own extension under ``Classes/Annotations/Example.php``.
   
   **Important:** The class needs to have the ``@Annotation`` in the class comment.

   .. code-block:: php
    
      <?php

      namespace My\Ext\Annotations;

      /**
       * @Annotation
       */
      class Example
      {
         public $value;

         /**
          * Normalize parameter to array.
          * Only needed, if you allow single AND multiple arguments in your annotation.
          *
          */
         public function __construct( $arr ) {
            $this->value = is_array( $arr['value'] ) ? $arr['value'] : [$arr['value']];
         }
        
         /**
          * This method is called when parsing all classes.
          * You must implement it in your own Annotation, if you want the parsed 
          * data to be cached and accessible later in your endpoint.
          *
          */
         public function mergeDataForEndpoint( &$data ) {
            $data['myIdentifer'] = $this->value;
         }
      }

2. Use the annotation in your endpoint

   Make sure to reference your namespace.

   .. code-block:: php
    
      <?php

      namespace My\Ext\Api;

      use My\Ext\Annotations as MyApi;
      use Nng\Nnrestapi\Annotations as Api;

      class Test {

         /**
          * @MyApi\Example("somevalue")
          * @Api\Access("public")
          * @return array
          */
         public function getAllAction()
         {	
            $endpoint = $this->request->getEndpoint();
            \nn\t3::debug( $endpoint );
            die();
         }
      }

3. Access the annotation value

   You have access to the value of your annotation at several places.
   
   Here is an example that accesses the above annotation value in the ``checkAccess`` method:
   See :ref:`@Api\\Access(...)<access_checkaccess>` for details.

   .. code-block:: php
    
      <?php

      namespace My\Ext\Api;

      use My\Ext\Annotations as MyApi;
      use Nng\Nnrestapi\Annotations as Api;

      class Test {

         /**
          * @return boolean
          */
         public function checkAccess( $endpoint = [] ) {
            if ($values = $endpoint['myIdentifer'] ?? false) {
               if (in_array('locked', $values)) {
                  return false;
               }
            }
            return true;
         }

         /**
          * @MyApi\Example("locked")
          * @return array
          */
         public function getAllAction()
         {	
            // ...
         }
      }