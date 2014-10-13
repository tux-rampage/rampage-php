.. _di:

Zend Dependency Injcetion Enhancements
======================================

Rampage-PHP provides a couple of enhancements to the default Di/ServiceManager
implementations of zf2.

.. _di.coupling:

Cupling ServiceManager and Di
-----------------------------

Rampage combines those both components more closely than zf2.
The Di InstanceManager will use the ServiceManager to look up a class instance. This
allows you to call ``$serviceManager->get('di')->newInstance($classname);`` which will respect and inject
configured services at any time.

.. note::

    Doing this call in plain zf2 would cause the Di framework to create new instances for
    classes that haven't been created via Di and ignoring the configured services.


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


.. _di.AbstractServiceFactory:

Abstract DI Service Factory
---------------------------

As in zf2, there is an abstract service factory, which allows you to request a service by a classname
 without the need to define it explicitly. It will be automatically instanciated via the di framework then.

The difference to the zf2 implementation is, that all configured services are respected by
the di framework (see :ref:`di.coupling`).

So the following code will work as expected.

**di.config.php:**

.. code-block:: php

    return [
        'factories' => [ 'DbAdapter' => MyDbAdapterFactory::class ],
        'aliases' => [ 'Zend\Db\Adapter\AdapterInterface' => 'DbAdapter' ]
    ]

**SomeClass.php:**

.. code-block:: php

    namespace my\app;

    use Zend\Db\Adapter\AdapterInterface as DbAdapterInterface;

    class SomeClass
    {
        /**
         * @var DbAdapterInterface
         */
        protected $adapter;

        /**
         * Constructor dependency to a Zend\Db\Adapter\AdapterInterface
         */
        public function __construct(DbAdapterInterface $adapter)
        {
            $this->adapter = $adapter;
        }

        // ...
    }

**Usage:**

.. code-block:: php

    namespace my\app;

    // ...

    // $instance will be created via Di and the "DbAdapter" service will be injected
    // via constructor.
    $instance = $serviceLocator->get(SomeClass::class);

    // ...


.. _di.references:

Possibilities to refernece services for dependency injection
------------------------------------------------------------

There are two options to refernce services for dependency injection which depends on your needs.

    * :ref:`di.references.sm`
    * :ref:`di.references.im`


.. _di.references.sm:

Option 1: ServiceManager name/alias
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is the simplest way of providing your services to the di framework.
Either you define the service directly with its fqcn[1]_ or you add an alias with
the fqcn[1]_.

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

Option 2: DI InstanceManager alias
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This way is more flexible and allows you to define services and injections more precisely.
To do so you should create an alias Matching your service name in your DI config:

.. code-block:: php

    // di.config.php
    // Assuming "MyServiceName" and "AnotherServiceName" are defined by the ServiceManager
    return [
        'instance' => [
            'aliases' => [
                // This makes the services known to the di framework.
                'MyServiceName' => 'my\app\SomeInterface`, // Pointing to an interface is sufficient
                'AnotherServiceName' => 'my\app\SomeClass`, // Pointing to a class is more flexible
            ],
            'preferences' => [
                // Explicit definition to
                // use the services to provide the dependencies.
                'thirdparty\SomeInterface' => 'AnotherServiceName',
                'thirtparty\AbstractClass' => 'AnotherServiceName',

                // Let Zend\Di decide which alias provides the dependency (implements or inhertis the interface)
                'foo\bar\AnotherInterface' => [ 'MyServiceName', 'AnotherServiceName' ],
            ]
        ]
    ];

.. note::

    When using an alias to provide a preference, the aliased class name must implement or inherit the
    the provided dependency class.
    Zend\Di **will** do a sanity check if this is the case (i.e. to pick the matching provider)!



.. [1] full qualified class name
