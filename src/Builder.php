<?php

namespace CodeIgniter\Startci;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Traits\ConditionalTrait;

class Builder extends BaseBuilder
{
    use ConditionalTrait;
    
    function whereRaw($cond)
    {
        $this->where($cond, null, false);
        return $this;
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $type Mode to set
     *
     * @return mixed
     */
    public function rs(string $type = 'object')
    {
        return $this->result($type);
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $type Mode to set
     *
     * @return mixed
     */
    public function result(string $type = 'object')
    {
        return $this->get()->getResult($type);
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $type Mode to set
     *
     * @return mixed
     */
    public function first(string $type = 'object')
    {
        return $this->get()->getFirstRow($type);
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $type Mode to set
     *
     * @return mixed
     */
    public function last(string $type = 'object')
    {
        return $this->get()->getLastRow($type);
    }

    /**
     * Sets a test mode status.
     *
     * @param boolean $mode Mode to set
     *
     * @return BaseBuilder
     */
    function def($values = [])
    {
        $table = $this->tableName;
        $db = $this->db();
        $v = [];
        foreach ($db->getFieldNames($table) as $key => $value) {
            $v[$value] = (!isset($values[$value])) ? null : $values[$value];
        }
        return (object) $v;
    }
}
