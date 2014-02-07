.. quickstart:

Quickstart
==========

This section will show you how to set up a basic application and how to start.

We strongly recommend to use composer as dependency manager, since it will simplify
you life a lot and take care of your library dependencies. In fact this tutorial
will rely on it.

See `getcomposer.org <http://getcomposer.org/>`_ for download and installation instructions
(It's really simple since it's just a single phar file).


.. quickstart.installation:

Installation / Application Setup
--------------------------------

At first we'll define the dependencies of our application. By now we only need to add
``rampage-php/framework`` as only dependency. This already defines dependencies to the
essential zf2 components (i.e. zend-mvc)::

    php composer.phar require rampage-php/framework

This command will add the dependency to your composer.json. For more information on
the composer.json package file see the `Composer Documentation <http://getcomposer.org/doc/00-intro.md>`_.

After doing so, we can run the install command to download all dependencies and generate the autoload
files::

    php composer.phar install

After executing this command, there will be a ``vendor`` directory containing all dependencies.
To use them, all you need to do is to include ``vendor/autoload.php`` and all dependency classes
become available to you.

.. quickstart.skeleton

Creating A Skeleton
-------------------

After doing the composer magic, we only have the vendor dir. But now we want to create an
application skeleton to start with.
To simplify things, rampage comes with a tool to do so, a script called ``rampage-app-skeleton.php``

Since rampage-php enhances zf2, it uses the zf2 layout. That means all modules are basically namespaces.
In vanilla ZF2, you can only use the first namespace segment as module name which is quite limiting.
Usually the first segment is the vendor name. But rampage-php enhances this behavior and adds
the ability to use subnamespaces as well and map them to a dot-separated directory name.

Let's assume your application module should be namespaced "acme\myapp".
Now let's create the application layout with this modulename::

    php vendor/bin/rampage-app-skeleton.php acme.myapp

For windows this command should be::

    .\vendor\bin\rampage-app-skeleton.php acme.myapp

This will create:

* a ``public`` directory which will contain the webserver root.
* an ``application`` directory containing the application components
* the module skeleton fur you application located in ``application/modules/acme.myapp``

