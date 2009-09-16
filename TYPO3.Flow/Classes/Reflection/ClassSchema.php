<?php
declare(ENCODING = 'utf-8');
namespace F3\FLOW3\Reflection;

/*                                                                        *
 * This script belongs to the FLOW3 framework.                            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A class schema
 *
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
class ClassSchema {

	/**
	 * Available model types
	 */
	const MODELTYPE_ENTITY = 1;
	const MODELTYPE_VALUEOBJECT = 2;

	/**
	 * Specifies the allowed property types.
	 */
	const ALLOWED_TYPES_PATTERN = '/^\\\\?(?P<type>integer|int|float|boolean|string|DateTime|F3\\\\[a-zA-Z0-9\\\\]+|array|ArrayObject|SplObjectStorage)(?:<(?P<elementType>[a-zA-Z0-9\\\\]+)>)?/';

	/**
	 * Name of the class this schema is referring to
	 *
	 * @var string
	 */
	protected $className;

	/**
	 * Model type of the class this schema is referring to
	 *
	 * @var integer
	 */
	protected $modelType = self::MODELTYPE_ENTITY;

	/**
	 * Whether a repository exists for the class this schema is referring to
	 * @var boolean
	 */
	protected $aggregateRoot = FALSE;

	/**
	 * The name of the property holding the uuid of an entity, if any.
	 *
	 * @var string
	 */
	protected $uuidPropertyName;

	/**
	 * Properties of the class which need to be persisted
	 *
	 * @var array
	 */
	protected $properties = array();

	/**
	 * The properties forming the identity of an object
	 *
	 * @var array
	 */
	protected $identityProperties = array();

	/**
	 * Constructs this class schema
	 *
	 * @param string $className Name of the class this schema is referring to
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function __construct($className) {
		$this->className = $className;
	}

	/**
	 * Returns the class name this schema is referring to
	 *
	 * @return string The class name
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * Adds (defines) a specific property and its type.
	 *
	 * @param string $name Name of the property
	 * @param string $type Type of the property (see ALLOWED_TYPES_PATTERN)
	 * @param boolean $lazy Whether the property should be lazy-loaded when reconstituting
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function addProperty($name, $type, $lazy = FALSE) {
		$matches = array();
		if (preg_match(self::ALLOWED_TYPES_PATTERN, $type, $matches)) {
			$type = ($matches['type'] === 'int') ? 'integer' : $matches['type'];
			$elementType = isset($matches['elementType']) ? $matches['elementType'] : NULL;

			if ($elementType !== NULL && !in_array($type, array('array', 'ArrayObject', 'SplObjectStorage'))) {
				throw new \F3\FLOW3\Reflection\Exception\InvalidPropertyType('Property  of type "' . $type . '" must not have an element type hint (' . $elementType . ').', 1248103053);
			}

			$this->properties[$name] = array(
				'type' => $type,
				'elementType' => $elementType,
				'lazy' => $lazy
			);
		} else {
			throw new \F3\FLOW3\Reflection\Exception\InvalidPropertyType('Invalid property type encountered: ' . $type, 1220387528);
		}
	}

	/**
	 * Returns the given property defined in this schema. Check with
	 * hasProperty($propertyName) before!
	 *
	 * @return array
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getProperty($propertyName) {
		return $this->properties[$propertyName];
	}

	/**
	 * Returns all properties defined in this schema
	 *
	 * @return array
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getProperties() {
		return $this->properties;
	}

	/**
	 * Sets the model type of the class this schema is referring to.
	 *
	 * @param integer $modelType The model type, one of the MODELTYPE_* constants.
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function setModelType($modelType) {
		if ($modelType < self::MODELTYPE_ENTITY || $modelType > self::MODELTYPE_VALUEOBJECT) throw new \InvalidArgumentException('"' . $modelType . '" is an invalid model type.', 1212519195);
		$this->modelType = $modelType;
	}

	/**
	 * Returns the model type of the class this schema is referring to.
	 *
	 * @return integer The model type, one of the MODELTYPE_* constants.
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function getModelType() {
		return $this->modelType;
	}

	/**
	 * Marks the class if it is root of an aggregate and therefore accessible
	 * through a repository - or not.
	 *
	 * @param boolean $isRoot TRUE if it is the root of an aggregate
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setAggregateRoot($isRoot) {
		$this->aggregateRoot = $isRoot;
	}

	/**
	 * Whether the class is an aggregate root and therefore accessible through
	 * a repository.
	 *
	 * @return boolean TRUE if it is managed
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function isAggregateRoot() {
		return $this->aggregateRoot;
	}

	/**
	 * If the class schema has a certain property.
	 *
	 * @param string $propertyName Name of the property
	 * @return boolean
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function hasProperty($propertyName) {
		return array_key_exists($propertyName, $this->properties);
	}

	/**
	 * Sets the property marked as uuid of an object with @uuid
	 *
	 * @param string $propertyName
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function setUUIDPropertyName($propertyName) {
		if (!array_key_exists($propertyName, $this->properties)) {
			throw new \InvalidArgumentException('Property "' . $propertyName . '" must be added to the class schema before it can be marked as UUID property.', 1233863842);
		}

		$this->uuidPropertyName = $propertyName;
	}

	/**
	 * Gets the name of the property marked as uuid of an object
	 *
	 * @return string
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function getUUIDPropertyName() {
		return $this->uuidPropertyName;
	}

	/**
	 * Marks the given property as one of properties forming the identity
	 * of an object. The property must already be registered in the class
	 * schema.
	 *
	 * @param string $propertyName
	 * @return void
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function markAsIdentityProperty($propertyName) {
		if (!array_key_exists($propertyName, $this->properties)) {
			throw new \InvalidArgumentException('Property "' . $propertyName . '" must be added to the class schema before it can be marked as identity property.', 1233775407);
		}
		if ($this->properties[$propertyName]['lazy'] === TRUE) {
			throw new \InvalidArgumentException('Property "' . $propertyName . '" must not be makred for lazy loading to be marked as identity property.', 1239896904);
		}

		$this->identityProperties[$propertyName] = $this->properties[$propertyName]['type'];
	}

	/**
	 * Gets the properties (names and types) forming the identity of an object.
	 *
	 * @return array
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @see markAsIdentityProperty()
	 */
	public function getIdentityProperties() {
		return $this->identityProperties;
	}

}
?>