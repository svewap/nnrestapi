.. include:: ../Includes.txt

.. _examples_jquery_fileuploads:

============
Uploading Files
============

How to upload files to your TYPO3 Rest Api with jQuery
------------

**Yes, nnrestapi can handle fileuploads.** 

If you have tried other TYPO3 Rest Api extensions you might know
this is the part where things tend to get complicated. With ``nnrestapi`` we have tried to keep file uploads
as simple as possible.

There are two possible ways of uploading files and attaching SysFileReferences (FAL) to your model.

.. tip::

    You can find a full example with fileuploads in jQuery here: 
    `play on CodePen <https://codepen.io/99grad/pen/NWajXGm>`__

Do-it-yourself (no fun)
~~~~~~~~~~~~

The first possibility: Take care of uploading the file yourself and add the file path to your model.
We are not going to explain this solution in depth, but the basic principle is:

- Upload a file using some custom made upload form

- Move the uploaded file to its destination in the ``fileadmin``

- Return the path to the file (e.g. ``fileadmin/myuploads/file.jpg``) to your frontend app

- set the filepath in the JSON. This can be done for single FAL FileReferences but also for ObjectStorages, e.g.
  
  .. code-block:: json
  
     {"image":"fileadmin/myuploads/file.jpg"}
     {"images":["fileadmin/myuploads/file.jpg"]}

- ``POST``, ``PUT`` or ``PATCH`` the JSON to your endpoint. Persist it.

The nnrestapi will automatically determine, of the field ``image`` is an ``ObjectStorage`` containing ``SysFileReferences``.
It will convert the file on the server to a ``SysFile``, create the ``SysFileReference`` and attach it to the Model.

.. hint::

    This is the way most other REST Apis handle file-uploads. Works fine – but there are some downsides to 
    this solution:

    - You need a **seperate logic** or form for the fileupload
    - The file is uploaded **before** the model is persisted: If the user aborts editing the record or closes
      the browser before saving, the file might be orphanded and never used as a FileReference. You will
      need some other solution to keep your server "clean".


.. _examples_fileuploads_multipart:

Using mutipart form-data (fun)
~~~~~~~~~~~~

The recommended way to upload files to your TYPO3 Restful Api is by using ``multipart/form-data``.

All you need is a normal file-upload field in your form. Then, before sending the ``POST``, ``PUT`` or ``PATCH`` request:

- Create the JSON-object you want to send by getting the values from the input-fields. 
  Just the way you would always do, e.g.

  .. code-block:: json
  
     {"title":"Test", "text":"nice!"}

- Set the special placeholder ``UPLOAD:/identifier`` for every fileupload at the place you want to have the FileReference, e.g.

  .. code-block:: json
  
     {"title":"Test", "text":"nice!", "image":"UPLOAD:/myfile"}

- Compose a ``multipart/form-data`` request using plain JavaScript, jQuery or axios.
  
  Attach the stringified JSON to the variable ``json`` of your multilpart form-data.
  
  Get the FileData from the fileupload-field using JavaScript and push it to multipart form-data using the 
  same variable-name you chose as a placeholder (``myfile`` in the example above)

- Send the request and let nnhelpers take care of the rest.


.. hint::

    Don't forget to set the ``Authentication Bearer``-header if you are adressing an endpoint that 
    requires Frontend-User Authentication... which is probably a good idea, when uploading
    files to your server ;) Read more here :ref:`examples_jquery_auth`


Example without Model-mapping
~~~~~~~~~~~~

Here is s step-by-step example:

.. rst-class:: bignums

1.  Create an endpoint to debug the result

    We will keep things simple and just debug the data that was uploaded.
    Create an endpoint with an ``postIndexAction``. 
    
    Please note that we are granting public access to this endpoint for test purposes only.
    
    You **definitely don't want to do this** in a production environment!

    .. code-block:: php

        <?php
        namespace My\Extname\Api;

        use Nng\Nnrestapi\Annotations as Api;

        class Index extends \Nng\Nnrestapi\Api\AbstractApi 
        {
            /**
             * @Api\Access("*")
             * @return array
             */
            public function postIndexAction()
            {
                $body = $this->request->getBody();
                return $body;
            }
        }

2.  Create your input fields in HTML

    Create a simple HTML document that has a file-input and some textfields.

    We are going to "grab" the values from the fields manually later, so you don't need to wrap the
    inputs in a ``<form>`` element. If you have been working with reactive data in Angular, VueJS or React, 
    you will know, why we are approaching things here this way ...

    .. code-block:: html

        <input type="file" id="file">
        <input id="title" />
        <input id="text" />
        <button>Send<button>

        <pre id="result"></pre>

