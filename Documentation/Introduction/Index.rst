.. include:: ../Includes.txt

.. _introduction:

============
Introduction
============

.. danger::

   This extension will save you much, much time. Time that you will have to spend on other things. With your children, your wife or your mother-in-law. In other words: You might run out of excuses.

   If you have any doubts whether you can cope with this, you should **NOT** install nnhelpers. We are not responsible for any side effects that may occur in your developer life as a result of the time gained.

.. _what-it-does:

What does it do?
================

Let it (nn)help you, whenever things seem to get more compliated that they should be.

Let's look at a few examples:
-----------------------------

Let's say you have built an upload form. The user can upload an image in the frontend. You would like to have the image as **FAL on a model**.

Sounds like an everyday task, doesn't it? So: Let's ask Google. After countless variations of search phrases, you land on a promising doc at `docs.typo3.org <https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Fal/UsingFal/ExamplesFileFolder.html>`__. 

You work your way through many lines of information and end up with this statement: Not our business. Take care of it yourself, or steal it from `Helmut <https://github.com/helhum/upload_example>`__. You then spend then the next 30 minutes with lovely Helmut. Enjoying beautiful source codes. And searching desperately for the little snippet you need for your project.

I don't know how long it took you to solve this task the first time. For us it was about four hours. We were in Typo3 version 6 at that time. When the 7 LTS was released, we searched again because some things changed in the core. When version 8 LTS was released, it was again almost 2 hours. And then the adaption had to be done over and over again for ALL our extensions that implemented a file upload from the frontend context.

This is a nightmare and time that seems kind of pointless. I prefer to spend this time - without batting an eye - with my choleric mother-in-law.

**How would we solve this task today?**

| We would ask `nnhelpers` for help.
| So let's switch to the **backend module** of nnhelpers and have a look:
| 

.. figure:: ../Images/backend-01.jpg
   :class: with-shadow
   :alt: nnhelpers Backend Module
   :width: 100%

   The backend module shows all methods nicely grouped by topics.

| What were we looking for?
| Right. Something related to **FAL**. 
| So let's use the search function or scroll down to the **FAL section**.
|

.. figure:: ../Images/backend-02.gif
   :class: with-shadow
   :alt: nnhelpers Backend Module
   :width: 100%

   Every method has detailed examples and use-cases.


| `setInModel()` sounds pretty much like the thing we're looking for. 
| Lets look at an the examples.
|
| Now wait, are you really telling me, all I need is this f..g line of code?
| WTF? No `ObjectManager->get()`, no `@inject`? Just a no-brainer **ONELINER**?
| 
| And the best part is: **This onliner won't ever change.**
| Not for Typo3 Version 7. Not for Typo3 11. We promise.
|

.. code-block:: php

   \nn\t3::Fal()->setInModel( $model, 'fieldname', 'path/to/image.jpg' );

| Well, what if the user uploads multiple images?
| Sure, I could use a `foreach`. But wait... there's more!
| 

.. code-block:: php

   \nn\t3::Fal()->setInModel( $model, 'fieldname', ['image-1.jpg', 'image-2.jpg'] );

| **Yeah, nice. But this is cheating.**
| My app is more complex. The user can set a title and description for the image.
| Ehm. Wait a minute.
|

.. code-block:: php

   \nn\t3::Fal()->setInModel( $member, 'fieldname', ['publicUrl'=>'01.jpg', 'title'=>'Titel', 'description'=>'...'] );

| **HA! Got you. You are mixing things up here.**
| Now the user can't upload multiple files with titles and descriptions.
| But damn, what is this?
|

.. code-block:: php

   \nn\t3::Fal()->setInModel( $member, 'fieldname', [
      ['publicUrl'=>'01.jpg', 'title'=>'Titel', 'description'=>'...'],
      ['publicUrl'=>'02.jpg', 'title'=>'Titel', 'description'=>'...'],
   ]);

| **Yeah, but that is breaking the rules I learned at the university!! This is ugly!!**
| Right. But if it is simple, we simply don't care. 
| This is a line of code you can remember. And if not: next time you will know, where to find it.
| 
| **You still don't like it?**
| Fine, then have a look at the source code and steal, what you need to build it by yourself. No need to leave the backend and dive into the source code of nnhelpers. It's all here where it should be.
|

.. figure:: ../Images/backend-03.gif
   :class: with-shadow
   :alt: nnhelpers Backend Module
   :width: 100%

   Every method has detailed examples and use-cases.

| Ok, it's your decision. ;)
| 


Got it? Stop thinking, start coding.
------------------------------------