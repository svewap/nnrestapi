.. include:: ../Includes.txt

.. _examples_article:

============
Example Api Endpoint
============

Creating a TYPO3 Restful Api for articles
------------

In the following example we would like to create an endpoint that can read, update, insert and
delete articles in a news-system.

All operations can be executed by calling the TYPO3 Rest Api endpoint located at  
``https://www.mysite.com/api/article``. Here is an overview of what we are planning to do:

+------------+---------------------+-------------------------------------+--------------------------------------------------+
| Method     | URL                 | Request body / payload              | operation                                        |
+============+=====================+=====================================+==================================================+
| ``GET``    | ``/api/article/1``  | (none)                              | Get entry with uid [1] from database             |
+------------+---------------------+-------------------------------------+--------------------------------------------------+
| ``PUT``    | ``/api/article/1``  | {"title":"News", "text":"nice"}     | Update full entry with uid [1] in database       |
+------------+---------------------+-------------------------------------+--------------------------------------------------+
| ``DELETE`` | ``/api/article/1``  | (none)                              | Delete entry with uid [1] in database            |
+------------+---------------------+-------------------------------------+--------------------------------------------------+
| ``POST``   | ``/api/article``    | {"title":"News", "text":"read it"}  | Insert a new entry in database                   |
+------------+---------------------+-------------------------------------+--------------------------------------------------+

**The URLs above have 3 parts:**

- every URL is prefixed with ``api`` as first part of the path. This is the default setting for 
  every Api. It can be changed in the configuration YAML.
- the second part of the URL is ``article``. This is the name of your custom Api-Class in lowercase.
- the third part is the ``uid`` of the entry to get, update or delete. It is not set in the ``POST``
  request, because here we want to insert a new article 


.. hint::

   To find out more about routing, have a look at :ref:`routing_standard` and :ref:`custom_routing`.



Step-by-step
----------

1. Creating the class
~~~~~~~~~~

Let's start by creating the ``Article`` class. Your Api classes can be located anywhere inside of
the ``Classes`` folder of your extension. We would recommend placing them in a folder named
``Classes/Api/...``.

Every Api Class should extend ``Nng\Nnrestapi\Api\AbstractApi``. You can also reference the 
``Nng\Nnrestapi\Annotations`` as ``Api``. You will be using these Annotations to define
access-rights and other things later. 

**Here is what you need to get started:**

.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;

   class Article extends AbstractApi {   
   }


2. Defining the GET-method
~~~~~~~~~~

The first method we want in our class should take care of retrieving a ``Article``-Model by its
uid from the database and returning it to the frontend.

We want to be able to access this endpoint by sending a GET-Request to the following URL.
As a last part of the URL we want to be able to pass the ``uid`` of the Article to retrieve.

.. code-block:: php

   https://www.mysite.com/api/article/1


**Remember:** If no :ref:`custom_routing` is defined for the method, the **first part** of the URL-path 
after ``api/`` will be interpreted as the controller-name of your Rest Api. In this case ``article``
automatically will route to methods in your class ``Article``.

If the next part of the URL is an **integer**, the TYPO3 Rest Api automatically maps this to the request 
argument ``$uid`` and will call the ``indexAction`` of your class. 

Depending on the HTTP Request method, the ``getIndexAction()``, ``postIndexAction()``, ``putIndexAction()`` 
etc. is called.

.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;

   class Article extends AbstractApi {

      /**
       * @Api\Access("public")
       * 
       * @return array
       */
      public function getIndexAction()
      {
      }
   }

3. Getting the uid
~~~~~~~~~~

There are several ways to get the value of ``$uid`` which was passed at the end of the URL:

.. code-block:: php

   https://www.mysite.com/api/article/1

When working with ActionControllers you are probably very familiar with this standard formula:

.. code-block:: php

   public function getIndexAction()
   {
      $args = $this->request->getArguments();
      $uid = $args['uid'];
      return ['uid'=>$uid];
   }

Another way of accessing the ``$uid`` is by using Dependeny Injection. All you need
to do is **define an argument** with the variable name ``$uid``. The uid will 
automatically be passed to your method:

.. code-block:: php

   public function getIndexAction( $uid = null )
   {
      return ['uid'=>$uid];
   }

Here we are going to use a even nice way: We'll inject the ``Model`` directly using
Depency Injection. And to round it up, we are also going to return a 404-error, if
the Article could not be found. 

.. code-block:: php

   /**
    * @param My\Extension\Domain\Model\Article $article 
    */
   public function getIndexAction( $article = null )
   {
      if (!$article) {
         return $this->response->notFound('Requested Article was not found.');
      }
      return $article;
   }

