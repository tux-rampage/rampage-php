.. _di.AbstractServiceFactory:

Abstract Service Factory
------------------------

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

