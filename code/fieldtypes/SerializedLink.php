<?php

/**
 * Represents a serialized Link object
 *
 * Example definition via {@link DataObject::$db}:
 * <code>
 * static $db = array(
 * 	"MyLink" => "SerializedLink",
 * );
 * </code>
 *
 *
 * @package linkable
 * @subpackage fieldtypes
 */
class SerializedLink extends StringField {

	private static $casting = array(
		'LinkObject' => 'Link',
	);

	/**
	 * (non-PHPdoc)
	 * @see DBField::requireField()
	 */
	public function requireField() {
		$parts = array(
			'datatype' => 'mediumtext',
			'character set' => 'utf8',
			'collate' => 'utf8_general_ci',
			'arrayValue' => $this->arrayValue
		);

		$values= array(
			'type' => 'text',
			'parts' => $parts
		);

		DB::require_field($this->tableName, $this->name, $values, $this->default);
	}

	/**
	 * Return the value of the field as a Link object
	 * @return Link
	 */
	public function LinkObject() {
		if (!$this->value) {
			return new Link();
		}
		return new Link(unserialize($this->value));
	}

	/**
	 * Allows a sub-class of TextParser to be rendered.
	 *
	 * @see TextParser for implementation details.
	 * @return string
	 */
	public function Parse($parser = "TextParser") {
		if($parser == "TextParser" || is_subclass_of($parser, "TextParser")) {
			$obj = new $parser($this->value);
			return $obj->parse();
		} else {
			// Fallback to using raw2xml and show a warning
			// TODO Don't kill script execution, we can continue without losing complete control of the app
			user_error("Couldn't find an appropriate TextParser sub-class to create (Looked for '$parser')."
				. "Make sure it sub-classes TextParser and that you've done ?flush=1.", E_USER_WARNING);
			return Convert::raw2xml($this->value);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see DBField::scaffoldFormField()
	 */
	public function scaffoldFormField($title = null, $params = null) {
		// Automatically determine null (empty string)
		return new LinkField($this->name, $title);
	}

	/**
	 * (non-PHPdoc)
	 * @see DBField::scaffoldSearchField()
	 */
	public function scaffoldSearchField($title = null, $params = null) {
		return new TextField($this->name, $title);
	}
}
