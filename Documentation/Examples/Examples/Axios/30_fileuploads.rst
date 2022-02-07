.. include:: ../Includes.txt

.. _examples_axios_fileuploads:

============
Uploading Files
============

.. hint::

    This chapter explains how to upload files to the ``nnrestapi`` using the JavaScript library **axios**.

    If you are interested in finding out, how to create an endpoint that processes the fileupload, attaches the SysFileReference 
    to a model and then persists the model in the database, please refer to the examples in :ref:`this section <examples_jquery_fileuploads>`

How to upload files using the axios library
------------

.. tip::

    You can find a full example with fileuploads and axios here:
    `play on CodePen <https://codepen.io/99grad/pen/JjrJojb>`__

Using mutipart form-data
~~~~~~~~~~~~

Please refer to :ref:`this section <examples_jquery_fileuploads>` for detailled information on implementing 
the backend part of the file-upload.

By default, the nnrestapi will recursively iterate through the JSON from your request and look for
the special placeholder ``UPLOAD:/varname``. 

If it finds a fileupload in the multipart/form-data corresponding to the ``varname`` of the placeholder, 
it will automatically move the file to its destination and replace the ``UPLOAD:/varname`` in the JSON
with the path to the file, e.g. ``fileadmin/api/image.jpg``. 

Here is a basic example on creating a multipart/form-data request using axios.

First, let's create a simple form in HTML. As we are retrieving the input-values manually, there is no need to
wrap the inputs in a ``<form>`` element:

.. code-block:: html

    <input type="file" id="file">
    <input id="title" />
    <input id="text" />
    <button id="submit">Send<button>

    <pre id="result"></pre>

And here is the JavaScript example using axios and FormData:

.. code-block:: javascript

    // put your url here
    const url = 'https://www.mywebsite.com/api/index';

    document.getElementById('submit').addEventListener('click', () => {

        // grab all fields from the form
        const json = {
            title: document.getElementById('title').value,
            text: document.getElementById('text').value,
            image: 'UPLOAD:/myfile'
        };

        // create a FormData        
        let formData = new FormData();

        // append the stringified version of the JS-Object in the variable "json"
        formData.append('json', JSON.stringify(json));

        // append the selected file
        formData.append('myfile', document.getElementById('file').files[0]);

        // send the request
        axios({
            url: url,
            method: 'post',
            data: formData
        }).then(({data}) => {
            document.getElementById('result').innerText = JSON.stringify( data );
        }).catch(({response}) => {
            alert( `Error ${response.status}: ${response.data.error}` );
        });

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

