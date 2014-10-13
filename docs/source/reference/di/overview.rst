.. _di.coupling:

Cupling ServiceManager And Di
-----------------------------

Rampage combines those both components more closely than zf2.
The Di InstanceManager will use the ServiceManager to look up a class instance. This
allows you to call ``$serviceManager->get('di')->newInstance($classname);`` which will respect and inject
configured services at any time.

.. note::

    Doing this call in plain zf2 would cause the Di framework to create new instances for
    classes that haven't been created via Di and ignoring the configured services.

