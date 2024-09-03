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
     * Array of all the properties of the model (data)
     * @var array
     */
    private array $properties = [];

    /**
     * Array of all the types of the properties of the model
     * @var array
     */
    private array $types = [];

    /**
     * Get the version of the Model
     *
     * @return string
     */
    public static function version(): string
    {
        return '1.0.0';
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
            $expectedType = $this->types[$property];
            $actualType = gettype($value);

            if ($expectedType === $actualType) {
                throw new Exception("Type mismatch for property $property. Expected $expectedType, got $actualType.");
            }

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
     * Get the properties
     *
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Get the types of the properties
     *
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
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