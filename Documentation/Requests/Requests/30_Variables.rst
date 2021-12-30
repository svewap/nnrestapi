.. include:: ../Includes.txt

.. _routing_variables:

============
Variables
============

Accessing Request variables in your endpoint
------------

When a request is forwarded to your endpoint, ``nnrestapi`` automatically injects an
instance of ``Nng\Nnrestapi\Mvc\Request``. This is basically a simple wrapper for the
standard TYPO3 ``TYPO3\CMS\Extbase\Mvc\Request``, pimped with a couple of features
needed specifically for accessing the Request-body (the parsed JSON), the files passed
in the multipart-formdata etc.

.. hint::

   Make sure your endpoints' class extends the ``Nng\Nnrestapi\Api\AbstractApi``, otherwise
   you will not have access to all properties and methods mentioned in this section.

To make things intuitive for anybody who is familiar with the TYPO3 ActionControllers, 
you can access the ``Nng\Nnrestapi\Mvc\Request`` in the class property ``$this->request``.

This section gives you an overview of common variables you might want to access while
evaluating the request and composing the response.

All of the following examples are placed inside of you endpoints' method.

Request arguments
~~~~~~~~~~~~

Use ``$this->request->getArguments()`` to access the Request variables.

.. code-block:: php

   <?php

   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;

   class Example extends AbstractApi
   {
      /**
       * @Api\Access("public")
       * 
       * @return array
       */
      public function getIndexAction()
      {
         $args = $this->request->getArguments();
         return $args;
      }
   }


Useful variables and methods in $this->request 
~~~~~~~~~~~~

Here is an list of the most-used methods and variables you can access in ``$this->request``:

+--------------------------------------------+---------------------------------------------------------------------+
| Method                                     | Description                                                         |
+============================================+=====================================================================+
| ``$this->request->getEndpoint()``          | Return information about the current endpoint, including parsed     |
|                                            | annotations, defined access-rights etc.                             |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getMvcRequest()``        | Return the original TYPO3 ServerRequest                             |
|                                            | ``TYPO3\CMS\Core\Http\ServerRequest``                               |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getAcceptedLanguage()``  | Return the accepted language passed by the browser, e.g.            |
|                                            | ``en`` or ``de``                                                    |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getMethod()``            | The HTTP-Request-Method used for the current request, e.g.          |
|                                            | ``post``, ``get``, ``put`` etc.                                     |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getPath()``              | The current URL requested, excluding the domain-name, e.g.          |
|                                            |                                                                     |
|                                            | .. code-block:: php                                                 |
|                                            |                                                                     |
|                                            |    "/api/test/1"                                                    |
|                                            |                                                                     |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getBody()``              | An array - the parsed JSON passed in the body of the request, e.g.  |
|                                            |                                                                     |
|                                            | .. code-block:: php                                                 |
|                                            |                                                                     |
|                                            |    ['title'=>'Hello', 'name'=>'David']                              |
|                                            |                                                                     |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getRawBody()``           | A string - the raw JSON-data, unparsed - e.g.                       |
|                                            |                                                                     |
|                                            | .. code-block:: php                                                 |
|                                            |                                                                     |
|                                            |    "{\"title\":\"Hello\", \"name\":\"David\"}"                      |
|                                            |                                                                     |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getSettings()``          | The settings for the current request, including the instructions    |
|                                            | for processing the data defined in the TypoScript setup             |
|                                            |                                                                     |
|                                            | .. code-block:: php                                                 |
|                                            |                                                                     |
|                                            |    [                                                                |
|                                            |       ...                                                           |
|                                            |       'fileUploads' => [                                            |
|                                            |           'default' => [                                            |
|                                            |               'defaultStoragePath' => '1:/api/',                    |
|                                            |               'file' => '1:/api/tests/',                            |
|                                            |            ]                                                        |
|                                            |       ],                                                            |
|                                            |       'globalDistillers' => [                                       |
|                                            |           'TYPO3\CMS\Extbase\Domain\Model\Category' => [            |
|                                            |               'exclude' => 'parent'                                 |
|                                            |            ]                                                        |
|                                            |       ]                                                             |
|                                            |       ...                                                           |
|                                            |    ]                                                                |
|                                            |                                                                     |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getFeUser()``            | The current Frontend-User. Raw data-row from ``fe_users``, if the   |
|                                            | current request was made by a authenticated frontend-user           |
|                                            |                                                                     |
|                                            | .. code-block:: php                                                 |
|                                            |                                                                     |
|                                            |    ['uid'=>1, 'username'=>'john', 'usergroup'=>'3,5', ...]          |
|                                            |                                                                     |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getFeGroups()``          | Data-rows from ``fe_groups``, if the current request was made by a  |
|                                            | authenticated frontend-user. An array of all groups that the        |
|                                            | current user belongs to, including subgroups.                       |
|                                            |                                                                     |
|                                            | .. code-block:: php                                                 |
|                                            |                                                                     |
|                                            |    [                                                                |
|                                            |       0 => ['uid'=>3, 'title'=>'groupname 1', ...],                 |
|                                            |       1 => ['uid'=>5, 'title'=>'groupname 2', ...],                 |
|                                            |       ...                                                           |
|                                            |    ]                                                                |
|                                            |                                                                     |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->isAdmin()``              | Returns ``true`` if the feUser has the checkbox "RestApi Admin"     |
|                                            | set in the fe_user-entry. This will grant additional privileges     |
|                                            | like retrieving hidden records as if the fe_user was a backend-user |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getRemoteAddr()``        | Returns the ``REMOTE_ADDR`` (IP-address) of the request             |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getUploadedFiles()``     | Returns an array of the uploaded files                              |
|                                            |                                                                     |
|                                            | .. code-block:: php                                                 |
|                                            |                                                                     |
|                                            |    [                                                                |
|                                            |       'file-0' => TYPO3\CMS\Core\Http\UploadedFile,                 |
|                                            |       'file-1' => TYPO3\CMS\Core\Http\UploadedFile,                 |
|                                            |       ...                                                           |
|                                            |    ]                                                                |
|                                            |                                                                     |
+--------------------------------------------+---------------------------------------------------------------------+
| ``$this->request->getServerParams()``      | Returns the ``$_SERVER`` array of the request                       |
|                                            |                                                                     |
|                                            | .. code-block:: php                                                 |
|                                            |                                                                     |
|                                            |    [                                                                |
|                                            |       'REQUEST_METHOD' => 'POST',                                   |
|                                            |       'REQUEST_SCHEME' => 'https',                                  |
|                                            |       'DOCUMENT_ROOT' => '/var/www/vhost/my/site',                  |
|                                            |       'REMOTE_ADDR' => '123.456.789.123',                           |
|                                            |       'SERVER_PORT' => '443',                                       |
|                                            |       ...                                                           |
|                                            |    ]                                                                |
|                                            |                                                                     |
+--------------------------------------------+---------------------------------------------------------------------+


