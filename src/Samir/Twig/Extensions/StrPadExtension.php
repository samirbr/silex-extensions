<?php

namespace Samir\Twig\Extensions;

/**
 * StrPadExtension class
 * 
 */
class StrPadExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'strpadleft' => new \Twig_Function_Method($this, 'strpadleft'),
        );
    }

    /**
     * Add the str_pad left php function
     *
     * @param  string $string
     * @param  int $pad_lenght
     * @param  string $pad_string
     * @return mixed
     */
    public function strpadleft($string, $pad_lenght, $pad_string = " ")
    {
        return str_pad($string, $pad_lenght, $pad_string, STR_PAD_LEFT);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'samir_strpad';
    }
}