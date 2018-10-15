<?php


namespace mattvb91\LightModel\DB;

/**
 * Basic class for handling column interaction
 *
 * Class Column
 * @package mattvb91\LightModel\DB
 */
class Column
{

    const TYPE_VARCHAR = 'varchar';
    const TYPE_INT = 'int';
    const TYPE_TINYINT = 'tinyint';
    const TYPE_DOUBLE = 'double';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_DATETIME = 'datetime';
    const TYPE_CHAR = 'char';
    const TYPE_TEXT = 'text';
    const TYPE_FLOAT = 'float';
    const TYPE_DATE = 'date';
    const TYPE_BLOB = 'blob';
    const TYPE_ENUM = 'enum';

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $type = self::TYPE_VARCHAR;

    /**
     * @var bool
     */
    private $null = true;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @var mixed
     */
    private $extra;

    /**
     * Column constructor.
     * @param $values
     */
    public function __construct($values = [])
    {
        $this->field = $values['Field'];
        $this->null = ($values['Null'] == 'NO') ? false : true;
        $this->type = self::getTypeFromDescription($values['Type']);
        $this->default = $values['Default'];
    }

    private static function getTypeFromDescription($description)
    {
        if (strpos($description, self::TYPE_INT) !== false)
            return self::TYPE_INT;

        if (strpos($description, self::TYPE_VARCHAR) !== false)
            return self::TYPE_VARCHAR;

        if (strpos($description, self::TYPE_TINYINT) !== false)
            return self::TYPE_TINYINT;

        if (strpos($description, self::TYPE_CHAR) !== false)
            return self::TYPE_CHAR;

        if (strpos($description, self::TYPE_TIMESTAMP) !== false)
            return self::TYPE_TIMESTAMP;

        if (strpos($description, self::TYPE_DATETIME) !== false)
            return self::TYPE_DATETIME;

        if (strpos($description, self::TYPE_TEXT) !== false)
            return self::TYPE_TEXT;

        if (strpos($description, self::TYPE_DOUBLE) !== false)
            return self::TYPE_DOUBLE;

        if (strpos($description, self::TYPE_FLOAT) !== false)
            return self::TYPE_FLOAT;

        if (strpos($description, self::TYPE_DATE) !== false)
            return self::TYPE_DATE;

        if (strpos($description, self::TYPE_BLOB) !== false)
            return self::TYPE_BLOB;

        if (strpos($description, self::TYPE_ENUM) !== false)
            return self::TYPE_ENUM;
    }

    /**
     * @return mixed
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->null;
    }

    /**
     * @return mixed
     */
    public function getDefault(): ?string
    {
        return $this->default;
    }

    /**
     * @return mixed
     */
    public function getExtra(): ?string
    {
        return $this->extra;
    }
}