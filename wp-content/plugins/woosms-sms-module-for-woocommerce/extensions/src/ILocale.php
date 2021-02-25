<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface ILocale
{
    /**
     * @param $price
     * @param null $currency
     * @return string
     */
    public function price($price, $currency = null);


    /**
     * @param $number
     * @return string
     */
    public function float($number);


    /**
     * @param $number
     * @return string
     */
    public function int($number);


    /**
     * @param \DateTime $dateTime
     * @return string
     */
    public function datetime(\DateTime $dateTime);


    /**
     * @param \DateTime $date
     * @return string
     */
    public function date(\DateTime $date);


    /**
     * @param \DateTime $date
     * @return string
     */
    public function time(\DateTime $date);
}
