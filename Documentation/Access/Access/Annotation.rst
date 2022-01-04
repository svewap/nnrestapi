.. include:: ../Includes.txt

.. _access_checkaccess:

============
Restricting Access with a custom method
============

How to implement your own method for checking access rights to your endpoint
---------

In most cases using the :ref:`@Api\\Access(...)<access>` annotation will be sufficient to 
restrict the access to your TYPO3 Rest Api endpoint to certain frontend-users or user groups.

In case you need to implement your own logic for checking access rights, you can simply
define a ``checkAccess()``-method in the class of your endpoint. This will override the
default ``checkAccess()``-method from ``\Nng\Nnrestapi\Api\AbstractApi``.

Here is an example:

.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   class Test extends \Nng\Nnrestapi\Api\AbstractApi {

      /**
       * Completely senseless, but nice demo: 
       * Decide randomly, if the user may access your endpoint.
       *
       * @param array $endpoint information about the endpoint that was supposed to be called
       * @return boolean
       */
      public function checkAccess( $endpoint = [] ) 
      {
         return rand(0, 2) == 1;
      }

      /**
       * This method will only be accessible if the checkAccess-method 
       * above returned true as value.
       * 
       * @return array
       */
      public function getExampleAction()
      {
         return ['result'=>'welcome!'];
      }
   }

The ``checkAccess()`` method must return ``TRUE``, if the user is allowed to access the endpoint. 
If it returns ``FALSE``, the script will automatically be aborted and the Api will return 
a ``HTTP 403 Forbidden`` header.


Example: Restricting access to certain IP-adresses
--------

In this example, we will use the ``checkAccess()`` method to check, if the user has a certain IP.
The script will only allow access to the methods in this class, if the ``$remoteAddr`` matches
one of the patterns defined in ``$allowedIpList``:

.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   class Test extends \Nng\Nnrestapi\Api\AbstractApi {

      /**
       * Checks, if the IP of the user matches a given adress or pattern.
       *
       * @param array $endpoint
       * @return boolean
       */
      public function checkAccess( $endpoint = [] ) 
      {
         $remoteAddr = $_SERVER['REMOTE_ADDR'];
         $allowedIpList = '109.251.*, 109.252.17.2';
         return \TYPO3\CMS\Core\Utility\GeneralUtility::cmpIP( $remoteAddr, $allowedIpList );
      }

      //... your endpoint-methods come here

   }


Example: Check for IP-adresses AND certain fe_user
--------

If you would like to combine the above example with the check for certain authenticated
Frontend-Users like described in :ref:`@Api\\Access(...)<access>` you can always call
the ``parent::checkAccess()`` method in your custom ``checkAccess()`` method.

This will process the login in ``\Nng\Nnrestapi\Api\AbstractApi::checkAccess()`` that
handles restrictions made in the annotations.

.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   class Test extends \Nng\Nnrestapi\Api\AbstractApi {

      /**
       * Checks, if the IP of the user matches a given adress or pattern.
       *
       * @param array $endpoint
       * @return boolean
       */
      public function checkAccess( $endpoint = [] ) 
      {
         $remoteAddr = $_SERVER['REMOTE_ADDR'];
         $allowedIpList = '109.251.*, 109.252.17.2';

         // First let's check, if the IP is allowed
         if (!\TYPO3\CMS\Core\Utility\GeneralUtility::cmpIP( $remoteAddr, $allowedIpList )) {
            return false;
         }

         // if yes, then let the AbstractApi take care of checking the fe_users etc.
         return parent::checkAccess( $endpoint );
      }

      //... your endpoint-methods come here
      
   }
