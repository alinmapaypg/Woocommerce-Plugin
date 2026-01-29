<?php




abstract class woocommerceLib
{
    // Private constants
  

    public static function _Hashcreation($text)
    {
        $hash = hash('sha256', $text);
        return $hash;
    }

   
   
}
