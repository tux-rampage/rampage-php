.. _resources:

Module/Component Resources
==========================

.. versionadded:: 1.0

Some of your modules may need to provide public resources like images, css or js files.
To allow bundeling them within your module, rampage offers a resource locator system that will automatically
publish them. You don't need to copy resources manually or make your vendor directory available to the webserver.


.. _resources.defining:

Defining Module Resources
-------------------------

Defining module resources is pretty easy. You just need to add them to your zf2 module config:

.. code-block:: php

    class Module
    {
        public function getConfig()
        {
            return [
                // ...
                'rampage' => [
                    'resources' => [
                        // Minimal, define only the base directory:
                        'foo.bar' => __DIR__ . '/resources',

                        // More detailed, allows to define additional resource types:
                        'foo.baz' => [
                            'base' => __DIR__ . '/resources',
                            'xml' => __DIR__ . '/resources/xml',
                        ],
                    ]
                ]
            ];
        }
    }

If you're using the manifest.xml for your modules, you can define them in the resources tag:

.. code-block:: xml

    <manifest xmlns="http://www.linux-rampage.org/ModuleManifest" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.linux-rampage.org/ModuleManifest http://www.linux-rampage.org/ModuleManifest ">
            <resources>
                <paths>
                    <path scope="foo.bar" path="resource" />
                    <path scope="foo.baz" path="resource" />
                    <path scope="foo.baz" type="xml" path="resource/xml" />
                </paths>
            </resources>
    </manifest>


.. _resources.helper:

Accessing resources in views
----------------------------

To access resources in views, you can use the `resourceurl` helper. The argument passed to this helper is the relative
file path prefixed with the scope like this: `scope::file/path.css`.

**Example:**

.. code-block:: html+php

    <img src="<?php echo $this->resourceUrl('my.module::images/foo.gif') ?>" />


.. _resources.templatelocator:

Addressing templates
--------------------

Templates will also be populated by the resource locator. You can address them by
prepending the template path with the scope like this: `scope/templatepath`.

**Example:**

.. code-block:: php

    $viewModel = new ViewModel();
    $viewModel->setTemplate('my.module/some/template');


.. _resources.publishing:

Static resource publishing
--------------------------

There is also a way to publish resources to the `public` directory for static delivery.
This is useful for production environments, where performance is important.

There are two ways to do this:

1. :ref:`resources.publishing.default`
2. :ref:`resources.publishing.custom`


.. _resources.publishing.default:

Use the publishing controller
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 1.1.1

The easiest way to do this, is to register the resources controller for publishing.

.. code-block:: php

    // module.config.php
    return [
        'console' => [
            'router' => [
                'routes' => [
                    'publish-resources' => \rampage\core\controllers\ResourcesController::getConsoleRouteConfig(),
                ]
            ]
        ],
    ];

You may also pass the route to `getConsoleRouteConfig()` if you don't like `publish resources` as route or create an own route yourself
pointing to the `publish` action of `rampage\\core\\controllers\\ResourcesController`.

.. note::

    The `getConsoleRouteConfig()` method is available since 1.1.1, prior that version you have to register the route config on your own.

.. code-block:: php

    return [
        'console' => [
            'router' => [
                'routes' => [
                    'publish-resources' => [
                        'options' => [
                            'route' => 'publish resources',
                            'defaults' => [
                                'controller' => 'rampage\\core\\controllers\\ResourcesController',
                                'action' => 'publish'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ];


.. _resources.publishing.custom:

Implement or modify the publishing strategy
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The controller uses the service `rampage.ResourcePublishingStrategy` which must implement `rampage\\core\\resources\\PublishingStrategyInterface`.
By default this interface is implemented by `rampage\\core\\resources\\StaticResourcePublishingStrategy`.

The default strategy will publish all resources to `static/` in the `public` directory.


.. _resources.special_notes:

Special Controllers/Routes
--------------------------

When implementing an authentication strategy which protects all of your routes from unauthorized access, you should be aware that
the resource publishing strategy uses a ZF2 route/controller to publish static resources from your vendor or module directories.

The controller class is `rampage\\core\\controllers\\ResourcesController` and it is registerd as `rampage.cli.resources` in the
controller manager. The route for this controller is called `rampage.core.resources`.

If you do not allow this route/controller, public resources from your modules may not be served.
