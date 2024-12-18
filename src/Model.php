<?php

namespace Tigress;

use Exception;
use Iterator;

/**
 * Class Model (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024, rudymas.be. (http://www.rudymas.be/)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2024.12.18.0
 * @package Tigress\Model
 */
class Model implements Iterator
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
     * Position of the iterator
     * @var int
     */
    private int $position = 0;

    /**
     * Get the version of the Model
     *
     * @return string
     */
    public static function version(): string
    {
        return '2024.12.18';
    }

    /**
     * Model constructor.
     *
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        if ($data !== null) {
            $this->update($data);
        }
    }

    /**
     * Update the model's properties
     *
     * @param object $data
     * @return void
     */
    public function update(object $data): void
    {
        foreach ($data as $property => $value) {
            if (array_key_exists($property, $this->properties)) {
            	$this->properties[$property] = $value;
            }
        }
    }

    /**
     * Update the model's properties from a POST request
     *
     * @param array $data
     * @return void
     */
    public function updateFromPost(array $data): void
    {
        foreach ($data as $property => $value) {
            if (array_key_exists($property, $this->properties)) {
            	$this->properties[$property] = $value;
            }
        }
    }

    /**
     * Check if the property is set
     *
     * @param string $property
     * @return bool
     */
    public function isset(string $property): bool
    {
        return array_key_exists($property, $this->properties);
    }

    /**
     * Initiate the model's properties/types
     *
     * @param array $data
     * @return void
     */
    public function initiateModel(array $data): void
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

            if (($value !== '' && $actualType !== 'NULL') && $expectedType !== $actualType) {
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
     * Check if the property is set
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($this->properties[$name]);
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
     * Get the data (properties) of the model
     *
     * @return array
     */
    public function read(): array
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

    /**
     * Return the current element
     *
     * @return mixed
     */
    public function current(): mixed
    {
        $keys = array_keys($this->properties);
        return $this->properties[$keys[$this->position]];
    }

    /**
     * Move forward to next element
     *
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element
     *
     * @return mixed
     */
    public function key(): mixed
    {
        $keys = array_keys($this->properties);
        return $keys[$this->position];
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        $keys = array_keys($this->properties);
        return isset($keys[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}