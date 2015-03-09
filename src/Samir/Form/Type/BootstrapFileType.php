<?php

namespace Samir\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;

class BootstrapFileType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
      $resolver->setDefaults(array(
        'attr' => array(
          'show_progress' => true,
          'shiw_preview'  => false,
          'thumbnail'     => true,
        )
      ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder->setAttribute('defaults', array(
        'thumbnail' => isset($options['thumbnail']) ? $options['thumbnail'] : true,
        'show-preview' => isset($options['show_preview']) ? $options['show_preview'] : false,
        'show-progress' => isset($options['show_progress']) ? $options['show_progress'] : true,
      ));
      ;
    } 
    
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
      $view->set('defaults', $form->getAttribute('defaults'));
    }

    public function getParent()
    {
        return 'file';
    }

    public function getName()
    {
        return 'bootstrap_file';
    }
}