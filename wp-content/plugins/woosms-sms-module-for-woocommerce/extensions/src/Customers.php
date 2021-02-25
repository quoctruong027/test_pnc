<?php
namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions;

abstract class Customers extends Extensions\Strict implements Extensions\ICustomers
{
    /** @var Extensions\Database\IDatabase */
    protected $db;

    /** @var bool */
    protected $empty = false;

    /** @var string */
    protected $table_user_key = 'customer_id';


    public function __construct(Extensions\Database\IDatabase $db)
    {
        $this->db = $db;
    }


    /**
     * @param array $customers
     * @param null|int $limit
     * @return array
     */
    abstract protected function loadCustomers(array $customers, $limit = null);


    /**
     * @param array $filters
     * @return array
     */
    abstract protected function filter(array $filters);


    /**
     * @return int
     */
    abstract protected function getTotal();


    /**
     * @param array $customers
     * @return int
     */
    abstract protected function getFilteredTotal(array $customers);


    public function loadCount(array $filter = array())
    {
        $customers = array();

        $filtered_count = $total = $this->getTotal();

        if (count($filter) > 0)
        {
            list($customers, $filtered) = $this->filter($filter);

            if ($filtered)
            {
                $filtered_count = $this->getFilteredTotal($customers);
            }
        }

        return array('total' => $total, 'count' => $filtered_count, 'limit' => $filtered_count !== 0 ? $this->loadCustomers((array) $customers, 10) : array());
    }


    public function load(array $filter = array())
    {
        $customers = array();

        if (count($filter) > 0)
        {
            list($customers, $_) = $this->filter($filter);
        }

        return $this->loadCustomers($customers);
    }

    protected function getSql(array $filter, $key_value = 'meta_value', $table = '')
    {
        $sql = array();

        strlen($table) > 0 && $table = '`'.$this->db->table($table).'`.';

        if (isset($filter['type']) && isset($filter['values']))
        {
            foreach ($filter['values'] as $value)
            {
                if (in_array($filter['type'], array('enum', 'string', 'float'), true))
                {
                    if ($value[0] === 'prefix')
                    {
                        $sql[] = $this->db->prepare($table."`".$key_value."` LIKE %s", array($value[1].'%'));
                    }
                    else if ($value[0] === 'sufix')
                    {
                        $sql[] = $this->db->prepare($table."`".$key_value."` LIKE %s", array('%'.$value[1]));
                    }
                    else if ($value[0] === 'substring')
                    {
                        $sql[] = $this->db->prepare($table."`".$key_value."` LIKE %s", array('%'.$value[1].'%'));
                    }
                    else if ($value[0] === 'empty')
                    {
                        $sql[] = "`".$key_value."` IS NULL OR TRIM(`".$key_value."`) = ''";
                    }
                    else if ($value[0] === 'filled')
                    {
                        $sql[] = "`".$key_value."` IS NOT NULL AND (`".$key_value."`) != ''";
                    }
                    else
                    {
                        $sql[] = $this->db->prepare($table."`".$key_value."` ".$this->getRelation($value[0])." %s", array($value[1]));
                    }
                }
                else if ($filter['type'] === "date-range")
                {
                    $sql[] = $this->db->prepare($table."`".$key_value."` BETWEEN %s AND %s", array($value[1], $value[2]));
                }
            }
        }

        return count($sql) > 0 ? implode(' OR ', $sql) : ' FALSE';
    }

    protected function getRelation($relation)
    {
        $relation_list = array(
            'is'    => '=',
            'not'   => '!=',
            'gt'    => '>',
            'lt'    => '<'
        );

        return isset($relation_list[$relation]) ? $relation_list[$relation] : '=';
    }

    protected function getCustomers(Extensions\Database\Result $result, array $customers)
    {
        $output = array();

        if ($result->getNumRows() > 0)
        {
            foreach ($result as $row)
            {
                $output[] = (int) $row->{$this->table_user_key};
            }
        }
        else
        {
            $this->empty = true;
        }

        return $this->empty ? array() : (count($customers) > 0 ? array_intersect($customers, $output) : $output);
    }
}