That was it! 

.. hint:: 

   To find out more about error handling, refer to this section: :ref:`responses_errors`.


4. Updating an existing Model
~~~~~~~~~~

Next, lets write the method to handle the ``PUT`` request. ``PUT`` will update an existing
model, so we will not only need to pass an ``uid``, but also a JSON with the data to update.

To compose and test this request, you can use `Postman <https://www.postman.com/>`__ or the
backend testbed that the ``nnrestapi`` comes with.

We will be ``PUT``ing the data to this URL:

.. code-block:: php

   https://www.mysite.com/api/article/1


and sending this JSON-data to update the title of the Article:

.. code-block:: json

   {"title":"updated title", "uid":1}

Next we need an endpoint to handle the request and update the model in the database:

.. code-block:: php

   /**
    * @param My\Extension\Domain\Model\Article $article 
    */
   public function putIndexAction( $article = null )
   {
      if (!$article) {
         return $this->response->notFound('Requested Article was not found.');
      }
      \nn\t3::Db()->update( $article );
      return $article;
   }

The actual magic is: The ``nnrestapi`` took care of retrieving the existing model from
the database and merging the JSON-data in to the model. The ``$model`` in this method
will have the updated ``$title`` from the request, but will **not** be persisted yet.
To save changes to the Model you will have to persist it.

You can use the standard ``$repository->update()`` and ``$persistenceManager->persistAll()`` methods,
but here we are using one of our favourite one-lines from the TYPO3 ``nnhelpers``-extension.

.. _examples_article_newmodel:

5. Creating / inserting a new Model
~~~~~~~~~~

Let's use the ``POST`` method to create a new Article and persist it in the database.
The precedure is almost identical to the ``PUT``-Request. Only difference: We are **NOT**
sending an ``$uid``.

.. code-block:: php

   https://www.mysite.com/api/article

.. code-block:: json

   {"title":"new article"}


Here is the corresponding method in your Article-class:

.. code-block:: php

   /**
    * @param My\Extension\Domain\Model\Article $article
    */
   public function postIndexAction( $article = null )
   {
      $persistedArticle = \nn\t3::Db()->insert( $article );
      return $persistedArticle;
   }

6. Deleting a Model
~~~~~~~~~~

Now we are only missing a way to delete a Model from the database. We will be sending a 
``DELETE`` request to this endpoint and pass the ``$uid`` of the Article we would like
to delete:

.. code-block:: php

   /**
    * @param My\Extension\Domain\Model\Article $article
    */
   public function deleteIndexAction( $article = null )
   {
      if (!$article) {
         return $this->response->notFound('Requested Article was not found.');
      }
      \nn\t3::Db()->delete( $article );
      return $article;
   }


Full example
----------

.. attention::

   **DON'T DO IT!**

   You probably will **not** want to expose all your read and write endpoints to the public using ``@Api\Access("public")``.
   We have only used public access here to provide you with an "instant feeling of success".

   Restricting acess to endpoints can be acomplished by :ref:`access_checkaccess` or by using Annotations as described
   in the chapter :ref:`access`


.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;

   class Article extends AbstractApi {

      /**
       * GET an article via: /api/article/{uid}
       *
       * @Api\Access("public")
       * @param My\Extension\Domain\Model\Article $article
       */
      public function getIndexAction( $article = null )
      {
         if (!$article) {
            return $this->response->notFound('Requested Article was not found.');
         }
         return $article;
      }

      /**
       * UPDATE an article via: /api/article/{uid}
       *
       * @Api\Access("public")
       * @param My\Extension\Domain\Model\Article $article
       */
      public function putIndexAction( $article = null )
      {
         if (!$article) {
            return $this->response->notFound('Requested Article was not found.');
         }
         \nn\t3::Db()->update( $article );
         return $article;
      }

      /**
       * INSERT a new article via: /api/article
       *
       * @Api\Access("public")
       * @param My\Extension\Domain\Model\Article $article
       */
      public function postIndexAction( $article = null )
      {
         $insertedArticle = \nn\t3::Db()->insert( $article );
         return $insertedArticle;
      }

      /**
       * DELETE an article via: /api/article/{uid}
       *
       * @Api\Access("public")
       * @param My\Extension\Domain\Model\Article $article
       */
      public function deleteIndexAction( $article = null )
      {
         if (!$article) {
            return $this->response->notFound('Requested Article was not found.');
         }
         \nn\t3::Db()->delete( $article );
         return $article;
      }
   }

