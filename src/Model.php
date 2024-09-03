<?php

namespace Tigress;

use Exception;

/**
 * Class Model (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024, rudymas.be. (http://www.rudymas.be/)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 1.0.0
 * @lastmodified 2024-09-02
 * @package Tigress\Model
 */
class Model
{
    /**
     * @var array
     */
    private array $properties = [];
    /**
     * @var array
     */
    private array $types = [];

    /**
     * Model constructor.
     */
    public function __construct()
    {
        define('TIGRESS_MODEL_VERSION', '1.0.0');
    }

    /**
     * Initiate the model's properties/types
     *
     * @param $data
     * @return void
     */
    public function initiateModel($data): void
    {
        foreach ($data as $property => $field) {
            $this->properties[$property] = $field['value'];
            $this->types[$property] = $field['type'];
        }
    }

    /**
     * Set the data for the model's properties
     *
     * @param string $property
     * @param mixed $value
     * @return void
     * @throws Exception
     */
    public function __set(string $property, mixed $value): void
    {
        if (array_key_exists($property, $this->properties)) {
            $this->properties[$property] = $value;
        } else {
            throw new Exception("Property $property does not exist.");
        }
    }

    /**
     * Get the data for the model's properties
     *
     * @param string $property
     * @return mixed
     * @throws Exception
     */
    public function __get(string $property): mixed
    {
        if (array_key_exists($property, $this->properties)) {
            return $this->properties[$property];
        }
        throw new Exception("Property $property does not exist.");
    }

    /**
     * Get the type of certain key
     *
     * @param string $property
     * @return string
     * @throws Exception
     */
    public function getType(string $property): string
    {
        if (array_key_exists($property, $this->properties)) {
            return $this->types[$property];
        }
        throw new Exception("Property $property does not exist.");
    }
}