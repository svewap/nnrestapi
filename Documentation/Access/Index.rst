.. include:: ../Includes.txt

.. _access:

============
Restricting Access
============

Only allow certain users to call your TYPO3 Rest Api endpoint
---------

By using the `@Api\Access(...)` annotation above your method, you can restrict the access to 
your REST API endpoint. This way you can decide, which Frontend-Users, Frontend-Usergroups,
Backend-Users or Backend-Admins are allowed to call your endpoint.

**You can restrict access to...**

- **frontend users**  (the standard TYPO3 frontend users)
- **frontend user groups**
- **backend users** (the endpoint can only be called, if you are logged in to the backend)
- **backend admins** (you must be logged in to the backend as admin)
- **global API-users** - defined in the Extension Configuration Manager
- **IP-adresses** and -ranges
- by using a :ref:`custom method <access_checkaccess>`

**The basic syntax of the @Api\Access-Annotation is:**

.. code-block:: php

   @Api\Access("options")

**Full example**

.. code-block:: php

   <?php   
   namespace My\Extension\Api;

   use Nng\Nnrestapi\Annotations as Api;
   use Nng\Nnrestapi\Api\AbstractApi;
   
   /**
    * @Api\Endpoint()
    */
   class Test extends AbstractApi 
   {
      /**
       * @Api\Access("public")
       * @return array
       */
      public function getExampleAction()
      {
         return ['result'=>'welcome!'];
      }
   }

Note that to use the ``@Api\Access``-annotation, you will need to add the `use Nng\Nnrestapi\Annotations as Api;` 
line at the top of your script.


Overview of options
---------

The following permissions exist for ``@Api\Access(...)``:

+--------------------------------------------+--------------------------------------------------------------+
| annotation                                 | permissions: Endpoint can be called by...                    |
+============================================+==============================================================+
| `@Api\Access("*")`                         | anyone, without authentication (same as `public`)            |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("public")`                    | anyone, without authentication (same as `*`)                 |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("fe_users")`                  | every logged in frontend user                                |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("fe_users[1]")`               | only logged in frontend user with uid 1                      |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("fe_users[1,2]")`             | logged in frontend user with uid 1 or 2                      |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("fe_users[david]")`           | only logged in frontend user with username `david`           |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("fe_groups[1,2]")`            | fe_user in fe_user_group uid 1 or 2                          |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("fe_groups[api]")`            | fe_user in fe_user_group `api`                               |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("api_users")`                 | all users defined in the extension configuration             |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("api_users[david]")`          | users `david` defined in the extension configuration         |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access({"fe_users", "be_users"})`    | all fe_users and be_users                                    |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("be_users")`                  | every logged in backend user                                 |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("be_admins")`                 | every logged in backend admin                                |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("ip[89.19.*,89.20.*]")`       | Limit to certain IPs (ADDITIONALLY to fe_user etc.)          |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("ip_users[89.19.*,89.*]")`    | Allow certain IPs (ALTERNATIVELY to fe_user etc.)            |
+--------------------------------------------+--------------------------------------------------------------+
| `@Api\Access("config[myconf]")`            | use `myconf` in Yaml config for the site/API **OR**          |
|                                            | the TypoScript Setup                                         |
+--------------------------------------------+--------------------------------------------------------------+


Examples
---------

Creating a public endpoint
~~~~~~~~~~~~

The following endpoint would be reachable as a GET-request at ``/test/example``.

To call the endpoint, the user does not have to be authenticated. It is a public endpoint without
any restrictions. Unnecessary to mention: Be careful, when exposing public endpoints!

.. code-block:: php

   /**
    * Open to public. Can be called by anybody.
    *
    * @Api\Access("public")
    * ...
    */


Restrict access to ANY frontend-user
~~~~~~~~~~~~

The following endpoint would be reachable as a GET-request at ``/test/example``.

Any **logged in frontend users** (``fe_users``) will be able to call it.

If the user is not logged in, a ``HTTP Error 403 Forbidden`` will be thrown.

.. code-block:: php

   /**
    * This endpoint will be only be accessible by a logged in fe_user.
    *
    * @Api\Access("fe_users")
    * ...
    */

Restrict access to SPECIFIC frontend-user(s)
~~~~~~~~~~~~

To be more specific about which Frontend-User is allowed to call the REST API endpoint
you can restrict it in the `@Api\Access`-annotation by using the square brackets syntax 
`fe_users[...]`.

.. code-block:: php

   /**
    * Only fe_user with uid 1 can call this endpoint.
    *
    * @Api\Access("fe_users[1]")
    * ...
    */


You can also use the `username` of the frontend-user instead of the `uid`:

.. code-block:: php

   /**
    * Only fe_user 'david' can call this endpoint!
    *
    * @Api\Access("fe_users[david]")
    * ...
    */

**Multiple users** can be defined by using on of the following syntaxes:

