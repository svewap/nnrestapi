.. include:: ../Includes.txt

.. _localization:

============
Localization & Translation
============

How to handle multiple languages in your TYPO3 RestApi
---------

.. tip::

   **TL;DR**

   #. Define the languages in your **site-configuration**
   #. **Translate your content** and data in the backend like you always would
   #. Set ``plugin.tx_nnrestapi.settings.localization.enabled = 1`` in the **TypoScript setup**
   #. Optionally use the :ref:`@Api\Localize() <annotations_localize>` Annotation to control localization on a per-method base
   #. Send your requests to the TYPO3 Rest Api using one of the following options:

      -  Add a ``Accept-Language`` header to the request, e.g ``Accept-Language: en-US``
      -  Use the language path in the URL when sending requests to the api, e.g. ``/en/api/endpoint/``
      -  Use the ``?L=`` parameter in the URL with the ``languageId``, e.g. ``?L=1``

Understanding the basics
~~~~~~~~~

Let's first have a short look at how TYPO3 handles translation in the backend. If you are not familiar
with the basics, you should first have a look at a very good 
`documentation and tutorial <https://docs.typo3.org/m/typo3/tutorial-editors/main/en-us/Languages/Index.html>`__.

-  In the **backend module "sites"** you can add as many languages to your installation as you like.
   You can add optional fallback languages so the user sees content in a selected language, if his 
   preferred language is not available.
   
-  The languages configuration will be stored in the file ``config.yaml``. The array ``languages`` will have
   an entry for every language. In the following example, two languages ``Deutsch`` and ``English`` were 
   defined:

   .. code-block:: yaml

      languages:
        -
          title: Deutsch
          enabled: true
          languageId: 0
          base: /
          iso-639-1: de
          typo3Language: de
          locale: de_DE.UTF-8
          navigationTitle: Deutsch
          hreflang: de-de
          direction: ltr
          flag: de
          websiteTitle: ''
        -
          title: English
          enabled: true
          base: /en
          languageId: 1
          iso-639-1: en
          typo3Language: default
          locale: en_US.UTF-8
          websiteTitle: ''
          navigationTitle: English
          hreflang: en-US
          direction: ''
          fallbackType: strict
          fallbacks: '0'
          flag: us
    
-  To view a page in a certain language, you add the language path to the URL as the first part after the 
   domain-name: ``https://www.mysite.com/en/path-to-page`` - the ``/en`` is defined in the ``base``-property
   of the configuration above.

TYPO3 offers many possibilities
~~~~~~~~~

When TYPO3 receives a request for a different language than the standard-language (``languageId = 0``) it will 
run through a complex procedure. What TYPO3 does exactly depends on your individual configuration. In the 
**"connected mode"**, every localized content-element has a direct connection to the content-element in the 
base-language. Depending on your settings, TYPO3 will override or merge data-fields from the base-language with
fields from the localized data. 

In the "free mode" every translation can be individual, meaning: Not every translated element
needs a content-element in the base-language. Also, the order of the elements can vary between the languages. 
In other words: There is no real "connection" between the languages.

`Read the docs <https://docs.typo3.org/m/typo3/guide-frontendlocalization/main/en-us/Index.html#start>`__ to find 
out more about the topic.

"A movie database" – example for localization in a REST Api
~~~~~~~~~

In the context of your REST Api the **"connected mode"** will probably be the most common use-case.
To make this clear, let's think of an REST Api that you can address to get information about a certain. 
This could be the title, description and director of the movie. 

Let's think of every movie being located in a unique "shelf-number" of our movie-wall. To get information
about a certain movie, we will need to know its number - or speaking in terms related to a REST Api:
We need to know its unique URL or URI.

Our Api could offer an endpoint in this style:

.. code-block:: php

   https://www.mymediadatabase.com/api/movie/123

A GET-Request to this "shelf" will provide us with information about the movie located in shelf number ``123``.
The response could look something like this:

.. code-block:: json

   {
      "uid": 123,
      "title": "Revenge of the Killer Tomatoes",
      "description": "A great movie for vegatarians.",
      "director": "John de Bello"
   }

So far, so good. But what about handling translations / localizations?

The problem with localized data in context of a Rest API
~~~~~~~~~

Looking at the example above, we are currently looking at shelf number ``123`` and getting a result in English.
But what if we want to get information about the same movie - but in German? 

There are many solutions you could come up with to solve this task:

-  You could use a **different ID** for the German version of the Killer Tomatoes.

   This would be a seperate "shelf-number" for every language. The English version is located in shelf ``123``.
   The German in shelf ``124`` and so on. 
   The idea is ok - but actually could get a little confusing: We are not really talking about a different movie – 
   we just want the information about the same movie in a different language. 

   Your conclusion probably will be: "No, doesn't really feel good". You might loose the overview and have to
   pay a lot of attention in creating "mapping-tables" that keep track of the shelves for every language-variation
   of every movie.

