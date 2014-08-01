.. resources

Module/Component Resources
==========================

Some of your modules may need to provide public resources like images, css or js files.
To allow bundeling them within your module, rampage offers a resource locator system that will automatically
publish them. You don't need to copy resources manually or make your vendor directory available to the webserver.

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


Accessing resources in views
----------------------------

To access resources in views, you can use the `resourceurl` helper. The argument passed to this helper is the relative
file path prefixed with the scope like this: `scope::file/path.css`.

.. code-block:: html+php

    <img src="<?php echo $this->resourceUrl('my.module::images/foo.gif') ?>" />


Addressing templates
--------------------

Templates will also populated by the resource locator. You can address them by prepending them with the scope 
like this: `scope/templatepath`.

.. code-block:: php

    $viewModel = new ViewModel();
    $viewModel->setTemplate('my.module/some/template');


