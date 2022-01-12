<?php

/**
 * Twig Component Handle Loader
 *
 * @copyright 2022 Dennis Morhardt <info@dennismorhardt.de>
 * @license MIT License; see LICENSE file for details.
 */

namespace Gglnx\TwigComponentHandleLoader\Loader;

use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * Add support for loading templates with '@component' handle syntax (like Fractal).
 *
 * @author Dennis Morhardt <info@dennismorhardt.de>
 * @package Gglnx\TwigComponentHandleLoader\Loader
 */
class ComponentHandleLoader implements LoaderInterface
{
    /**
     * A path where to look for components.
     *
     * @var string
     */
    protected $pathToComponents;

    /**
     * List of all components
     *
     * @var array
     */
    private $components = [];

    /**
     * Inits this loader
     *
     * @param string $pathToComponents
     * @return void
     */
    public function __construct(string $pathToComponents)
    {
        $this->pathToComponents = $pathToComponents;
        $this->components = $this->getComponents();
    }

    /**
     * @inheritdoc
     */
    public function getSourceContext(string $name): Source
    {
        $path = $this->findTemplate($name);

        return new Source(file_get_contents($path, true), $name, $path);
    }

    /**
     * @inheritdoc
     */
    public function getCacheKey(string $name): string
    {
        $path = $this->findTemplate($name);
        $len = strlen($this->pathToComponents);

        if (strncmp($this->pathToComponents, $path, $len) === 0) {
            return substr($path, $len);
        }

        return $path;
    }

    /**
     * @inheritdoc
     */
    public function isFresh(string $name, int $time): bool
    {
        $path = $this->findTemplate($name);

        return filemtime($path) < $time;
    }

    /**
     * @inheritdoc
     */
    public function exists(string $name)
    {
        return $this->findTemplate($name, false) !== null;
    }

    /**
     * Finds a template by at-name
     *
     * @param string $name The template logical name.
     * @param bool $throw Throw on errors.
     * @throws LoaderError When $name is not found or doesn't start with @.
     * @return string|null
     */
    protected function findTemplate($name, $throw = true)
    {
        try {
            // Throw error if name doesn't start with '@'
            if ($name[0] !== '@') {
                throw new LoaderError(sprintf(
                    'Template name "%s" does not support with @ as needed for component handle.',
                    $name
                ));
            }

            // Throw error if name includes a slash (we don't support namespaces)
            if (strpos($name, '/') !== false) {
                throw new LoaderError(sprintf(
                    'Template name "%s" includes an /, but namespaced are not supported.',
                    $name
                ));
            }

            // Try to find component
            if (!isset($this->components[$name])) {
                throw new LoaderError(sprintf(
                    'Unable to find component "%s" (looked into: %s).',
                    $name,
                    $this->pathToComponents
                ));
            }
        } catch (LoaderError $exception) {
            if (!$throw) {
                return null;
            }

            throw $exception;
        }

        // Get path to component
        return $this->components[$name];
    }

    /**
     * Loads all component paths
     *
     * @throws LoaderError When path to components doesn't exits.
     * @return array
     */
    private function getComponents()
    {
        // Check path to components
        if (!is_dir($this->pathToComponents)) {
            throw new LoaderError(sprintf(
                'The "%s" directory does not exist.',
                $this->pathToComponents
            ));
        }

        // Parse through all component folders
        $directory = new RecursiveDirectoryIterator($this->pathToComponents);

        // Build filter for 'twig' files
        $files = new RecursiveCallbackFilterIterator(
            $directory,
            function ($current, $key, $iterator) {
                if ($iterator->hasChildren()) {
                    return true;
                }

                if (!$current->isFile()) {
                    return false;
                }

                if (pathinfo($key, PATHINFO_EXTENSION) !== 'twig') {
                    return false;
                }

                return true;
            }
        );

        // Get all matches ending with 'twig'
        $matches = array_map(
            function ($match) {
                return sprintf('@%s', pathinfo($match->getFilename(), PATHINFO_FILENAME));
            },
            iterator_to_array(new RecursiveIteratorIterator($files))
        );

        // Return all components
        return array_flip($matches);
    }
}