-  You could keep the same book-shelf ID for the movie, but **prefix or suffix the URL** with a path that indicates,
   which language you are aiming for.

   The English version could be accessible at ``/api/movie/123`` and the German version at
   ``/de/api/movie/123`` or ``/api/movie/123/de`` or some other variation.

   This idea is OK as the shelf-number of the movie stays the same and we are only modifying the "language-part"
   of the URI. This seems stringent and logical – and once you've understood the principle and know the languages-
   abbreviations you can easily get the translations for every movie without any stress.

-  An alternative to the above approach: You could add an additional **URL-parameter** to the request.
   If you've been working for a longer time with TYPO3 the you should recognize the "famous" L-parameter that
   could be used up until version 8 of TYPO3. 
   
   Without URL-rewriting ("realurl") the language variants of a page would have looked like this:

   .. code-block:: php

      https://www.mymediadatabase.com/api/movie/123?L=1

   A little ugly - and not really the aspired way of creating a "beautiful Rest Api". But otherwise is rather
   comprehensible like the solution discussed above.

-  Last idea: Send the **preferred language "hidden" to the API** - as a kind of "metadata".

   This is actually a very nice idea: In this case the URI is not modified in any way. No path-prefixes. No
   additional GET-parameters. The movie ID stays the same. All we are telling the API during the request is:
   "I accept German. So please give me the information in German!"

   Here is where the "Request Header"-magic kicks in. You can accompany every request you send to the server, with
   a battalion of "hidden" headers. This can be: The format you would like to receive the answer in (JSON, HTML or 
   XML?) and of course the language you want (en-US? de-DE? klingon-Klingon?)

   The header commonly used the tell the server "I want a certain language" is the ``Accept-Language`` header.
   To make it clear in an example: When using the language-header you will physically **always** be sending 
   a request to the same URI:

   .. code-block:: php

      GET https://www.mymediadatabase.com/api/movie/123

   But depending on the language you will be sending different headers with the request.
   So it could be one of the following:

   .. code-block:: php

      // Ick sprecke Deutsch!
      Accept-Language: de-DE

      // Je parle Baguette
      Accept-Language: fr-FR

      // Il pablo Parmegano
      Accept-Language: it-IT

Beautiful solution! Well, then all we need to do is send the right ``Accept-Language``-header to get the localized
data, correct? Well, almost.

So where is the problem?
~~~~~~~~~

The problem is, the way TYPO3 stores localized data in the database: Under the hood TYPO3 **always** creates unique 
UIDs for every localized entry and content. This is because TYPO3 uses only **one field** as unique identifier in 
the database (the field ``uid``) - not two fields (e.g. ``uid`` and ``sys_language_uid``).

The English database-row of the "Killer Tomatoes" might have ``uid = 123``, but the German translation will definitly 
have some other ``uid`` - maybe ``281`` or something else. In the "connected mode" Typo3 will link these two rows
to each other using the field ``l10n_parent``. The field ``l10n_parent`` of the German translation will be set
to ``123`` which is the uid of the movie in the base-language (English).

**Now things get really confusing:**

If you do a query to the database and want to get the German (= localized) version of the movie number ``123``, then
at a first glance the result will look like this:

.. code-block:: json

   {
      "uid": 123,
      "title": "Rache der Killer Tomaten",
      "description": "Ein toller Film für Vegetarier.",
      "director": "John de Bello",
      ...
   }

The query-result is actually returning the UID of the base-language (English), but "invisibly" overlaying fields from the
translated database-row (281). In other words: We are actually looking at data from the database-row with the uid 281 (German)
but get the uid of the base-language in the result.

**Invisibly?**

Well not completely. TYPO3 actually passes two more "pseudo"-fields. These fields are ``_localizedUid`` and ``_languageUid``
and they indicate, that the data we are receiving is the merged result of two rows in the database: 

.. code-block:: json

   {
      "uid": 123,
      ...
      "_localizedUid": 281,
      "_languageUid": 1
   }

So which is the right uid?
~~~~~~~~~

Here is where the frontend needs a certain amount of "intelligence": It might be GETTING data using the identical URI in the request:

.. code-block:: php

   GET https://www.mymediadatabase.com/api/movie/123

But depending on the ``Accept-Language``-header will be retrieving data with the same ``uid``, but needs to be *stored* in different 
shelves. If the user can edit the title, then - depending on the language he is currently editing - the data must be ``PUT`` back 
in to the UID ``123`` (for the English version) but ``281`` for the German version.

This is something you will have to implement yourself – either in the front- or backend. The nnrestapi doesn't take care of
automatically "changing" the UID of the data to be persisted. It simply ignores the field "_localizedUid" - to not produce 
uncontrolled results.

