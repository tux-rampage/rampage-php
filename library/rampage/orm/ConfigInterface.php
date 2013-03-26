<?php

namespace rampage\orm;

use rampage\orm\hydrator\EntityHydrator;
/**
 * Interface for config
 */
interface ConfigInterface
{
    /**
     * Configure a repository
     *
     * @param object $repository
     */
    public function configureRepository(RepositoryInterface $repository);

    /**
     * Repository service/class name
     *
     * @param string $name
     * @return string
     */
    public function getRepositoryClass($name);

    /**
     * Has a repository config
     *
     * @param string $name
     * @return bool
     */
    public function hasRepositoryConfig($name);

    /**
     * Should return all defined repository names
     *
     * @return array
     */
    public function getRepositoryNames();
}