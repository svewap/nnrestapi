.. include:: ../Includes.txt

.. _introduction:

============
What is a RESTful Api?
============

.. tip::

    If you are not new to the topic, skip this. It's going to bore you ;)


A simplified explanation
----------------

If you are new to the topic or you are wondering, what a **REST Api ("Representational State Transfer")** is all about and why it is 
hyped, let's put it in a few simple words. This is no explanation, that a "real" programmer would accept. But therefore it will be easy to understand ;) 

*And yes, ok, we admit, we're writing this to get Google more interested in this text ;)*

Roundtrips? Old-school.
~~~~~~~

If you've been working with TYPO3 for a while, you've probably been thinking in terms of "content-pages" and "page reloads":
You have your backend. The backend has many pages. And in the frontend, clicking on a menu will navigate to the requested page. 

With every page you visit, there is a "roundtrip". The screen goes blank for a moment. The backend renders the templates and 
responds with everything needed to display the page in the frontend: The complete markup (HTML-code), 
the styles (CSS) and a little bit of JavaScript-magic to make things more interesting and interactive.

Time for the SPAs
~~~~~~~

Well, nowadays, things have changed a little. Everybody is excited about `Single Page Applications (SPAs) <https://de.wikipedia.org/wiki/Single-Page-Webanwendung>`__
and `Progressive Web Apps (PWAs) <https://de.wikipedia.org/wiki/Progressive_Web_App>`__.

These solutions "feel" a lot more like real, native apps and offer a great User Experience (UX). A good SPA or PWA might also dynamically load
content from a backend, but most of the time it doesn't *feel* like a page reload. The data is loaded and persisted "in the background" without
the screen going blank and then successively being re-rendered. I bet, you wouldn't believe that an Desktop-App like `Slack <https://slack.com/>`__ 
or `Figma <https://www.figma.com/>`__ are based on web-technology!

If you look at one of the SPAs in depth, you'll notice a different concept and architecture. Most of the SPAs are not fetching HTML and CSS
from the backend (the way everybody did it in the last century with ``jQuery.load()``). They actually have most of the markup and styles of all 
pages and the complete application loaded on the first page load. 

The rendering of the markup and the communication with the backend is solved using JavaScript.

Let's talk JSON
~~~~~~~

JavaScript does just about everything in a Single Page Application or Progressive Web App. It's the engine and brain of the frontend. And because 
it takes care of dynamically creating and rendering the markup, there is no real need to load bits and chunks of markup from the backend. 

Instead, JavaScript will load raw data-Objects and then "convert" the data to something visible and readable for the user.
The communication between JavaScript and the backend is based on the JSON-format (at least in most applications). 
Nobody, who has ever touched a ``JSON`` wants to see ``XML`` again for the rest of his life.

**So here is a JSON:**

.. code-block:: json

    {"title":"Nice title", "text":"And this is the text!"}

Looks pretty straight forward, right? The JavaScript in the frontend application says: "GET me that data" and the backend delivers the 
above string. From there, it's only a one liner to convert the string to a "normal" JavaScript Object:

.. code-block:: javascript

    let data = JSON.parse('{"title":"Nice title", "text":"And this is the text!"}');
    console.log( data.title );

| What about sending data back to the server, for example, if you wanted to change the title and save it in the database? 
Let's modify the title and send it back:

.. code-block:: javascript

    data.title = 'A new title';
    fetch('https://www.mysite.com/path/to/my/api', {
        method: 'POST',
        body: JSON.stringify(data)
    });

That's what makes working with JSON so great. Data (Objects) from the request are ready-to-use in your script without any hassle. 
And modifying them and getting them back to the server is fun.


GETting and PUTting things
~~~~~~~

Things start to get fascinating, if you imagine your JSON-object was like a real "object" in a shelf.

Like every book, every object has its own place in the shelf. The place is defined by the endpoint (or URL).
(Behind the scenes, most of the time, the "shelf" just a simple database with rows and columns. 
The shelf-number would correspond to the ``uid`` of the entry)

To now check, which object is in the first shelf, we will send a request to the API and GET the content from
shelf number 1. To do this all we need to know, is the "unique place" the object has in the shelf.
And this is nothing other than the URL!

The URL identifies a unique and clear "position" of an object (you could also say "entity") on the server.

.. code-block:: php

    https://www.mywebsite.com/api/shelf/1

Fine. We got a data-row. Maybe the data contains the title and description of a book.
Now we modify the title and want to put the book back on the shelf. So, again, we tell the api:
"Listen, here is the book. I modified the title. Could you put it back in shelf number 1?"

We want to put it back in shelf number 1... so the URL we call should be:

.. code-block:: php

    https://www.mywebsite.com/api/shelf/1

**But wait... that is the same URL?** Right. So how can the backend know what we want to do? 
With the first request we want to GET the book and with the second one we want to PUT the book back on the shelf.
But that can't work, if it is the same URL, right?

The request-type makes the difference!
~~~~~~~

Most "frontenders" coming from jQuery or the classic HTML-pages will now think: Simple. I would just 
use different URLs: One for reading the data, one for writing it. Or they would use URL-parameters
like ``?action=update`` to make a difference between the two requests.

I bet, up until now, you have probably only worked with GET- or POST-requests. 

A **GET-requests** is the thing you can "read" in the URL like ``https://www.mywebsite.com/path/to.php?target=somewhere``).
And you probably know **POST-requests** from HTML-forms. The POST-body (the form-data) is sent "invisible" to the server
after submitting the form.

One of the main ideas with a REST Api, is to use the HTTP-Request-Type to make clear, what you want to do.
The idea is: You will be sending a request to the same URL, but using different request types.

.. code-block:: php

    // get the data
    GET https://www.mywebsite.com/api/shelf/1

    // write the data
    POST https://www.mywebsite.com/api/shelf/1

The nice thing: There are request types for just about anything you want to do.
Some great minds came up with the following definition:

* ``GET`` will retrieve an existing entry
* ``POST`` will create a new entry
* ``PUT`` will replace an existing entry (all fields are updated)
* ``PATCH`` will update certain fields of an existing entry
* ``DELETE`` will delete an existing entry

It is pretty much up to you, which request type you use to achieve what. And people occasionally get confused about the exact difference between ``PUT`` and ``PATCH``.
But things get easier to understand later, if you stick with the standards.

Where to go from here?
~~~~~~~

This TYPO3 extension is not only a good starting point to get things "up and running" in a few minutes – it also offers a nice module in 
the backend for testing your endpoints. And it comes with many examples and step-by-step tutorials about the front- and backend-implementation.

We hope you have fun using ``nnrestapi`` - feel free to contact us if you find a bug or have ideas on how to improve the extension.