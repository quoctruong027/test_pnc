<?php

namespace BulkGate\Extensions\Database;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface IDatabase
{
    /**
     * @param string $sql
     * @return Result
     */
    public function execute($sql);


    /**
     * @param string $sql
     * @param array $params
     * @return string
     */
    public function prepare($sql, array $params = array());


    /**
     * @return mixed
     */
    public function lastId();


    /**
     * @param string $string
     * @return string
     */
    public function escape($string);


    /**
     * @return string
     */
    public function prefix();


    /**
     * @return array
     */
    public function getSqlList();


    /**
     * @param string $table
     * @return string
     */
    public function table($table);
}
