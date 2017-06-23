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