.. include:: ../Includes.txt

.. _examples_legacy_fileuploads:

============
Uploading Files
============

.. warning::

    | This example uses an JavaScript with a very "old" way of creating and sending XHR-requests.
    | You will only need this, if you are still forced to optimize for **Internet Explorer 11 and below**.
    For a more modern approach we would recommend using the promise-based :ref:`fetch() <examples_plain_fileuploads>` or
    a library that saves a lot of headaches like :ref:`Axios <examples_axios_auth>`

.. hint::

    This chapter explains how to upload files to the ``nnrestapi`` (TYPO3 Restful Api) using pure JavaScript without any libraries and
    shows a solution that is compatible with browser not supporting ES6+ and ``fetch()`` like Internet Explorer Version 11 and below. 

    If you are interested in finding out, how to create the TYPO Rest Api Endpoint that processes the fileupload, attaches the SysFileReference 
    to a model and then persists the model in the database, please refer to the examples in :ref:`this section <examples_jquery_fileuploads>`

How to upload files to your TYPO3 Rest Api with pure JavaScript for older browsers
------------

.. tip::

    You can find a full example with fileuploads and JavaScript here:
    `play on CodePen <https://codepen.io/99grad/pen/VwMWNKW>`__

Using mutipart form-data
~~~~~~~~~~~~

Please refer to :ref:`this section <examples_jquery_fileuploads>` for detailled information on implementing 
the backend part of the file-upload.

By default, the nnrestapi will recursively iterate through the JSON from your request and look for
the special placeholder ``UPLOAD:/varname``. 

If it finds a fileupload in the multipart/form-data corresponding to the ``varname`` of the placeholder, 
it will automatically move the file to its destination and replace the ``UPLOAD:/varname`` in the JSON
with the path to the file, e.g. ``fileadmin/api/image.jpg``. 

Here is a basic example on creating a multipart/form-data request using pure JavaScript.

First, let's create a simple form in HTML. As we are retrieving the input-values manually, there is no need to
wrap the inputs in a ``<form>`` element:

.. code-block:: html

    <input type="file" id="file">
    <input id="title" />
    <input id="text" />
    <button id="submit">Send<button>

    <pre id="result"></pre>

And here is the JavaScript example using "VanillaJS" (nothing else but pure JavaScript) and FormData:

.. code-block:: javascript

    // put your url here
    var url = 'https://www.mywebsite.com/api/index';

    document.getElementById('submit').addEventListener('click', function() {
        
        var json = {
            title: document.getElementById('title').value,
            text: document.getElementById('text').value,
            image: 'UPLOAD:/myfile'
        };

        var formData = new FormData();
        formData.append('json', JSON.stringify(json));
        formData.append('myfile', document.getElementById('file').files[0]);

        var xhr = new XMLHttpRequest();
        xhr.overrideMimeType('application/json');
        xhr.open('POST', url);

        xhr.onload = function () {
            var data = JSON.parse( xhr.responseText );
            if (xhr.status != 200) {
                alert("Error " + xhr.status + ": " + data.error );
                return false;
            }
            document.getElementById('result').innerText = JSON.stringify( data );
        };

        xhr.onerror = function () {
            alert('Some other error... probably wrong url?');
        };

        xhr.send(formData);

    });

If you select The result of the above example will look something like this:

.. code-block:: json

    {
        "title": "This is the title",
        "text": "This is the bodytext",
        "image": "fileadmin/api/filename.jpg"
    }


.. tip::

    The nnrestapi can automatically create a Model from your JSON-data and attach SysFileReferences (FAL) relations.
    Please refer to :ref:`this example <example_model_mapping>` for in-depth information.

