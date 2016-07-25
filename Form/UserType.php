<?php

namespace Ibtikar\ShareEconomyUMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('fullName', null, array('constraints' => array(new Assert\NotBlank())))
                ->add('email', null, array('constraints' => array(new Assert\NotBlank(), new Assert\Email())))
                ->add('password', null, array('constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 6, 'max' => 50)))))
                ->add('phone', null, array('constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 6, 'max' => 50)))))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ibtikar\ShareEconomyUMSBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user';
    }

}
