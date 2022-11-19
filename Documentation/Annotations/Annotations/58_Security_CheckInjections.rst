.. include:: ../Includes.txt

.. _annotations_security_checkinjection:

==================
@Api\\Security\\CheckInjections
==================

Check incoming request for SQL Injections
---------

The ``@Api\Security\CheckInjections()`` annotation allows you perform a very basic check of the incoming 
``POST`` and ``GET`` variables. It searches for typical SQL-injection patterns like ``"; SELECT ...`` and 
automatically locks all requests from the current IP for 24 hours.

We know this: checking for typical SQL injection patterns at this level is not very reliable.
There are many sneaky methods and patterns that could be missed by this check. And it should never be
be a substitute for securing your database queries and sanitizing the variables before writing them to 
the database.

On the other hand: have you ever had a look in one of your server log files? 
You will see tons of requests from bots using patterns that would be successfully blocked by using
this annotation. And keeping bots out of the system as soon as possible is always sensible.

The basic syntax is:

.. code-block:: php

   @Api\Security\CheckInjections( $autoLockIp )

An example would be:

.. code-block:: php

   // Check for typical injection-patterns and lock IP if an attempt was detected
   @Api\Security\CheckInjections()

   // Check, but don't automatically lock the IP
   @Api\Security\CheckInjections( false )

Full example:

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
       * (!) Note that we also need to add CheckLocked() for this to work
       * This could also be done globally in the TypoScript setup
       *
       * @Api\Security\CheckInjections()
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


Globally activating an injection test
--------------

If you would like to globally check for SQL injections for every endpoint, you do to not need 
to add ``@Api\Security\CheckInjections()`` to every endpoint manually. Instead you can 
set up a global check using this TypoScript setup:

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

.. hint::

   The ``\nn\rest::Security()``-Helper has many useful methods in case you would like
   to handle checking for limits and locking users manually.

   Have a look at ``\Nng\Nnrestapi\Utilities\Security`` for more details.

   .. code-block:: php

      // manually lock an IP for 5 minutes
      \nn\rest::Security( $this->request )->lockIp( 300, 'Reason why...' );

      // unlock the IP
      \nn\rest::Security( $this->request )->unlockIp();


