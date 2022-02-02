.. include:: ../Includes.txt

.. _localization:

============
Localization & Translation
============

How to handle multiple languages in your TYPO3 RestApi
---------

The TYPO3 RestAPI supports retrieving localized (translated) data from the TYPO3 backend.

**Localized data could be...**

- records, models or data from any database table
- :ref:`individual content elements <examples_contentelements>` from pages in the backend
- complete pages or :ref:`columns <examples_pages>` with all content elements
- configuration-arrays, :ref:`TypoScript setup arrays <examples_settings>` etc. 
- translated labels from the TCA to be used in forms in your frontend application
- ...

Step-by-step
---------

.. rst-class:: bignums

1. Prerequisites

   In the following example we are assuming that you plan to use the `standard TYPO3 procedure <https://docs.typo3.org/m/typo3/guide-frontendlocalization/main/en-us/SettingUpLanguages/Index.html>`__
   to create localized records in the backend:

   - You have **defined your languages** in your site-configuration, either using the backend module "Sites" or by editing the site's config.yaml
   - You have **translated your content-elements** or records in the backend and all records are in "connected" mode
   - You have created an Endpoint that returns data or content elements like described :ref:`in this example <examples_contentelements>`

2. Enable localization in the TYPO3 RestAPI:

   By default, localization is disabled for the TYPO3 RestApi.

   There are two ways to get it up and running. Depending on your :ref:`use-case <annotations_localize_usecases>`, choose one
   of the following options:

   | **Enable global localization**
   Allow localization for ALL records by setting ``enabled = 1`` in your TypoScript setup:

   .. code-block:: typoscript

      plugin.tx_nnrestapi.settings.localization.enabled = 1

   **---- OR ----**

   | **Enable localization on a per-endpoint base**
   Use this Annotation at your method to enable localization only for individual methods:

   .. code-block:: php

      @Api\Localize()

3. Request the language

   Once you have localization enabled you can retrieve the translated records by sending a requests
   using one of the following options:

   -  Add a ``Accept-Language`` header to the request, e.g ``Accept-Language: en-US``
   -  Use the language path in the URL when sending requests to the api, e.g. ``/en/api/endpoint/``
   -  Use the ``?L=`` parameter in the URL with the ``languageId``, e.g. ``?L=1``

Frontend examples
---------

.. tabs::

   .. tab:: PHP

      .. code-block:: php

         <?php

         $url = 'https://www.mysite.com/api/endpoint';
         $language = 'en-EN';

         $headers = [
            'Accept: application/json',
            'Accept-Language: ' . $language
         ];

         $curl = curl_init();
         curl_setopt($curl, CURLOPT_URL, $url);
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

         // only include if you are having problems with SSL certificate
         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

         $json = curl_exec($curl);
         curl_close($curl);

         $data = json_decode( $json, true );

         $dump = htmlspecialchars( print_r( $data, true ) );
         echo "<pre>{$dump}";

   .. tab:: pure JS

      .. code-block:: javascript

         const url = 'https://www.mysite.com/api/endpoint';
         const language = 'de-DE';
         
         const xhrConfig = {
            method: 'GET',
            headers: {
               'Content-Type': 'application/json',
               'Accept-Language': language
            },
         };
         
         fetch( url, xhrConfig )
               .then( async response => {

               let data = await response.json()

               if ( !response.ok ) {
                  alert( `Error ${response.status}: ${data.error}` );   
               } else {
                  console.log( data );
                  document.getElementById('result').innerText = data.html;
               }
            });

   .. tab:: pure JS (old browsers)

      .. code-block:: javascript

         var url = 'https://www.mysite.com/api/endpoint';
         var language = 'en-EN';
                  
         var xhr = new XMLHttpRequest();
         xhr.overrideMimeType('application/json');

         xhr.open('get', url);
         xhr.setRequestHeader('Accept-Language', language);

         xhr.onload = function () {
            var data = JSON.parse( xhr.responseText );
            if (xhr.status != 200) {
               alert('Error!');
            }
            console.log( data );
         };

         xhr.onerror = function () {
            alert('Some other error... probably wrong url?');
         };
         
         xhr.send();

   .. tab:: axios

      .. code-block:: javascript

         const url = 'https://www.mysite.com/api/endpoint';
         const language = 'en-EN';

         const headers = {
            'Accept-Language': language
         };

         axios({
            method: 'get',
            url: url,
            headers: headers
         }).then( ({data}) => {
            console.log( data );
         }).catch( ({response}) => {
            console.log( response.data );
         });

   .. tab:: jQuery

      .. code-block:: javascript

         const url = 'https://www.mysite.com/api/endpoint';
         const language = 'en-EN';
         
         const headers = {
            'Accept-Language': language
         };
               
         $.ajax({
            url: url,
            type: 'GET',
            headers: headers
         }).done((data) => {
            console.log( data );
         }).fail((error) => {
            alert( `Error ${error.status}: ${error.responseJSON.error}` );
         });

More examples?
---------

You can find more explanations and examples in the following chapters:

- Using the :ref:`@Api\Localize() Annotation <annotations_localize>` to enable translation on a per-method base 
- How to :ref:`render localized content-elements <examples_contentelements>` and return them to your frontend application
- How to :ref:`render a complete page <examples_pages>` and return all content elements in a certain column

Dive deeper?
---------

If you are interested to find out more about localization in TYPO3 and the reason why it is not 
a trivial topic, head on :ref:`to this chapter <localization_background>`.

.. toctree::
   :hidden:
   :glob:
   :maxdepth: 1

   Localization/*

