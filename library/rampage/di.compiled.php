<?php return array (
  'rampage\\orm\\RepositoryInterface' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
      'setName' => false,
      'setConfig' => false,
    ),
    'parameters' => 
    array (
      'setName' => 
      array (
        'rampage\\orm\\RepositoryInterface::setName:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setConfig' => 
      array (
        'rampage\\orm\\RepositoryInterface::setConfig:0' => 
        array (
          0 => 'config',
          1 => 'rampage\\orm\\ConfigInterface',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\orm\\ConfigInterface' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\orm\\Config' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\EventManager\\EventsCapableInterface',
      1 => 'Zend\\EventManager\\EventManagerAwareInterface',
      2 => 'rampage\\orm\\ConfigInterface',
      3 => 'rampage\\core\\xml\\Config',
      4 => 'Zend\\EventManager\\EventManagerAwareInterface',
      5 => 'Zend\\EventManager\\EventsCapableInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setEventManager' => true,
      'setXml' => false,
      'setMergeRules' => false,
      'getEventManager' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\orm\\Config::__construct:0' => 
        array (
          0 => 'file',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setEventManager' => 
      array (
        'rampage\\orm\\Config::setEventManager:0' => 
        array (
          0 => 'eventManager',
          1 => 'Zend\\EventManager\\EventManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setXml' => 
      array (
        'rampage\\orm\\Config::setXml:0' => 
        array (
          0 => 'xml',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setMergeRules' => 
      array (
        'rampage\\orm\\Config::setMergeRules:0' => 
        array (
          0 => 'rules',
          1 => 'rampage\\core\\xml\\mergerule\\ChainedRule',
          2 => false,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\orm\\RepositoryFactory' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\ServiceManager\\ServiceLocatorAwareInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      'setServiceLocator' => true,
      'setConfig' => false,
      'setRepositoryInstance' => false,
      'getServiceLocator' => true,
    ),
    'parameters' => 
    array (
      'setServiceLocator' => 
      array (
        'rampage\\orm\\RepositoryFactory::setServiceLocator:0' => 
        array (
          0 => 'serviceLocator',
          1 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setConfig' => 
      array (
        'rampage\\orm\\RepositoryFactory::setConfig:0' => 
        array (
          0 => 'config',
          1 => 'rampage\\orm\\ConfigInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setRepositoryInstance' => 
      array (
        'rampage\\orm\\RepositoryFactory::setRepositoryInstance:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\orm\\RepositoryFactory::setRepositoryInstance:1' => 
        array (
          0 => 'instance',
          1 => 'rampage\\orm\\RepositoryInterface',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\data\\Object' => 
  array (
    'supertypes' => 
    array (
      0 => 'rampage\\core\\data\\ObjectInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setIdFieldName' => false,
      'setId' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\data\\Object::__construct:0' => 
        array (
          0 => 'data',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setIdFieldName' => 
      array (
        'rampage\\core\\data\\Object::setIdFieldName:0' => 
        array (
          0 => 'field',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setId' => 
      array (
        'rampage\\core\\data\\Object::setId:0' => 
        array (
          0 => 'id',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\data\\ObjectInterface' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
      'setId' => false,
    ),
    'parameters' => 
    array (
      'setId' => 
      array (
        'rampage\\core\\data\\ObjectInterface::setId:0' => 
        array (
          0 => 'id',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\data\\Collection' => 
  array (
    'supertypes' => 
    array (
      0 => 'IteratorAggregate',
      1 => 'Traversable',
      2 => 'Countable',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\log\\Logger' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\Log\\LoggerInterface',
      1 => 'Zend\\Log\\Logger',
      2 => 'Zend\\Log\\LoggerInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setWriterPluginManager' => false,
      'setWriters' => false,
      'setProcessorPluginManager' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\log\\Logger::__construct:0' => 
        array (
          0 => 'options',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setWriterPluginManager' => 
      array (
        'rampage\\core\\log\\Logger::setWriterPluginManager:0' => 
        array (
          0 => 'plugins',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setWriters' => 
      array (
        'rampage\\core\\log\\Logger::setWriters:0' => 
        array (
          0 => 'writers',
          1 => 'Zend\\Stdlib\\SplPriorityQueue',
          2 => true,
          3 => NULL,
        ),
      ),
      'setProcessorPluginManager' => 
      array (
        'rampage\\core\\log\\Logger::setProcessorPluginManager:0' => 
        array (
          0 => 'plugins',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\resource\\BootstrapListener' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\resource\\FileLocatorInterface' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\resource\\FileLocator' => 
  array (
    'supertypes' => 
    array (
      0 => 'rampage\\core\\resource\\FileLocatorInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\resource\\Theme' => 
  array (
    'supertypes' => 
    array (
      0 => 'rampage\\core\\resource\\FileLocatorInterface',
      1 => 'rampage\\core\\resource\\FileLocator',
      2 => 'rampage\\core\\resource\\FileLocatorInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setFallback' => false,
      'setCurrentTheme' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\resource\\Theme::__construct:0' => 
        array (
          0 => 'fallback',
          1 => 'rampage.resource.FileLocator',
          2 => true,
          3 => NULL,
        ),
      ),
      'setFallback' => 
      array (
        'rampage\\core\\resource\\Theme::setFallback:0' => 
        array (
          0 => 'fallback',
          1 => 'rampage\\core\\resource\\FileLocatorInterface',
          2 => false,
          3 => NULL,
        ),
      ),
      'setCurrentTheme' => 
      array (
        'rampage\\core\\resource\\Theme::setCurrentTheme:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\ViewLocator' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
      1 => 'rampage\\core\\ServiceManager',
      2 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
      3 => 'Zend\\ServiceManager\\ServiceManager',
      4 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setShared' => false,
      'setAlias' => false,
      'setAllowOverride' => false,
      'setShareByDefault' => false,
      'setThrowExceptionInCreate' => false,
      'setRetrieveFromPeeringManagerFirst' => false,
      'setInvokableClass' => false,
      'setFactory' => false,
      'setService' => false,
      'setCanonicalNames' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\view\\ViewLocator::__construct:0' => 
        array (
          0 => 'parent',
          1 => 'rampage\\core\\ObjectManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setShared' => 
      array (
        'rampage\\core\\view\\ViewLocator::setShared:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\ViewLocator::setShared:1' => 
        array (
          0 => 'isShared',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setAlias' => 
      array (
        'rampage\\core\\view\\ViewLocator::setAlias:0' => 
        array (
          0 => 'alias',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\ViewLocator::setAlias:1' => 
        array (
          0 => 'class',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setAllowOverride' => 
      array (
        'rampage\\core\\view\\ViewLocator::setAllowOverride:0' => 
        array (
          0 => 'allowOverride',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setShareByDefault' => 
      array (
        'rampage\\core\\view\\ViewLocator::setShareByDefault:0' => 
        array (
          0 => 'shareByDefault',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setThrowExceptionInCreate' => 
      array (
        'rampage\\core\\view\\ViewLocator::setThrowExceptionInCreate:0' => 
        array (
          0 => 'throwExceptionInCreate',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setRetrieveFromPeeringManagerFirst' => 
      array (
        'rampage\\core\\view\\ViewLocator::setRetrieveFromPeeringManagerFirst:0' => 
        array (
          0 => 'retrieveFromPeeringManagerFirst',
          1 => NULL,
          2 => false,
          3 => true,
        ),
      ),
      'setInvokableClass' => 
      array (
        'rampage\\core\\view\\ViewLocator::setInvokableClass:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\ViewLocator::setInvokableClass:1' => 
        array (
          0 => 'invokableClass',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\ViewLocator::setInvokableClass:2' => 
        array (
          0 => 'shared',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setFactory' => 
      array (
        'rampage\\core\\view\\ViewLocator::setFactory:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\ViewLocator::setFactory:1' => 
        array (
          0 => 'factory',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\ViewLocator::setFactory:2' => 
        array (
          0 => 'shared',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setService' => 
      array (
        'rampage\\core\\view\\ViewLocator::setService:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\ViewLocator::setService:1' => 
        array (
          0 => 'service',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\ViewLocator::setService:2' => 
        array (
          0 => 'shared',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setCanonicalNames' => 
      array (
        'rampage\\core\\view\\ViewLocator::setCanonicalNames:0' => 
        array (
          0 => 'canonicalNames',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\View' => 
  array (
    'supertypes' => 
    array (
      0 => 'rampage\\core\\data\\ObjectInterface',
      1 => 'rampage\\core\\view\\LayoutViewInterface',
      2 => 'Serializable',
      3 => 'rampage\\core\\view\\RenderableInterface',
      4 => 'rampage\\core\\view\\TemplateInterface',
      5 => 'rampage\\core\\data\\Object',
      6 => 'rampage\\core\\data\\ObjectInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setTemplate' => false,
      'setLayout' => false,
      'setNameInLayout' => false,
      'setViewRenderer' => false,
      'setIdFieldName' => false,
      'setId' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\view\\View::__construct:0' => 
        array (
          0 => 'data',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setTemplate' => 
      array (
        'rampage\\core\\view\\View::setTemplate:0' => 
        array (
          0 => 'template',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setLayout' => 
      array (
        'rampage\\core\\view\\View::setLayout:0' => 
        array (
          0 => 'layout',
          1 => 'rampage\\core\\view\\Layout',
          2 => true,
          3 => NULL,
        ),
      ),
      'setNameInLayout' => 
      array (
        'rampage\\core\\view\\View::setNameInLayout:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setViewRenderer' => 
      array (
        'rampage\\core\\view\\View::setViewRenderer:0' => 
        array (
          0 => 'renderer',
          1 => 'Zend\\View\\Renderer\\RendererInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setIdFieldName' => 
      array (
        'rampage\\core\\view\\View::setIdFieldName:0' => 
        array (
          0 => 'field',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setId' => 
      array (
        'rampage\\core\\view\\View::setId:0' => 
        array (
          0 => 'id',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\features\\CachableViewInterface' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\view\\RenderableInterface' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
      'setViewRenderer' => false,
    ),
    'parameters' => 
    array (
      'setViewRenderer' => 
      array (
        'rampage\\core\\view\\RenderableInterface::setViewRenderer:0' => 
        array (
          0 => 'renderer',
          1 => 'Zend\\View\\Renderer\\RendererInterface',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\cache\\HtmlCache' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\Cache\\Pattern\\PatternInterface',
      1 => 'Zend\\Cache\\Pattern\\AbstractPattern',
      2 => 'Zend\\Cache\\Pattern\\PatternInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      'setOptions' => false,
    ),
    'parameters' => 
    array (
      'setOptions' => 
      array (
        'rampage\\core\\view\\cache\\HtmlCache::setOptions:0' => 
        array (
          0 => 'options',
          1 => 'Zend\\Cache\\Pattern\\PatternOptions',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\LayoutViewInterface' => 
  array (
    'supertypes' => 
    array (
      0 => 'rampage\\core\\view\\RenderableInterface',
      1 => 'Serializable',
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
      'setLayout' => false,
      'setNameInLayout' => false,
      'setViewRenderer' => false,
    ),
    'parameters' => 
    array (
      'setLayout' => 
      array (
        'rampage\\core\\view\\LayoutViewInterface::setLayout:0' => 
        array (
          0 => 'layout',
          1 => 'rampage\\core\\view\\Layout',
          2 => true,
          3 => NULL,
        ),
      ),
      'setNameInLayout' => 
      array (
        'rampage\\core\\view\\LayoutViewInterface::setNameInLayout:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setViewRenderer' => 
      array (
        'rampage\\core\\view\\LayoutViewInterface::setViewRenderer:0' => 
        array (
          0 => 'renderer',
          1 => 'Zend\\View\\Renderer\\RendererInterface',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\http\\DefaultRenderStrategy' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\EventManager\\ListenerAggregateInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\view\\http\\ViewInitializer' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\EventManager\\ListenerAggregateInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setObjectManager' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\view\\http\\ViewInitializer::__construct:0' => 
        array (
          0 => 'objectManager',
          1 => 'rampage\\core\\ObjectManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setObjectManager' => 
      array (
        'rampage\\core\\view\\http\\ViewInitializer::setObjectManager:0' => 
        array (
          0 => 'objectManager',
          1 => 'rampage\\core\\ObjectManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\http\\LayoutConfigListener' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\view\\LayoutConfig' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\EventManager\\EventsCapableInterface',
      1 => 'Zend\\EventManager\\EventManagerAwareInterface',
      2 => 'rampage\\core\\xml\\Config',
      3 => 'Zend\\EventManager\\EventManagerAwareInterface',
      4 => 'Zend\\EventManager\\EventsCapableInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setEventManager' => true,
      'setXml' => false,
      'setMergeRules' => false,
      'getEventManager' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\view\\LayoutConfig::__construct:0' => 
        array (
          0 => 'resourceLocator',
          1 => 'rampage\\core\\resource\\FileLocatorInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setEventManager' => 
      array (
        'rampage\\core\\view\\LayoutConfig::setEventManager:0' => 
        array (
          0 => 'eventManager',
          1 => 'Zend\\EventManager\\EventManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setXml' => 
      array (
        'rampage\\core\\view\\LayoutConfig::setXml:0' => 
        array (
          0 => 'xml',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setMergeRules' => 
      array (
        'rampage\\core\\view\\LayoutConfig::setMergeRules:0' => 
        array (
          0 => 'rules',
          1 => 'rampage\\core\\xml\\mergerule\\ChainedRule',
          2 => false,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\TemplateInterface' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
      'setTemplate' => false,
    ),
    'parameters' => 
    array (
      'setTemplate' => 
      array (
        'rampage\\core\\view\\TemplateInterface::setTemplate:0' => 
        array (
          0 => 'template',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\Layout' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\EventManager\\EventManagerAwareInterface',
      1 => 'Zend\\EventManager\\EventsCapableInterface',
      2 => 'Serializable',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setResponse' => false,
      'setRequest' => false,
      'setViewLocator' => false,
      'setEventManager' => true,
      'setUpdate' => false,
      'setView' => false,
      'getEventManager' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\view\\Layout::__construct:0' => 
        array (
          0 => 'update',
          1 => 'rampage\\core\\view\\LayoutUpdate',
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\Layout::__construct:1' => 
        array (
          0 => 'locator',
          1 => 'rampage\\core\\view\\ViewLocator',
          2 => true,
          3 => NULL,
        ),
      ),
      'setResponse' => 
      array (
        'rampage\\core\\view\\Layout::setResponse:0' => 
        array (
          0 => 'response',
          1 => 'Zend\\Stdlib\\ResponseInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setRequest' => 
      array (
        'rampage\\core\\view\\Layout::setRequest:0' => 
        array (
          0 => 'request',
          1 => 'Zend\\Stdlib\\RequestInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setViewLocator' => 
      array (
        'rampage\\core\\view\\Layout::setViewLocator:0' => 
        array (
          0 => 'locator',
          1 => 'rampage\\core\\view\\ViewLocator',
          2 => true,
          3 => NULL,
        ),
      ),
      'setEventManager' => 
      array (
        'rampage\\core\\view\\Layout::setEventManager:0' => 
        array (
          0 => 'eventManager',
          1 => 'Zend\\EventManager\\EventManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setUpdate' => 
      array (
        'rampage\\core\\view\\Layout::setUpdate:0' => 
        array (
          0 => 'update',
          1 => 'rampage\\core\\view\\LayoutUpdate',
          2 => true,
          3 => NULL,
        ),
      ),
      'setView' => 
      array (
        'rampage\\core\\view\\Layout::setView:0' => 
        array (
          0 => 'view',
          1 => 'rampage\\core\\view\\LayoutViewInterface',
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\Layout::setView:1' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\LayoutUpdate' => 
  array (
    'supertypes' => 
    array (
      0 => 'IteratorAggregate',
      1 => 'Traversable',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setConfig' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\view\\LayoutUpdate::__construct:0' => 
        array (
          0 => 'config',
          1 => 'rampage\\core\\view\\LayoutConfig',
          2 => true,
          3 => NULL,
        ),
      ),
      'setConfig' => 
      array (
        'rampage\\core\\view\\LayoutUpdate::setConfig:0' => 
        array (
          0 => 'config',
          1 => 'rampage\\core\\view\\LayoutConfig',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\LayoutAwareInterface' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
      'setLayout' => false,
    ),
    'parameters' => 
    array (
      'setLayout' => 
      array (
        'rampage\\core\\view\\LayoutAwareInterface::setLayout:0' => 
        array (
          0 => 'layout',
          1 => 'rampage\\core\\view\\Layout',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\renderer\\PhpRenderer' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\View\\Renderer\\RendererInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setHelperPluginManager' => false,
      'setTemplateResolver' => false,
      'setResolver' => false,
      'setData' => false,
    ),
    'parameters' => 
    array (
      'setHelperPluginManager' => 
      array (
        'rampage\\core\\view\\renderer\\PhpRenderer::setHelperPluginManager:0' => 
        array (
          0 => 'helpers',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setTemplateResolver' => 
      array (
        'rampage\\core\\view\\renderer\\PhpRenderer::setTemplateResolver:0' => 
        array (
          0 => 'locator',
          1 => 'rampage\\core\\resource\\FileLocatorInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setResolver' => 
      array (
        'rampage\\core\\view\\renderer\\PhpRenderer::setResolver:0' => 
        array (
          0 => 'resolver',
          1 => 'Zend\\View\\Resolver\\ResolverInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setData' => 
      array (
        'rampage\\core\\view\\renderer\\PhpRenderer::setData:0' => 
        array (
          0 => 'values',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\view\\renderer\\PhtmlTemplate' => 
  array (
    'supertypes' => 
    array (
      0 => 'rampage\\core\\data\\ObjectInterface',
      1 => 'rampage\\core\\data\\Object',
      2 => 'rampage\\core\\data\\ObjectInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setIdFieldName' => false,
      'setId' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\view\\renderer\\PhtmlTemplate::__construct:0' => 
        array (
          0 => 'data',
          1 => 'ArrayObject',
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\renderer\\PhtmlTemplate::__construct:1' => 
        array (
          0 => 'renderer',
          1 => 'rampage\\core\\view\\renderer\\PhpRenderer',
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\renderer\\PhtmlTemplate::__construct:2' => 
        array (
          0 => 'view',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\view\\renderer\\PhtmlTemplate::__construct:3' => 
        array (
          0 => 'template',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setIdFieldName' => 
      array (
        'rampage\\core\\view\\renderer\\PhtmlTemplate::setIdFieldName:0' => 
        array (
          0 => 'field',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setId' => 
      array (
        'rampage\\core\\view\\renderer\\PhtmlTemplate::setId:0' => 
        array (
          0 => 'id',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\PathManager' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\PathManager::__construct:0' => 
        array (
          0 => 'config',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\controller\\AbstractLayoutController' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\Stdlib\\DispatchableInterface',
      1 => 'Zend\\EventManager\\EventManagerAwareInterface',
      2 => 'Zend\\EventManager\\EventsCapableInterface',
      3 => 'Zend\\Mvc\\InjectApplicationEventInterface',
      4 => 'Zend\\ServiceManager\\ServiceLocatorAwareInterface',
      5 => 'rampage\\core\\view\\LayoutAwareInterface',
      6 => 'Zend\\Mvc\\Controller\\AbstractActionController',
      7 => 'Zend\\ServiceManager\\ServiceLocatorAwareInterface',
      8 => 'Zend\\Mvc\\InjectApplicationEventInterface',
      9 => 'Zend\\EventManager\\EventsCapableInterface',
      10 => 'Zend\\EventManager\\EventManagerAwareInterface',
      11 => 'Zend\\Stdlib\\DispatchableInterface',
      12 => 'Zend\\Mvc\\Controller\\AbstractController',
      13 => 'Zend\\Stdlib\\DispatchableInterface',
      14 => 'Zend\\EventManager\\EventManagerAwareInterface',
      15 => 'Zend\\EventManager\\EventsCapableInterface',
      16 => 'Zend\\Mvc\\InjectApplicationEventInterface',
      17 => 'Zend\\ServiceManager\\ServiceLocatorAwareInterface',
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
      'setLayout' => true,
      'setEventManager' => true,
      'setEvent' => false,
      'setServiceLocator' => true,
      'setPluginManager' => false,
      'getEventManager' => true,
      'getServiceLocator' => true,
    ),
    'parameters' => 
    array (
      'setLayout' => 
      array (
        'rampage\\core\\controller\\AbstractLayoutController::setLayout:0' => 
        array (
          0 => 'layout',
          1 => 'rampage\\core\\view\\Layout',
          2 => true,
          3 => NULL,
        ),
      ),
      'setEventManager' => 
      array (
        'rampage\\core\\controller\\AbstractLayoutController::setEventManager:0' => 
        array (
          0 => 'eventManager',
          1 => 'Zend\\EventManager\\EventManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setEvent' => 
      array (
        'rampage\\core\\controller\\AbstractLayoutController::setEvent:0' => 
        array (
          0 => 'e',
          1 => 'Zend\\EventManager\\EventInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setServiceLocator' => 
      array (
        'rampage\\core\\controller\\AbstractLayoutController::setServiceLocator:0' => 
        array (
          0 => 'serviceLocator',
          1 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setPluginManager' => 
      array (
        'rampage\\core\\controller\\AbstractLayoutController::setPluginManager:0' => 
        array (
          0 => 'plugins',
          1 => 'Zend\\Mvc\\Controller\\PluginManager',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\controller\\LayoutOnlyController' => 
  array (
    'supertypes' => 
    array (
      0 => 'rampage\\core\\view\\LayoutAwareInterface',
      1 => 'Zend\\ServiceManager\\ServiceLocatorAwareInterface',
      2 => 'Zend\\Mvc\\InjectApplicationEventInterface',
      3 => 'Zend\\EventManager\\EventsCapableInterface',
      4 => 'Zend\\EventManager\\EventManagerAwareInterface',
      5 => 'Zend\\Stdlib\\DispatchableInterface',
      6 => 'rampage\\core\\controller\\AbstractLayoutController',
      7 => 'Zend\\Stdlib\\DispatchableInterface',
      8 => 'Zend\\EventManager\\EventManagerAwareInterface',
      9 => 'Zend\\EventManager\\EventsCapableInterface',
      10 => 'Zend\\Mvc\\InjectApplicationEventInterface',
      11 => 'Zend\\ServiceManager\\ServiceLocatorAwareInterface',
      12 => 'rampage\\core\\view\\LayoutAwareInterface',
      13 => 'Zend\\Mvc\\Controller\\AbstractActionController',
      14 => 'Zend\\ServiceManager\\ServiceLocatorAwareInterface',
      15 => 'Zend\\Mvc\\InjectApplicationEventInterface',
      16 => 'Zend\\EventManager\\EventsCapableInterface',
      17 => 'Zend\\EventManager\\EventManagerAwareInterface',
      18 => 'Zend\\Stdlib\\DispatchableInterface',
      19 => 'Zend\\Mvc\\Controller\\AbstractController',
      20 => 'Zend\\Stdlib\\DispatchableInterface',
      21 => 'Zend\\EventManager\\EventManagerAwareInterface',
      22 => 'Zend\\EventManager\\EventsCapableInterface',
      23 => 'Zend\\Mvc\\InjectApplicationEventInterface',
      24 => 'Zend\\ServiceManager\\ServiceLocatorAwareInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      'setLayout' => true,
      'setEventManager' => true,
      'setEvent' => false,
      'setServiceLocator' => true,
      'setPluginManager' => false,
      'getServiceLocator' => true,
      'getEventManager' => true,
    ),
    'parameters' => 
    array (
      'setLayout' => 
      array (
        'rampage\\core\\controller\\LayoutOnlyController::setLayout:0' => 
        array (
          0 => 'layout',
          1 => 'rampage\\core\\view\\Layout',
          2 => true,
          3 => NULL,
        ),
      ),
      'setEventManager' => 
      array (
        'rampage\\core\\controller\\LayoutOnlyController::setEventManager:0' => 
        array (
          0 => 'eventManager',
          1 => 'Zend\\EventManager\\EventManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setEvent' => 
      array (
        'rampage\\core\\controller\\LayoutOnlyController::setEvent:0' => 
        array (
          0 => 'e',
          1 => 'Zend\\EventManager\\EventInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setServiceLocator' => 
      array (
        'rampage\\core\\controller\\LayoutOnlyController::setServiceLocator:0' => 
        array (
          0 => 'serviceLocator',
          1 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setPluginManager' => 
      array (
        'rampage\\core\\controller\\LayoutOnlyController::setPluginManager:0' => 
        array (
          0 => 'plugins',
          1 => 'Zend\\Mvc\\Controller\\PluginManager',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\router\\http\\LayoutRoute' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\Mvc\\Router\\Http\\RouteInterface',
      1 => 'Zend\\Mvc\\Router\\RouteInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\router\\http\\LayoutRoute::__construct:0' => 
        array (
          0 => 'route',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\router\\http\\LayoutRoute::__construct:1' => 
        array (
          0 => 'layout',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\router\\http\\LayoutRoute::__construct:2' => 
        array (
          0 => 'handles',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\router\\http\\StandardRoute' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\Mvc\\Router\\Http\\RouteInterface',
      1 => 'Zend\\Mvc\\Router\\RouteInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\router\\http\\StandardRoute::__construct:0' => 
        array (
          0 => 'frontname',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\router\\http\\StandardRoute::__construct:1' => 
        array (
          0 => 'namespace',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\router\\http\\StandardRoute::__construct:2' => 
        array (
          0 => 'defaults',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
        'rampage\\core\\router\\http\\StandardRoute::__construct:3' => 
        array (
          0 => 'allowedParams',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\Utils' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\Config' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\EventManager\\EventManagerAwareInterface',
      1 => 'Zend\\EventManager\\EventsCapableInterface',
      2 => 'rampage\\core\\modules\\AggregatedXmlConfig',
      3 => 'Zend\\EventManager\\EventsCapableInterface',
      4 => 'Zend\\EventManager\\EventManagerAwareInterface',
      5 => 'rampage\\core\\xml\\Config',
      6 => 'Zend\\EventManager\\EventManagerAwareInterface',
      7 => 'Zend\\EventManager\\EventsCapableInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setModuleRegistry' => false,
      'setPathManager' => false,
      'setEventManager' => true,
      'setXml' => false,
      'setMergeRules' => false,
      'getEventManager' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\Config::__construct:0' => 
        array (
          0 => 'registry',
          1 => 'rampage\\core\\ModuleRegistry',
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\Config::__construct:1' => 
        array (
          0 => 'pathManager',
          1 => 'rampage\\core\\PathManager',
          2 => true,
          3 => NULL,
        ),
      ),
      'setModuleRegistry' => 
      array (
        'rampage\\core\\Config::setModuleRegistry:0' => 
        array (
          0 => 'registry',
          1 => 'rampage\\core\\ModuleRegistry',
          2 => true,
          3 => NULL,
        ),
      ),
      'setPathManager' => 
      array (
        'rampage\\core\\Config::setPathManager:0' => 
        array (
          0 => 'paths',
          1 => 'rampage\\core\\PathManager',
          2 => true,
          3 => NULL,
        ),
      ),
      'setEventManager' => 
      array (
        'rampage\\core\\Config::setEventManager:0' => 
        array (
          0 => 'eventManager',
          1 => 'Zend\\EventManager\\EventManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setXml' => 
      array (
        'rampage\\core\\Config::setXml:0' => 
        array (
          0 => 'xml',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setMergeRules' => 
      array (
        'rampage\\core\\Config::setMergeRules:0' => 
        array (
          0 => 'rules',
          1 => 'rampage\\core\\xml\\mergerule\\ChainedRule',
          2 => false,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\Module' => 
  array (
    'supertypes' => 
    array (
      0 => 'rampage\\core\\modules\\ModuleInterface',
      1 => 'Zend\\ModuleManager\\Feature\\ConfigProviderInterface',
      2 => 'Zend\\ModuleManager\\Feature\\AutoloaderProviderInterface',
      3 => 'Zend\\ModuleManager\\Feature\\ConsoleBannerProviderInterface',
      4 => 'Zend\\ModuleManager\\Feature\\ConsoleUsageProviderInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setRegistry' => false,
      'setPathManager' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\Module::__construct:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\Module::__construct:1' => 
        array (
          0 => 'options',
          1 => NULL,
          2 => false,
          3 => 
          array (
          ),
        ),
      ),
      'setRegistry' => 
      array (
        'rampage\\core\\Module::setRegistry:0' => 
        array (
          0 => 'registry',
          1 => 'rampage\\core\\ModuleRegistry',
          2 => true,
          3 => NULL,
        ),
      ),
      'setPathManager' => 
      array (
        'rampage\\core\\Module::setPathManager:0' => 
        array (
          0 => 'path',
          1 => 'rampage\\core\\PathManager',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\ModuleRegistry' => 
  array (
    'supertypes' => 
    array (
      0 => 'IteratorAggregate',
      1 => 'Traversable',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setPathManager' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\ModuleRegistry::__construct:0' => 
        array (
          0 => 'pathManager',
          1 => 'rampage\\core\\PathManager',
          2 => false,
          3 => NULL,
        ),
      ),
      'setPathManager' => 
      array (
        'rampage\\core\\ModuleRegistry::setPathManager:0' => 
        array (
          0 => 'manager',
          1 => 'rampage\\core\\PathManager',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\pathmanager\\DefaultFallback' => 
  array (
    'supertypes' => 
    array (
      0 => 'Serializable',
      1 => 'Iterator',
      2 => 'Traversable',
      3 => 'Countable',
      4 => 'rampage\\core\\pathmanager\\FallbackInterface',
      5 => 'Zend\\Stdlib\\SplPriorityQueue',
      6 => 'Countable',
      7 => 'Traversable',
      8 => 'Iterator',
      9 => 'Serializable',
      10 => 'SplPriorityQueue',
      11 => 'Iterator',
      12 => 'Traversable',
      13 => 'Countable',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      'setExtractFlags' => false,
    ),
    'parameters' => 
    array (
      'setExtractFlags' => 
      array (
        'rampage\\core\\pathmanager\\DefaultFallback::setExtractFlags:0' => 
        array (
          0 => 'flags',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\pathmanager\\FallbackInterface' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\service\\AggregatedServicesFactory' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\ServiceManager\\FactoryInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\service\\ApplicationFactory' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\ServiceManager\\FactoryInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\service\\DiFactory' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\ServiceManager\\FactoryInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\service\\DiAbstractServiceFactory' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\ServiceManager\\AbstractFactoryInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\service\\ModuleRegistryFactory' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\service\\ObjectManagerFactory' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\ServiceManager\\FactoryInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\service\\AggregatedServiceLocator' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
      1 => 'rampage\\core\\ServiceManager',
      2 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
      3 => 'Zend\\ServiceManager\\ServiceManager',
      4 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setShared' => false,
      'setAlias' => false,
      'setAllowOverride' => false,
      'setShareByDefault' => false,
      'setThrowExceptionInCreate' => false,
      'setRetrieveFromPeeringManagerFirst' => false,
      'setInvokableClass' => false,
      'setFactory' => false,
      'setService' => false,
      'setCanonicalNames' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\service\\AggregatedServiceLocator::__construct:0' => 
        array (
          0 => 'parent',
          1 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setShared' => 
      array (
        'rampage\\core\\service\\AggregatedServiceLocator::setShared:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\service\\AggregatedServiceLocator::setShared:1' => 
        array (
          0 => 'isShared',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setAlias' => 
      array (
        'rampage\\core\\service\\AggregatedServiceLocator::setAlias:0' => 
        array (
          0 => 'alias',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\service\\AggregatedServiceLocator::setAlias:1' => 
        array (
          0 => 'class',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setAllowOverride' => 
      array (
        'rampage\\core\\service\\AggregatedServiceLocator::setAllowOverride:0' => 
        array (
          0 => 'allowOverride',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setShareByDefault' => 
      array (
        'rampage\\core\\service\\AggregatedServiceLocator::setShareByDefault:0' => 
        array (
          0 => 'shareByDefault',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setThrowExceptionInCreate' => 
      array (
        'rampage\\core\\service\\AggregatedServiceLocator::setThrowExceptionInCreate:0' => 
        array (
          0 => 'throwExceptionInCreate',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setRetrieveFromPeeringManagerFirst' => 
      array (
        'rampage\\core\\service\\AggregatedServiceLocator::setRetrieveFromPeeringManagerFirst:0' => 
        array (
          0 => 'retrieveFromPeeringManagerFirst',
          1 => NULL,
          2 => false,
          3 => true,
        ),
      ),
      'setInvokableClass' => 
      array (
        'rampage\\core\\service\\AggregatedServiceLocator::setInvokableClass:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\service\\AggregatedServiceLocator::setInvokableClass:1' => 
        array (
          0 => 'invokableClass',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\service\\AggregatedServiceLocator::setInvokableClass:2' => 
        array (
          0 => 'shared',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setFactory' => 
      array (
        'rampage\\core\\service\\AggregatedServiceLocator::setFactory:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\service\\AggregatedServiceLocator::setFactory:1' => 
        array (
          0 => 'factory',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\service\\AggregatedServiceLocator::setFactory:2' => 
        array (
          0 => 'shared',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setService' => 
      array (
        'rampage\\core\\service\\AggregatedServiceLocator::setService:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\service\\AggregatedServiceLocator::setService:1' => 
        array (
          0 => 'service',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\service\\AggregatedServiceLocator::setService:2' => 
        array (
          0 => 'shared',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setCanonicalNames' => 
      array (
        'rampage\\core\\service\\AggregatedServiceLocator::setCanonicalNames:0' => 
        array (
          0 => 'canonicalNames',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\service\\ViewInitializerFactory' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\ServiceManager' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
      1 => 'Zend\\ServiceManager\\ServiceManager',
      2 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setShared' => false,
      'setAlias' => false,
      'setAllowOverride' => false,
      'setShareByDefault' => false,
      'setThrowExceptionInCreate' => false,
      'setRetrieveFromPeeringManagerFirst' => false,
      'setInvokableClass' => false,
      'setFactory' => false,
      'setService' => false,
      'setCanonicalNames' => false,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\ServiceManager::__construct:0' => 
        array (
          0 => 'config',
          1 => 'Zend\\ServiceManager\\ConfigInterface',
          2 => false,
          3 => NULL,
        ),
      ),
      'setShared' => 
      array (
        'rampage\\core\\ServiceManager::setShared:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\ServiceManager::setShared:1' => 
        array (
          0 => 'isShared',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setAlias' => 
      array (
        'rampage\\core\\ServiceManager::setAlias:0' => 
        array (
          0 => 'alias',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\ServiceManager::setAlias:1' => 
        array (
          0 => 'class',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setAllowOverride' => 
      array (
        'rampage\\core\\ServiceManager::setAllowOverride:0' => 
        array (
          0 => 'allowOverride',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setShareByDefault' => 
      array (
        'rampage\\core\\ServiceManager::setShareByDefault:0' => 
        array (
          0 => 'shareByDefault',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setThrowExceptionInCreate' => 
      array (
        'rampage\\core\\ServiceManager::setThrowExceptionInCreate:0' => 
        array (
          0 => 'throwExceptionInCreate',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setRetrieveFromPeeringManagerFirst' => 
      array (
        'rampage\\core\\ServiceManager::setRetrieveFromPeeringManagerFirst:0' => 
        array (
          0 => 'retrieveFromPeeringManagerFirst',
          1 => NULL,
          2 => false,
          3 => true,
        ),
      ),
      'setInvokableClass' => 
      array (
        'rampage\\core\\ServiceManager::setInvokableClass:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\ServiceManager::setInvokableClass:1' => 
        array (
          0 => 'invokableClass',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\ServiceManager::setInvokableClass:2' => 
        array (
          0 => 'shared',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setFactory' => 
      array (
        'rampage\\core\\ServiceManager::setFactory:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\ServiceManager::setFactory:1' => 
        array (
          0 => 'factory',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\ServiceManager::setFactory:2' => 
        array (
          0 => 'shared',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setService' => 
      array (
        'rampage\\core\\ServiceManager::setService:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\ServiceManager::setService:1' => 
        array (
          0 => 'service',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\ServiceManager::setService:2' => 
        array (
          0 => 'shared',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
      'setCanonicalNames' => 
      array (
        'rampage\\core\\ServiceManager::setCanonicalNames:0' => 
        array (
          0 => 'canonicalNames',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\Autoload' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\ObjectManagerInterface' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\ServiceManager\\ServiceLocatorInterface',
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\ServiceConfig' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\ServiceManager\\ConfigInterface',
      1 => 'Zend\\Mvc\\Service\\ServiceManagerConfig',
      2 => 'Zend\\ServiceManager\\ConfigInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\ServiceConfig::__construct:0' => 
        array (
          0 => 'config',
          1 => NULL,
          2 => false,
          3 => 
          array (
          ),
        ),
      ),
    ),
  ),
  'rampage\\core\\Application' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\EventManager\\EventManagerAwareInterface',
      1 => 'Zend\\EventManager\\EventsCapableInterface',
      2 => 'Zend\\Mvc\\ApplicationInterface',
      3 => 'Zend\\Mvc\\Application',
      4 => 'Zend\\Mvc\\ApplicationInterface',
      5 => 'Zend\\EventManager\\EventsCapableInterface',
      6 => 'Zend\\EventManager\\EventManagerAwareInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setEventManager' => true,
      'getEventManager' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\Application::__construct:0' => 
        array (
          0 => 'configuration',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\Application::__construct:1' => 
        array (
          0 => 'serviceManager',
          1 => 'Zend\\ServiceManager\\ServiceManager',
          2 => true,
          3 => NULL,
        ),
      ),
      'setEventManager' => 
      array (
        'rampage\\core\\Application::setEventManager:0' => 
        array (
          0 => 'eventManager',
          1 => 'Zend\\EventManager\\EventManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\event\\SharedEventManager' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\EventManager\\SharedEventManagerInterface',
      1 => 'Zend\\EventManager\\SharedEventAggregateAwareInterface',
      2 => 'Zend\\EventManager\\SharedEventManager',
      3 => 'Zend\\EventManager\\SharedEventAggregateAwareInterface',
      4 => 'Zend\\EventManager\\SharedEventManagerInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      'attachAggregate' => true,
      'detachAggregate' => true,
    ),
    'parameters' => 
    array (
      'attachAggregate' => 
      array (
        'rampage\\core\\event\\SharedEventManager::attachAggregate:0' => 
        array (
          0 => 'aggregate',
          1 => 'Zend\\EventManager\\SharedListenerAggregateInterface',
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\event\\SharedEventManager::attachAggregate:1' => 
        array (
          0 => 'priority',
          1 => NULL,
          2 => false,
          3 => 1,
        ),
      ),
      'detachAggregate' => 
      array (
        'rampage\\core\\event\\SharedEventManager::detachAggregate:0' => 
        array (
          0 => 'aggregate',
          1 => 'Zend\\EventManager\\SharedListenerAggregateInterface',
          2 => true,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\modules\\ManifestConfig' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\EventManager\\EventsCapableInterface',
      1 => 'Zend\\EventManager\\EventManagerAwareInterface',
      2 => 'rampage\\core\\xml\\Config',
      3 => 'Zend\\EventManager\\EventManagerAwareInterface',
      4 => 'Zend\\EventManager\\EventsCapableInterface',
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
      'setEventManager' => true,
      'setXml' => false,
      'setMergeRules' => false,
      'getEventManager' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\modules\\ManifestConfig::__construct:0' => 
        array (
          0 => 'module',
          1 => 'rampage\\core\\modules\\ModuleInterface',
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\modules\\ManifestConfig::__construct:1' => 
        array (
          0 => 'file',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setEventManager' => 
      array (
        'rampage\\core\\modules\\ManifestConfig::setEventManager:0' => 
        array (
          0 => 'eventManager',
          1 => 'Zend\\EventManager\\EventManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setXml' => 
      array (
        'rampage\\core\\modules\\ManifestConfig::setXml:0' => 
        array (
          0 => 'xml',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setMergeRules' => 
      array (
        'rampage\\core\\modules\\ManifestConfig::setMergeRules:0' => 
        array (
          0 => 'rules',
          1 => 'rampage\\core\\xml\\mergerule\\ChainedRule',
          2 => false,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\modules\\ModuleInterface' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
    ),
    'parameters' => 
    array (
    ),
  ),
  'rampage\\core\\modules\\ModuleEntry' => 
  array (
    'supertypes' => 
    array (
    ),
    'instantiator' => '__construct',
    'methods' => 
    array (
      '__construct' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\modules\\ModuleEntry::__construct:0' => 
        array (
          0 => 'name',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\modules\\ModuleEntry::__construct:1' => 
        array (
          0 => 'options',
          1 => NULL,
          2 => false,
          3 => NULL,
        ),
      ),
    ),
  ),
  'rampage\\core\\modules\\AggregatedXmlConfig' => 
  array (
    'supertypes' => 
    array (
      0 => 'Zend\\EventManager\\EventsCapableInterface',
      1 => 'Zend\\EventManager\\EventManagerAwareInterface',
      2 => 'rampage\\core\\xml\\Config',
      3 => 'Zend\\EventManager\\EventManagerAwareInterface',
      4 => 'Zend\\EventManager\\EventsCapableInterface',
    ),
    'instantiator' => NULL,
    'methods' => 
    array (
      '__construct' => true,
      'setModuleRegistry' => false,
      'setPathManager' => false,
      'setEventManager' => true,
      'setXml' => false,
      'setMergeRules' => false,
      'getEventManager' => true,
    ),
    'parameters' => 
    array (
      '__construct' => 
      array (
        'rampage\\core\\modules\\AggregatedXmlConfig::__construct:0' => 
        array (
          0 => 'registry',
          1 => 'rampage\\core\\ModuleRegistry',
          2 => true,
          3 => NULL,
        ),
        'rampage\\core\\modules\\AggregatedXmlConfig::__construct:1' => 
        array (
          0 => 'pathManager',
          1 => 'rampage\\core\\PathManager',
          2 => true,
          3 => NULL,
        ),
      ),
      'setModuleRegistry' => 
      array (
        'rampage\\core\\modules\\AggregatedXmlConfig::setModuleRegistry:0' => 
        array (
          0 => 'registry',
          1 => 'rampage\\core\\ModuleRegistry',
          2 => true,
          3 => NULL,
        ),
      ),
      'setPathManager' => 
      array (
        'rampage\\core\\modules\\AggregatedXmlConfig::setPathManager:0' => 
        array (
          0 => 'paths',
          1 => 'rampage\\core\\PathManager',
          2 => true,
          3 => NULL,
        ),
      ),
      'setEventManager' => 
      array (
        'rampage\\core\\modules\\AggregatedXmlConfig::setEventManager:0' => 
        array (
          0 => 'eventManager',
          1 => 'Zend\\EventManager\\EventManagerInterface',
          2 => true,
          3 => NULL,
        ),
      ),
      'setXml' => 
      array (
        'rampage\\core\\modules\\AggregatedXmlConfig::setXml:0' => 
        array (
          0 => 'xml',
          1 => NULL,
          2 => true,
          3 => NULL,
        ),
      ),
      'setMergeRules' => 
      array (
        'rampage\\core\\modules\\AggregatedXmlConfig::setMergeRules:0' => 
        array (
          0 => 'rules',
          1 => 'rampage\\core\\xml\\mergerule\\ChainedRule',
          2 => false,
          3 => NULL,
        ),
      ),
    ),
  ),
);