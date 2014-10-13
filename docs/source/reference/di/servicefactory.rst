.. _di.ServiceFactory:

DIServiceFactory
----------------

:ref:`di.coupling` allows the library to provide a service factory to create class
instances purely by dependency injection.

**Example:**

.. code-block:: php

    // Service config:
    return [
        'factories' => [
            'MyService' => new \rampage\core\services\DIServiceFactory(ServiceImplementation::class);
        ]
    ];
