<?php
namespace Essence\Database;

use DateTime;
use DateTimeZone;
class TimeHelper
{
    public static function dateTimeToTimestamp($datetime)
    {
        return strtotime($datetime);
    }
    
    public static function timeStampToDateTime($unixTimestamp)
    {
        return date("Y-m-d H:i:s", $unixTimestamp);
    }

    public static function UTCToTimezone($timestamp, $timezone, $readable = true)
    {
        $datetime = new DateTime('@' . $timestamp, new DateTimeZone('UTC'));
        $datetime->setTimezone(new DateTimeZone($timezone));
        if($readable)
            return $datetime->format('d-m-Y H:i:s');
        else
            return $datetime->getTimestamp();
    }

    public static function getTimezones($utc_only = false, $group_by_abbr = false)
    {
        $timezone_array = [];
        $abbr_index_map = [];
        $identifiers = DateTimeZone::listIdentifiers();
        $current_time = time();
        foreach($identifiers as $key => $identifier)
        {
            $timezone = new DateTimeZone($identifier);
            $transition = $timezone->getTransitions($current_time, $current_time);
            
            $abbr = $transition[0]['abbr'];
            
            if(General::Contains('-', $abbr) || General::Contains('+', $abbr))
            {
                if(strlen($abbr) > 3)
                {
                    $abbr = substr_replace($abbr, ':', strlen($abbr)-2, 0);
                }
                $abbr = 'UTC'.$abbr;
            }
            
            if(!$utc_only || General::Contains('UTC', $abbr))
            {
                if($group_by_abbr && isset($abbr_index_map[$abbr])){
                    //append to existing
                    $key = $abbr_index_map[$abbr];
                    $timezone_array[$key]['timezone'] .= ', '. $identifier;
                }
                else
                {
                    //new abbr
                    $timezone_array[$key] = [
                        'index' => $key,
                        'timezone'=> $identifier,
                        'display_abbr' => $abbr
                    ];
                    $abbr_index_map[$abbr] = $key;
                }
            }
            
            
        }
        return $timezone_array;
    }
}