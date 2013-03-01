<?php
/**
 * This is part of rampage.php
 * Copyright (c) 2012 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  library
 * @package   rampage.core
 * @author    Axel Helmert
 * @copyright Copyright (c) 2013 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\core\model\url;

use rampage\core\ObjectManagerInterface;
use rampage\core\model\Url;
use rampage\core\exception\RuntimeException;

/**
 * Url repository
 */
class Repository
{
    /**
     * Model instances
     *
     * @var string
     */
    private $models = array();

    /**
     * Model classes
     *
     * @var string
     */
    protected $services = array(
        'media' => 'rampage.url.media',
        'base' => 'rampage.url.base'
    );

    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Construct
     */
    public function __construct(ObjectManagerInterface $objectManager, $config = null)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Returns the object manager
     *
     * @return \rampage\core\ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Returns the default url class
     *
     * @return string
     */
    protected function getDefaultClass()
    {
        return 'rampage.core.model.Url';
    }

    /**
     * Returns the requested URL model
     *
     * @param string $type
     * @return rampage\core\model\Url
     */
    public function getUrlModel($type = null)
    {
        $type = ($type)? (string)$type : 'base';

        if (isset($this->models[$type])) {
            return $this->models[$type];
        }

        if (isset($this->services[$type])) {
            $service = $this->services[$type];
            $instance = $this->getObjectManager()->get($service);
        } else {
            $class = $this->getDefaultClass();
            $options = ($type == 'base')? array() : array('type' => $type);

            $instance = $this->getObjectManager()->newInstance($class, $options);
        }

        if (!$instance instanceof Url) {
            throw new RuntimeException(sprintf(
                'Invalid url model: "%s" (should be an instance of rampage\core\model\Url)',
                is_object($instance)? get_class($instance) : gettype($instance)
            ));
        }

        $this->models[$type] = $instance;
        return $instance;
    }
}