Request arguments when using standard routing
~~~~~~~~~~~~

Imagine you have defined an endpoint that handles all ``GET`` requests that have an URL-pattern
beginning like this: ``/api/example/news/...``:

.. code-block:: php

   <?php

   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;

   class Example extends AbstractApi
   {
      /**
       * @Api\Access("public")
       * 
       * @return array
       */
      public function getNewsAction()
      {
         $args = $this->request->getArguments();
         return $args;
      }
   }


If you are using the standard routing, the endpoint to handle the request will automatically
be determined by the methodname. (see :ref:`routing_standard` for details). That means, that all 
of the of the following ``GET`` requests would be routed to the ``Example->getNewsAction()``
method:

.. code-block:: php

   https://www.mysite.com/api/example/news/
   https://www.mysite.com/api/example/news/1
   https://www.mysite.com/api/example/news/article
   https://www.mysite.com/api/example/news/article/1
   https://www.mysite.com/api/example/news/article/1/2/3

Based on the default way that ``nnrestapi`` interprets the above URL, it will parse the URL to
request-arguments using the pattern: 

.. code-block:: php

   https://www.mysite.com/api/{class}/{method}/{uid}/{param1}/{param2}/{param3}/{param4}


Here are examples of the resulting array returned by ``$this->request->getArguments()``:

+--------------------------------------------+---------------------------------------------------------------------+
| Requestes URL                              | **$this->request->getArguments()** will contain:                    |
+============================================+=====================================================================+
| ``/api/example/news``                      | ``[]``                                                              |
+--------------------------------------------+---------------------------------------------------------------------+
| ``/api/example/news/1``                    | ``['uid'=>1]``                                                      |
+--------------------------------------------+---------------------------------------------------------------------+
| ``/api/example/news/article``              | ``['uid'=>'article']``                                              |
+--------------------------------------------+---------------------------------------------------------------------+
| ``/api/example/news/article/1``            | ``['uid'=>'article', 'param1'=>'1']``                               |
+--------------------------------------------+---------------------------------------------------------------------+
| ``/api/example/news/article/a/b/c``        | ``['uid'=>'article', 'param1'=>'a', 'param2'=>'b', 'param3'=>'c']`` |
+--------------------------------------------+---------------------------------------------------------------------+


Dependeny injections with request arguments
~~~~~~~~~~~~

Remember, that you can always use Dependeny Injection to automatically pass request-
variables from ``$this->request->getArguments()`` in to your method. This is very useful when
you have implemented :ref:`custom_routing` and defined variables for your Route:

.. code-block:: php

   /**
    * @Api\Route("GET /example/{name}")
    * @Api\Access("public")
    * 
    * @param string $name
    * @return array
    */
   public function anyMethodNameYouLike( $name = null )
   {
      return ['welcome' => "Welcome {$name} to my RestAPi!"];
   }

--------------------------------


