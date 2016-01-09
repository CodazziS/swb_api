<?php
class Date {
    static public function get_date () {
        $date = date("Y-m-d");
        return $date;
    }
    static public function get_UTC () {
        $date = new DateTime(null, new DateTimeZone('UTC'));
        return $date;
    }
    static public function get_tomorrow_UTC () {
        $date = new DateTime(null, new DateTimeZone('UTC'));
        $date->add(new DateInterval('PT24H'));
        return $date;
    }
    static public function to_datetime_UTC($str, $tz) {
        $date = new DateTime($str, new DateTimeZone($tz));
        $date->setTimezone(new DateTimeZone("UTC"));
        return $date;
    }
}