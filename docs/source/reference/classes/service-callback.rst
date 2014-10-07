.. _classes.service_callback:

ServiceCallbackDelegator
========================

This class allows to wrap a lazy load callback to a specific service.
Lazy means this class will only instanciate the service when the callback is about to be invoked.

The service locator may be injected or changed at any time. That means you can create the callback
without a service locator attached and assign it later.

The service locator must implement ``Zend\\ServiceManager\\ServiceLocatorInterface``, which is the case
for the zf2 ServiceManager.

.. note::

    Invoking such a callback wrapper that has no service locator, will
    throw a ``rampage\core\exceptions\LogicException``.


.. _classes.service_callback.usage:

Usage
-----

The constructor takes two arguments. The first one is the service name, which is required.
The second, optional, argument is the method name to call. If the method name is omitted,
the service object will be treated as invokable (i.e. for classes that implement ``__invoke()``).

.. _classes.service_callback.usage.example:

**Example:**

.. code-block:: php

    <?php

    // ...

    $callback = new rampage\core\ServiceCallbackDelegator('MyServiceName', 'someMethod');
    $callback->setServiceLocator($serviceManager); // assuming $serviceManager is a ServiceLocator

    (new Zend\EventManager\EventManager())->attach('somevent', $this, $callback);

This example would request the service ``MyServiceName`` from ``$serviceLocator`` when ``somevent``
is triggered on the EventManager.


Injecting the service locator
-----------------------------

The service locator can be set via ``setServiceLocator()`` on the callback instance at any time (See the :ref:`Usage Example <classes.service_callback.usage.example>` above).

