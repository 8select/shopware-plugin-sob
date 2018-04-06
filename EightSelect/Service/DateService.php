<?php
namespace EightSelect\Service;

class DateService
{
    public static function getNextMidnight()
    {
        $date = new \DateTime();
        $date->setTime(0, 0);
        $date->add(new \DateInterval('P1D'));
        return $date;
    }

}
