.. include:: ../Includes.txt

.. _annotations_security_checklocked:

==================
@Api\\Security\\CheckLocked
==================

Check if IP was locked
---------

This annotation will check, if the current IP was blocked by a previous security check.

Use this annotation like this:

.. code-block:: php

   @Api\Security\CheckLocked()

(Un)locking an IP manually
----------

The ``\nn\rest::Security()``-Helper has many useful methods in case you would like
to lock the users manually.

Have a look at ``\Nng\Nnrestapi\Utilities\Security`` for more details.

.. code-block:: php

   // manually lock an IP for 5 minutes
   \nn\rest::Security( $this->request )->lockIp( 300, 'Reason why...' );

   // unlock the IP
   \nn\rest::Security( $this->request )->unlockIp();


.. important::

   The ``@Api\Security\CheckLocked()`` Annotation is typically used in combination
   with other Security-Annotations.

   One on them is the :ref:`\Api\Security\CheckLocked() <annotations_security_checkinjection>` Annotation
   which will automatically lock an IP if an SQL injection was attempted.

   In order to not need to add ``@Api\Security\CheckLocked()`` to every endpoint manually, you can 
   set up a global check which will block all requests from locked IPs.
   
   Here is the TypoScript setup that will always first check for SQL-injections and then check
   for locked users.

   .. code-block:: typoscript

      plugin.tx_nnrestapi {
         settings {
            security {
               defaults {
                  10 = \Nng\Nnrestapi\Utilities\Security->checkInjections
                  20 = \Nng\Nnrestapi\Utilities\Security->checkLocked
               }
            }
         }
      }



.. code-block:: php

   <?php
   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   /**
    * @Api\Endpoint()
    */
   class Example extends AbstractApi
   {
      /**
       * @Api\Security\CheckLocked()
       * @Api\Access("public")
       *
       * @return array
       */
      public function getSettingsAction() 
      {
         return ['nice'=>'result'];
      }

   }


