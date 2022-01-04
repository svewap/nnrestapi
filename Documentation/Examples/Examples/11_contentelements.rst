.. include:: ../Includes.txt

.. _examples_contentelements:

============
Rendering Content-Elements
============

Retrieving pre-rendered, translated content-elements
------------

Let's imagine you are creating a SPA / frontend-application in VueJS, React or Angular and are using
TYPO3 as a Restful Api.

In your application you have various texts and labels, for example in dialog-modals, on the onboarding 
screen, the privacy policy, imprint and so on. Of course, all of these texts are translated in to
multiple languages.

Wouldn't it be great to keep these texts editable in the TYPO3 backend and load them dynamically 
in the language the user has his browser set to?

The following example illustrates, how easy it is to **get pre-rendered and localized content-elements** 
from the TYPO3 Rest Api.

| **Want to render all content-elements of a page?** 
Then check out :ref:`how to render a complete column <examples_pages>`.

Step-by-step
----------

.. rst-class:: bignums

1. Creating the class

   After :ref:`installing <quickstart>` the nnrestapi extension, let's start by creating the ``Content`` class. 
   
   Your Api classes can be located anywhere inside the ``Classes`` folder of your extension. 
   We would recommend placing them in a folder named ``Classes/Api/...``.

   **Here is what you need to get started:**

   .. code-block:: php

      <?php   
      namespace My\Extension\Api;

      use Nng\Nnrestapi\Annotations as Api;
      use Nng\Nnrestapi\Api\AbstractApi;

      class Content extends AbstractApi {   
      }


2. Defining the GET-method

   The idea is to be able to get any content-element by its ``uid`` by sending a GET-request to 
   the endpoint: 

   .. code-block:: php

      https://www.mysite.com/api/content/{uid}

   **Reminder:** If no :ref:`custom_routing` is defined for the method, the **first part** of the URL-path 
   after ``api/`` will be interpreted as the controller-name of your Rest Api. In this case ``content``
   automatically will route to methods in your class ``Content``.

   If the next part of the URL is an **integer**, the TYPO3 Rest Api automatically maps this to the request 
   argument ``$uid`` and will call the ``indexAction`` of your class. 

   As we want a ``GET`` request here, all we need to do is define a method called ``getIndexAction()``.

   To get the ``uid``, let's use Dependency Injection: 

   .. code-block:: php

      <?php   
      namespace My\Extension\Api;

      use Nng\Nnrestapi\Annotations as Api;
      use Nng\Nnrestapi\Api\AbstractApi;

      class Content extends AbstractApi 
      {
         /**
          * @Api\Access("public")
          * @Api\Localize()
          *
          * @param int $uid
          * @return array
          */
         public function getIndexAction( int $uid = null )
         {
            $html = \nn\t3::Content()->render( $uid );
            return ['html'=>$html];
         }
      }
   
   **The two things to pay attention to here are:**

   .. code-block:: php

      @Api\Access("public")

   This says, that **anybody** can access this endpoint. No authentication required. 
   No need to be logged in. You probably will want to :ref:`change this <authentication>`.

   .. code-block:: php

      @Api\Localize()

   Defines, that the nnrestapi will "pay attention" to the requested language and translate 
   the content-element (if a translation exists for it in the backend).


3. Test it!

   Create a content-element in the backend, translate it and then enter the URL in the browser
   to see the result. Replace ``123`` in the example with the ``uid`` of your content-element 
   in the default language:

   .. code-block:: php

      https://www.mysite.com/api/content/123

   This should give you a JSON with the rendered content element in your default language.
   To get the localized version you have 3 possibilities:

   | **1. Send the "Accept-Language" header**
   Send a ``Accept-Language`` header with your request as described :ref:`in this chapter <localization>`.
   The nnrestapi will automatically "listen" to this header and make sure that the data returned will be localized.

   .. code-block:: php

      Accept-Language: en-US

   | **2. Use the language path in the URL**
   Call the endpoint, but add the language prefix to the URL like you would when doing standard requests to TYPO3
   pages.

   .. code-block:: php

      https://www.mysite.com/en/api/content/123

   **Getting translations, even with no language path set?** Simple explanation: You are currently testing things 
   by entering the URL in the browser. Your browser might automatically be sending an ``Accept-Language``-header, 
   e.g. ``en-DE``. The nnrestapi falls back to the Accept-Language header, if no other language path is set in the URL.
   This can be changed by removing Accept-Language as a field to check in the :ref:`TypoScript setup <configuration_locaization>`.

   | **3. Use the "L"-parameter in the URL**
   Last option: add the famous "L"-parameter with the language-uid to the URL. This option was actually removed in
   one of the last TYPO3 versions - but we are "reintroducing" it for the Rest Api because it might make life a
   little easier:

   .. code-block:: php

      https://www.mysite.com/api/content/123?L=1


.. tip::

   **Test it in the backend!**

   Testing the localization can also be done using the "RestApi" backend module:
   Use the tab "Language" below the input field for the URL to modify the "Accept-Language" header that is sent
   when making the request.