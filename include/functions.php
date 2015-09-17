<?php
function get_index_value($index, $array=array(), $default=NULL){
   if(is_array($array)&&array_key_exists($index, $array)){
      return $array[$index];
   } else{
      return $default;
   }
   unset($array, $index, $default);
}
?>