<?php 

namespace Uviba;

use \ArrayAccess;

class ResponseObject implements  ArrayAccess
{
  
	public function __construct() {
       // if ($json) $this->set(json_decode($json, true));
    }

    public function set($data) {
    	if(!is_array($data)){
    		if(is_string($data)){
    			$data=json_decode($data, true);
    		}else{
    			//so it is JSON
    			$data = json_decode(json_encode($data), true);
    		}
    	}
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sub = new ResponseObject;
                $sub->set($value);
                $value = $sub;
            }
            $this->{$key} = $value;
        }
    }




    //public $data;
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
           // $this->data[] = $value;
        } else {
            $this->{$offset} = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->{$offset});
    }
    
    public function offsetUnset($offset) {
        unset($this->{$offset});
    }
    
    public function offsetGet($offset) {
        return isset($this->{$offset}) ? $this->{$offset} : null;
    }


}