3.  Create the JavaScript to send the ``multipart/form-data``.

    The important part is using the special ``UPLOAD:/varname`` placeholder in your JSON:
    
    By default, the nnrestapi will recursively iterate through the JSON you have passed and look for
    all ``UPLOAD:/varname`` strings. If it finds a fileupload corresponding to the ``varname`` in
    the placeholder, it will automatically move the file to its destination and replace the
    ``UPLOAD:/varname`` with the path to the file, e.g. ``fileadmin/api/image.jpg``. 

    .. code-block:: javascript

        // put your url here
        const url = 'https://www.mywebsite.com/api/index';

        $('button').click(() => {

            // grab the fields. Use placeholder for "image"            
            const data = {
                title: $('#title').val(),
                text: $('#text').val(),
                image: 'UPLOAD:/myfile'
            };
            
            // Create FormData for sending multipart/form-data
            const formData = new FormData();

            // Append JSON-string, use variable "json"
            formData.append('json', JSON.stringify(data));

            // Append filedata of first file, use "myfile" from placeholder 
            formData.append('myfile', $('#file')[0].files[0]);
            
            // Send request and degub result in textfield
            $.ajax({
                url: $('#url').val(),
                type: $('#request-method').val(),
                cache: false,
                contentType: false,
                processData: false,
                data: formData
            }).done((result) => {
                $('#result').text( JSON.stringify(result) );
            }).fail((error) => {
                $('#result').text( 'ERROR: ' + JSON.stringify(error) );
            });
        });


    The backend will automatically detect, that you have filedata attached to your request. 
    It will move the file to the destination defined in :ref:`annotations_upload` and replace 
    the placeholder in the payload.

    The result of the above example will look something like this:

    .. code-block:: json

        {
            "title": "This is the title",
            "text": "This is the bodytext",
            "image": "fileadmin/api/filename.jpg"
        }

    .. hint::

        The automatic upload only allows file-extensions and -types defined in ``$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']``.
        To add additional types like SVGs you can put this line in ``ext_localconf.php`` of your extension - or change the value in 
        the TYPO3 Install-Tool:

        .. code-block:: php

            $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'] .= ',svg';


.. _example_model_mapping:

Example with Model-mapping
~~~~~~~~~~~~

Let's use the above example and modify the scripts to automatically create a Model with a FAL (SysFileReference) in your TYPO3 Restful Api.

.. rst-class:: bignums

1.  Create a Model

    Do what you always do to create a Model. Nothing special to the ``ext_tables.sql``, ``Configuration/TCA/...`` settings.
    And nothing special to the Domain Model. 
    
    .. hint::

        If you like, extend your model from ``Nng\Nnrestapi\Domain\Model\AbstractRestApiModel``.
        This is not mandatory – but the ``AbstractRestApiModel`` comes equipped with getters and setters to access ``tstamp`` and ``crdate`` etc.

    .. code-block:: php

        <?php
        namespace My\Extname\Domain\Model;

        use \TYPO3\CMS\Extbase\Domain\Model\FileReference;

        class MyModel extends \Nng\Nnrestapi\Domain\Model\AbstractRestApiModel
        {
            /**
             * @var string
             */
            protected $title;

            /**
             * @var string
             */
            protected $text;

            /**
             * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
             */
            protected $image;
                
            /**
             * @return  string
             */
            public function getTitle() 
            {
                return $this->title;
            }

            /**
             * @param   string $title title
             * @return  self
             */
            public function setTitle($title) 
            {
                $this->title = $title;
                return $this;
            }

            /**
             * @return  string
             */
            public function getText() 
            {
                return $this->text;
            }

            /**
             * @param   string $text text
             * @return  self
             */
            public function setText($text) 
            {
                $this->text = $text;
                return $this;
            }

            /**
             * @return  \TYPO3\CMS\Extbase\Domain\Model\FileReference
             */
            public function getImage() 
            {
                return $this->image;
            }

            /**
             * @param   \TYPO3\CMS\Extbase\Domain\Model\FileReference $image image
             * @return  self
             */
            public function setImage($image) 
            {
                $this->image = $image;
                return $this;
            }
        }

