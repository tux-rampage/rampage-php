.. _di.references:

Define Services For Dependency Injection
========================================

There are two options to refernce services for dependency injection which depends on your needs.

    * :ref:`di.references.sm`
    * :ref:`di.references.im`


.. _di.references.sm:

Option 1: ServiceManager Name/Alias
-----------------------------------

This is the simplest way of providing your services to the di framework.
Either you define the service directly with its :term:`fqcn` or you add an alias pointing to
the :term:`fqcn` .

.. code-block:: php

    return [
        'invokables' => [
            'my\app\ClassName' => 'my\app\ClassName', // Direct naming
            'MyService' => 'my\app\AnotherClassName',
        ],
        'aliases' => [
            'my\app\SomeInterface' => 'MyService', // Alias a class/interface to a service
        ]
    ];


.. _di.references.im:

Option 2: DI InstanceManager Alias
----------------------------------

This way is more flexible and allows you to define services and injections more precisely.
To do so you should create an alias Matching your service name in your DI config:

.. code-block:: php

    // di.config.php
    // Assuming "MyServiceName" and "AnotherServiceName" are defined by the ServiceManager
    return [
        'instance' => [
            'aliases' => [
                // This makes the services known to the di framework.
                'MyServiceName' => 'my\app\SomeInterface', // Pointing to an interface is sufficient
                'AnotherServiceName' => 'my\app\SomeClass', // Pointing to a class is more flexible
            ],
            'preferences' => [
                // Explicit definition to
                // use the services to provide the dependencies.
                'thirdparty\SomeInterface' => 'AnotherServiceName',
                'thirtparty\AbstractClass' => 'AnotherServiceName',

                // Let Zend\Di decide which alias provides the dependency
                // (implements or inhertis the interface)
                'foo\bar\AnotherInterface' => [ 'MyServiceName', 'AnotherServiceName' ],
            ]
        ]
    ];

.. note::

    When using an alias to provide a preference, the aliased class name must implement or inherit the
    the provided dependency class/interface.
    Zend\\Di **will** do a sanity check if this is the case (i.e. to pick the matching provider)!

