.. rampage-php documentation master file, created by
   sphinx-quickstart on Mon Jan 20 08:15:37 2014.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Reference Guide to rampage-php
==============================

Welcome to the reference guide to rampage-php - an addon library to `ZendFramework 2 <http://framework.zend.com/>`_.

.. toctree::
    :hidden:
    :maxdepth: 4

    reference/quickstart
    reference/di
    reference/resources
    reference/theming
    reference/filesystem
    reference/convenience-classes
    components
    glossary


.. _reference.overview:

Overview
--------

Rampage-PHP is a framework enhancement library for ZendFramework 2.
It is supposed to offer some convenience components.

As most developers don't like to write the same code fragments over and over again.
This is the point where rampage-php comes in.

Read the :doc:`Quickstart Guide <reference/quickstart>` to get started.


.. _reference.overview.key-features:

Key Features
------------

* :doc:`Tight integration between Di and ServiceManager <reference/di>`
* XML based module configurations (XSD provided)
* :ref:`Powerful resource locators for module resource files (i.e. js and css) <resources>`
* :ref:`Advanced url locators/helpers <resources.helper>`
* :ref:`Cascading themes support <theming>`
* Base implementation to create mergable XML config models
* :ref:`Service callback wrappers <classes.service_callback>`
* :ref:`Filesystem Containers <filesystem>`