2.  Let yor endpoint do the mapping

    Modify you ``postIndexAction`` so it will automatically create a ``MyModel`` from the JSON you passed.

    We will use Dependency Injection to accomplish this. If you want to do it manually, have a look at this nice TYPO3 helper: 
    `\nn\t3::Convert( $arr )->toModel() <https://docs.typo3.org/p/nng/nnhelpers/1.4/en-us/Helpers/Classes/convert.html#nn-t3-convert-tomodel-classname-null-parentmodel-null>`__

    Have a look at :ref:`this chapter <examples_article>` for more examples.

    .. code-block:: php

        <?php
        namespace My\Extname\Api;

        use My\Extname\Domain\Model\MyModel;
        use Nng\Nnrestapi\Annotations as Api;

        class Index extends \Nng\Nnrestapi\Api\AbstractApi {
        
            /**
             * @Api\Access("*")
             * @param MyModel $model
             * @return array
             */
            public function postIndexAction( MyModel $model = null )
            {
                // Persist the model in database. No Repo needed :)
                \nn\t3::Db()->update( $model );
                return $model;
            }

        }

Adding FileReferences to existing ObjectStorages
~~~~~~~~~~~~

The recipe above with using multipart form-data and the ``UPLOAD:/varname`` placeholder in your JSON will work in any context,
even if you are using ``ObjectStorages`` to attach an array of multiple FileReferences to your model.

All you need to do is pass an array of paths and/or placeholders in your JSON request.

Here we are keeping two existing FileReferences that have already been attached to the Model in a previous request and
are adding an additional, new file-upload to the Model:

.. code-block:: json

    {
        "title": "My Title",
        "text: "My Text",
        "images": [
            "fileadmin/path/existing/file-1.jpg",
            "UPLOAD:/newfile",
            "fileadmin/path/existing/file-2.jpg"
        ]
    }

Of course you will have to modify the above JavaScript to handle multiple files and create an Array of paths and placeholders.
And you need to modify the ``MyModel``-Class to use an ``ObjectStorage`` instead of a single FileReference.

Removing FileReferences from a Model or ObjectStorage
~~~~~~~~~~~~

To remove a FileReference from a Model or ObjectStorage, simple remove it from the JSON-Object or Array and send it 
to the Rest Api. Let's look at an example.

To remove ``file-1.jpg`` from the ObjectStorage of the following Model, all we need to do is **remove it from the Array**:

.. code-block:: json

    {
        "title": "My Title",
        "text: "My Text",
        "images": [
            "fileadmin/path/existing/file-1.jpg",
            "fileadmin/path/existing/file-2.jpg"
        ]
    }

... and send the **resulting JSON** to the Rest Api:

.. code-block:: json

    {
        "title": "My Title",
        "text: "My Text",
        "images": [
            "fileadmin/path/existing/file-2.jpg"
        ]
    }

The same can be done, if you only have a **single FileReference** property in your Model (instead of an ObjectStorage).
Let's remove ``file.jpg`` from the Model:

.. code-block:: json

    {
        "title": "My Title",
        "text: "My Text",
        "image": "fileadmin/path/existing/file.jpg"
    }

... by simply setting the field ``image`` to ``null``, ``false`` or an empty string:

.. code-block:: json

    {
        "title": "My Title",
        "text: "My Text",
        "image": ""
    }


Modifying FileReferences properties (image-title, alternative-text etc.)
~~~~~~~~~~~~

If you look at the response of our ``Index->postIndexAction()`` above, you may have noticed, that the
nnrestapi is actually not only returning the path to the publicUrl of the FileReferences, but an object
containing all relevant information about the FileReference, including fields like ``title``, ``alternative``,
``description`` and ``crop``.

This is, because sending the FileReference information in this form:

.. code-block:: json

    {
        "image": "fileadmin/path/existing/file.jpg"
    }

... is actually just a "convenient" and shorthand way of sending it like this:

.. code-block:: json

    {
        "image": {
            "publicUrl": "fileadmin/path/existing/file.jpg"
        }
    }

If you are not planning to modify anything aside of the FileReference itself, the above syntax is fine.
But the fact, that you can also define the FileReference in the latter way makes it possible to
manipulate and persist other fields of the FileReference:

.. code-block:: json

    {
        "image": {
            "publicUrl": "fileadmin/path/existing/file.jpg",
            "title": "My image title",
            "description": "A nice picture"
        }
    }

Under the hood, the ``nnrestapi`` uses the `\nn\t3::Fal()->setInModel() <https://docs.typo3.org/p/nng/nnhelpers/1.4/en-us/Introduction/Index.html#let-s-look-at-a-few-examples>`__
method. Have a look at `EXT:nnhelper <https://docs.typo3.org/p/nng/nnhelpers/1.4/en-us/Index.html>`__ to find out more.