.. _theming:

Themes Support
==============

Rampage-PHP allows you to register cascading themes to apply different styles to your
application in a flexible way.

Themes can depend on another theme to which it will fall back when a resource is not found.

Of course you can overwrite module resources like templates, css, js, images, etc.
In this case the resource will be loaded from your (or the parent) theme if present.

.. _theming.define:

Defining Themes
---------------

Themes can be defined in your ZF2 module (or application) configuration as follows:

.. code-block:: php

    <?php

    namespace my\component;

    class Module
    {
        public function getConfig()
        {
            return [
                'rampage' => [
                    'themes' => [
                        'my.theme.name' => [
                            // Simple path definition following the default layout
                            'path' => __DIR__ . '/../theme',
                            'fallbacks' = [ 'parent.theme', rampage\core\resources\Theme::DEFAULT_THEME ],
                        ],

                        'my.other.theme.name' => [
                            // More detailed path definition
                            'path' => [
                                'base' => __DIR__ . '/../theme',
                                'template' => __DIR__ . '/../theme/templates',
                            ],
                            'fallbacks' = [ 'my.theme.name', 'parent.theme', rampage\core\resources\Theme::DEFAULT_THEME ],
                        ]

                    ]
                ]
            ];
        }
    }

One module may define multiple themes if needed.

.. note::

    The theme will try the fallbacks in the order in which they're defined.
    It will fallback flat through those themes, **not** in depth.

    i.e. The fallbacks of a fallback theme are not visited.


.. _theming.dirlayout:

Theme Directory Layout
----------------------

Within the defined theme directory, there should be two directories.

* ``public`` Which contains all public assets
* ``template`` Which contains template files


.. _theming.overwrite_resources:

Overwriting Module Resources
----------------------------

Of course it is possible to overwrite module resources, like templates or public assets, in a theme.
To do so simply place the file ine a directory named like the module's resource name (See :ref:`resources.defining` for details).

**Example:**

* theme directory
    - public
        + module.resource.name
            - some/public/file.css
    - template
        + module.resource.name
            - some/template.phtml
            - some-other-template.phtml

