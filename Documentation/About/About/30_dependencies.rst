.. include:: ../Includes.txt

.. _about_dependencies:

============
Dependencies
============

Sorry, we've sneaked something in.
-------------

This extension depends on `EXT:nnhelpers <https://extensions.typo3.org/extension/nnhelpers>`__ which takes care of 
most of the hardcore conversions: from JSON to Model, Array to FAL, FAL-Uploads and other strenuous things... 

If you look at the examples or source codes and see a line of code starting with ``\nn\t3`` then this is what
we are talking about. ``nnhelpers`` is basically just a wrapper for many methods and functions that TYPO3 offers
and that have been hard to find or have changed from version to version. Using ``nnhelpers`` in our extensions
has made it possible for `our company <https://www.99grad.de>`__ to develop and update extensions faster. 

Sorry for "sneaking this in" when you install nnrestapi... but it saved us many, many hours of lifetime.
