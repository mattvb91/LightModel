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

    private static $_types = [
        self::TYPE_VARCHAR,
        self::TYPE_INT,
        self::TYPE_TINYINT,
        self::TYPE_DOUBLE,
        self::TYPE_TIMESTAMP,
        self::TYPE_DATETIME,
        self::TYPE_CHAR,
        self::TYPE_TEXT,
        self::TYPE_FLOAT,
        self::TYPE_DATE,
        self::TYPE_BLOB,
        self::TYPE_ENUM,
    ];

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

    /**
     * @param string $description
     * @return mixed
     */
    private static function getTypeFromDescription(string $description)
    {
        foreach (self::$_types as $type) {
            if (strpos($description, $type) !== false)
                return $type;
        }
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