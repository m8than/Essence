<?php
namespace Essence\Config;

use IteratorAggregate;
use ArrayAccess;
use Countable;

class ConfigReader implements IteratorAggregate, ArrayAccess, Countable
{
    private $location;
    protected $data;
    
    public function __construct($location)
    {
        $this->location = $location;
        $this->load();
    }

    public function load()
    {
        //Output buffering for security
        ob_start();
        $this->data = require($this->location);
        ob_end_clean();
    }
    
    /*
     Iterator overrides
     */
     public function getIterator()
     {
         return new ArrayIterator($this->data);
     }
 
     /*
     ArrayAccess overrides
     */
     public function offsetSet($key, $value)
     {
         if (is_null($key)) {
             $this->data[] = $value;
         } else {
             $this->data[$key] = $value;
         }
     }
 
     public function offsetExists($key)
     {
         return isset($this->data[$key]);
     }
 
     public function offsetUnset($key)
     {
         unset($this->data[$key]);
     }
 
     public function offsetGet($key)
     {
         return $this->data[$key];
     }
 
     /*
     Countable overrides
     */
     public function count()
     {
         return count($this->data);
     }
 
     /*
     Magic methods for property access
     */
     public function __isset($key)
     {
         return isset($this->data[$key]);
     }
 
     public function __get($key)
     {
         return $this->data[$key];
     }
 
     public function __set($key, $value)
     {
         if (is_null($key)) {
             $this->data[] = $value;
         } else {
             $this->data[$key] = $value;
         }
     }
}
?>