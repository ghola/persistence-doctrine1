<?php
namespace PSB\Persistence\Doctrine1;


class Schema
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var array
     */
    private $definition;

    /**
     * @var array
     */
    private $options;

    /**
     * @param string      $tableName
     * @param array $definition
     * @param array $options
     */
    public function __construct($tableName, array $definition, array $options = [])
    {
        $this->tableName = $tableName;
        $this->definition = $definition;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
