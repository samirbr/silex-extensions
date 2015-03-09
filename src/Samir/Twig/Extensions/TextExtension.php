<?php

namespace Samir\Twig\Extensions;

/**
 * TextExtension class
 * 
 */
class TextExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'wordwrap' => new \Twig_Function_Method($this, 'wordwrapMock'),
	    'normalize' => new \Twig_Function_Method($this, 'normalize'),
        );
    }
    
    public function wordwrapMock($str, $length, $append)
    {
      return self::wordwrap($str, $length, $append);
    }

    public function normalize($str) 
    {
      return str_replace(array('á', 'à', 'â', 'ã', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç', ' '), array('a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c', '-'), strtolower($str));
    }
    
    public static function wordwrap($str, $length, $append) 
    {    
      if (strlen($str) > $length) {
        $array = explode(' ', strip_tags($str));
        $output = array();
        $count = 0;
      
        foreach ($array as $word) {
          $count += strlen($word) + 1;
          
          if ($count > $length) {
            break;
          }
          
          $output[] = $word;
        }
        
        $str = implode(' ', $output) . '...';
      }
      
      return $str;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'samir_text';
    }
}
