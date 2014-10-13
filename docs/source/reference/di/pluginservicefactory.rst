.. _di.PluginServiceFactory:

DIPluginServiceFactory
======================

As much as :doc:`servicefactory` this allows defining a class to be instanciated by dependency injection.
The difference to the :doc:`servicefactory` is that this may be used for zf2 PluginManagers.

**Example:**

.. code-block:: php

    // PluginManager config:
    return [
        'factories' => [
            'myhelper' => new \rampage\core\services\DIPluginServiceFactory(MyHelper::class);
        ]
    ];
