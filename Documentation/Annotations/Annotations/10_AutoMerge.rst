.. include:: ../Includes.txt

.. _annotations_automerge:

============
@Api\\AutoMerge
============

Disabling automatic merging of JSON data with a Model
---------

The ``@Api\AutoMerge()`` annotation can be used to control, if the JSON data automatically gets
merged with the Model you have defined as argument injection in your endpoint.

**By default, autoMerge is enabled**.
This can be changed by setting the following flag in the TypoScript setup:

.. code-block:: typoscript

   plugin.tx_nnrestapi.settings {
      autoMerge.enabled = 0
   }

The default setting in TypoScript can be overriden for every endpoint individually by using
the following Annotation syntax:

.. code-block:: php

   // Merge data with model (same as TRUE)
   @Api\AutoMerge()

   // Merge data with model
   @Api\AutoMerge(TRUE)

   // Disable the merging of data
   @Api\AutoMerge(FALSE)


**Full example:**

.. code-block:: php
   
   <?php

   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   use My\Extension\Domain\Model\Article;

   /**
    * @Api\Endpoint()
    */
   class Example extends AbstractApi
   {
      /**
       * Disable merging of JSON data with the model
       *
       * @Api\Route("PUT /news/{article}")
       * @Api\Example("{'title':'My new title'}")
       * @Api\AutoMerge(FALSE)
       * @Api\Access("public")
       *
       * @param Article $article
       * @return array
       */
      public function getIndexAction( Article $article = null ) 
      {
         return $article;
      }
   }

How autoMerge works (default bevaviour)
~~~~~~~~~~~

To understand the example above, let's have a quick look at what would happen **without** the 
``@Api\AutoMerge(FALSE)`` annotation.

Assume we have an ``Article`` with the ``uid = 1`` in the database and make a request to ``PUT /api/news/1`` with the JSON ``{"title":"My new title"}``

- The ApiController first checks the endpoint and sees, that it is expecting ``Article $article`` as first argument
- As we have passed ``uid = 1`` in the ``PUT /api/news/1`` request, it automatically retrieves the ``Article``-Model with ``uid = 1`` from the database
- It then checks the JSON body and sees: ``title`` was passed
- Next it overrides the ``title`` from the Model with the new ``title`` passed with the JSON body
- Now the modified Model gets passed to the endpoint method

Merging the JSON-data with the Model like described above is the default behaviour.
This can be disabled by either using the ``@Api\AutoMerge(FALSE)`` annotation – or by disabling it globally using the TypoScript 
setting ``plugin.tx_nnrestapi.settings.autoMerge.enabled = 0``. 

Quick-Tip: Persisting the Model
~~~~~~~~~~~

Note that the ``Article``-Model will have a modified ``title``, but **not** be persisted yet in the database.

You will need to do this yourself, e.g. by using this simple oneliner in your endpoint:

.. code-block:: php

   \nn\t3::Db()->update( $article )

Quick-Tip: A Fast way to set properties in the Model
~~~~~~~~~~~

When merging was disabled, you will need to take care of merging the 
data with your Model by yourself. This can be done with the classic getters and setters of the Model – or by using the helper-function:

.. code-block:: php

   $modifiedArticle = \nn\t3::Obj( $article )->merge(['title'=>'new title', ...]);

