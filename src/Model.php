<?php

namespace Tigress;

use Exception;
use Iterator;

/**
 * Class Model (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024-2025, rudymas.be. (http://www.rudymas.be/)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.02.04.0
 * @package Tigress\Model
 */
class Model implements Iterator
{
    /**
     * Position of the iterator
     * @var int
     */
    private int $position = 0;

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
        return '2025.02.04';
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
                throw new Exception("Type mismatch for property $property ($value). Expected $expectedType, got $actualType.");
            }

            $this->properties[$property] = $value;
        } else {
            throw new Exception("Property $property does not exist.");
        }
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
     * Get the types of the properties
     *
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
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
     * Update the model's properties by an array
     *
     * @param array $data
     * @return void
     */
    public function updateByArray(array $data): void
    {
        foreach ($data as $property => $value) {
            if (array_key_exists($property, $this->properties)) {
            	$this->properties[$property] = $value;
            }
        }
    }

    /**
     * Update the model's properties by a POST request
     *
     * @param array $data
     * @return void
     */
    public function updateByPost(array $data): void
    {
        $this->updateByArray($data);
    }

    /**
     * Update the model's properties from a POST request
     *
     * @param array $data
     * @return void
     * @deprecated since 2025.01.23 - Use updateByPost instead - To be removed after version 2025.03.31
     */
    public function updateFromPost(array $data): void
    {
        $this->updateByArray($data);
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
     * Move forward to next element
     *
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
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
}