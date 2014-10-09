.. _classes.gracefularrayaccess:

GracefulArrayAccess
===================

This class provides graceful access to arrays or objects that implement ``ArrayAccess``.
This means requesting a key that does not exist will not trigger a warning, but return a default instead.


Usage Example
-------------

.. code-block:: php

    $wrapper = new GracefulArrayAccess(['bar' => true]);

    var_dump($wrapper->get('foo', 'default value'));
    var_dump($wrapper->get('foo'));
    var_dump($wrapper->get('bar'));

This example will output::

    string(13) "default value"
    NULL
    bool(true)

