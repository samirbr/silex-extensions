<?php

namespace Samir\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigurationServiceProvider implements ServiceProviderInterface
{
    private $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function register(Application $app)
    {
        return $this->readConfig();
    }

    public function boot(Application $app)
    {
    }

    public function getFileFormat()
    {
      return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    protected function processRawJson($json)
    {
        return $json;
    }

    protected function readConfig()
    {
        $format = $this->getFileFormat();

        if (!$this->filename || !$format) {
            throw new \RuntimeException('A valid configuration file must be passed before reading the config.');
        }

        if (!file_exists($this->filename)) {
            throw new \InvalidArgumentException(
                sprintf("The config file '%s' does not exist.", $this->filename));
        }

        if ('php' === $format) {
            $config = require $this->filename;
            $config = (1 === $config) ? array() : $config;
            return $config ?: array();
        }

        if ('yml' === $format) {
            if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
                throw new \RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
            }
            $config = Yaml::parse($this->filename);
            return $config ?: array();
        }

        if ('json' === $format) {
            $config = $this->parseJson($this->filename);

            if (JSON_ERROR_NONE !== json_last_error()) {
                $jsonError = $this->getJsonError(json_last_error());
                throw new \RuntimeException(
                    sprintf('Invalid JSON provided "%s" in "%s"', $jsonError, $this->filename));
            }

            return $config ?: array();
        }

        throw new \InvalidArgumentException(
                sprintf("The config file '%s' appears has invalid format '%s'.", $this->filename, $format));
    }

    private function parseJson($filename)
    {
        $json = file_get_contents($filename);
        $json = $this->processRawJson($json);
        return json_decode($json, true);
    }

    private function getJsonError($code)
    {
        $errorMessages = array(
            JSON_ERROR_DEPTH            => 'The maximum stack depth has been exceeded',
            JSON_ERROR_STATE_MISMATCH   => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR        => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX           => 'Syntax error',
            JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        );

        return isset($errorMessages[$code]) ? $errorMessages[$code] : 'Unknown';
    }
}