.. code-block:: php

   /**
    * Only fe_users 'david' and 'marc' can access this endpoint!
    *
    * @Api\Access("fe_users[david,marc]")
    * ...
    */

You are allowed to **mix** `usernames` and frontend-user `uids`:

.. code-block:: php

   /**
    * Only fe_users 'david' and the fe_user with uid '2' can access this endpoint!
    *
    * @Api\Access("fe_users[david,2]")
    * ...
    */

And in case you prefer using the **array syntax**, that is also possible:

.. code-block:: php

   /**
    * Only fe_users 'david' and 'marc' can access this endpoint!
    *
    * @Api\Access({"fe_users[david]", "fe_users[marc]"})
    * ...
    */


Restrict to IP-adresses ADDITIONALLY to other authentications
~~~~~~~~~~~~

By using the ``@Api\Access("ip[...]")`` annotation, you can limit the request to a given
list of IPs.

Contrary to all other ``@Api\Access()`` restrictions, the IP-restriction will be handled
like an **AND** constraint. If you set an IP-restriction, then the request **must** come from the
given IP, independent of other access-restrictions like Frontend-User Authentication etc.

.. code-block:: php

   /**
    * Only REMOTE_ADDR with IP 90.120.10.* may access this endpoint
    *
    * @Api\Access("ip[90.120.10.*]")
    * ...
    */

Multiple IPs can be listed the same way usernames or uids are listed in the examples above.
All the following examples are equivalents, choose the syntax you can remember best:

.. code-block:: php

   @Api\Access("ip[90.120.10.*, 90.120.11.*]")
   @Api\Access("ip[90.120.10.*], ip[90.120.11.*]")
   @Api\Access({"ip[90.120.10.*]", "ip[90.120.11.*]"})



Grant access for IP-adresses ALTERNATIVELY to other authentications
~~~~~~~~~~~~

By using the ``@Api\Access("ip_users[...]")`` annotation, you grant access to the endpoint from
given IPs without any other limitations.

Other than ``@Api\Access("ip[...]")``, if the IP is correct, there will not be any additional check 
of ``fe_users`` or ``be_users``.

.. code-block:: php

   /**
    * User with IP 90.120.10.* OR any fe_users (with any IP) may access this endpoint
    *
    * @Api\Access("ip_users[90.120.10.*]")
    * @Api\Access("fe_users")
    * ...
    */


Using global configurations
---------

Defining centralized access-groups in your site YAML or TypoScript Setup
~~~~~~~~~~~~

Of course it might not "feel" very good, to define users and usergroup-restrictions in
your TYPO3 Rest Api by using a static `username` or `uid` directly in the annotation. 

What if you need to add a user to the TYPO3 Restful Api that has access to all endpoints?
You would have to go through all your scripts and add the `username` or `uid` to the
`@Api\Access()` annotation.

The next problem might be: What if you plan to deploy your TYPO3 Rest Api to other environments
or installations. Every installation might have different usernames or `uids` . Defining
users by their `username` or `uid` in the `@Api\Access()` annotation directly will make it very 
difficult to keep all your installation up-to-date with the same code-base.

**The good news:** 
With the TYPO3 Rest Api you can also define `accessGroups` in your site-configuration or
TypoScript Setup and then refer to their identifier in your `@Api\Access()`-annotation instead 
of repeating using usernames or uids above the individual methods.

Let's start by adding this to your site-configuration `YAML` or TypoScript Setup

.. tabs::

   .. code-tab:: yaml

      nnrestapi:
         accessGroups:
            apiUsers: fe_users[3,2]

   .. code-tab:: typoscript

      plugin.tx_nnrestapi.settings {
         accessGroups {
            apiUsers = fe_users[3,2]
         }
      }

We have defined an access group with the identifier ``apiUsers``.
The frontend-users with the uid ``3`` and ``2`` are in this group.

The identifier name ``apiUsers`` is arbitrary. 
You may choose any identifier name here you like, and that makes sense to you.

Of course, you can also define multiple groups and have multiple users per group:

.. tabs::

   .. code-tab:: yaml

      nnrestapi:
         accessGroups:
            limitedUser: fe_users[david,marc]
            adminUsers: fe_users[1,2,3], be_users, be_admins
            viewOnlyUsers: fe_groups[apiViewers]

   .. code-tab:: typoscript

      plugin.tx_nnrestapi.settings {
         accessGroups {
            limitedUser = fe_users[david,marc]
            adminUsers = fe_users[1,2,3], be_users, be_admins
            viewOnlyUsers = fe_groups[apiViewers]
         }
      }


We now can refer to the access group by using the `config[identifier]`-syntax in our annotation:

.. code-block:: php

   /**
    * This endpoint will be only be accessible by a logged in fe_user.
    *
    * @Api\Access("config[apiUsers]")
    * ...
    */


Read on
---------

.. toctree::
   :glob:
   :maxdepth: 1

   Access/*
