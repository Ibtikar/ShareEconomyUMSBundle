<?php

namespace Ibtikar\ShareEconomyUMSBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use AppBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class AdminType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = User::getRolesList();
        unset($roles['ROLE_SUPER_ADMIN']);

        $entity = $builder->getData();

        if (!$entity || null === $entity->getId()) {
            $builder->add('file', FileType::class, array(
                'required' => false,
                'attr' => array('data-rule-imageDimensions' => '300', 'accept' => 'image/jpg,image/jpeg,image/pjpeg,image/png', 'data-rule-filesize' => '1', 'data-error-after-selector' => '.admin_file_selector', 'data-image-type' => 'profile')));
        }else{
            $builder->add('file', FileType::class, array(
                'required' => false,
                'attr' => array('data-rule-imageDimensions' => '300', 'accept' => 'image/jpg,image/jpeg,image/pjpeg,image/png', 'data-rule-filesize' => '1', 'data-error-after-selector' => '.admin_file_selector', 'data-image-type' => 'profile', 'data-image-url' => $entity->getWebPath(), 'data-image-alt' => $entity->__toString())));
        }
        
        $builder->add('fullName', TextType::class, array('attr' => array('data-rule-maxlength' => "25", 'data-rule-minlength' => "4")))
                ->add('email', EmailType::class, array('attr' => array("data-rule-email" => "true")))
                ->add('phone', TextType::class, array('attr' => array('data-rule-phone' => true)))
                ->add('roles', ChoiceType::class, array('choices' => $roles))
                ->add('userPassword', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match',
                    'options' => array('attr' => array('class' => 'form-control password-field')),
                    'required' => true,
                    'first_options' => array('label' => 'Password', 'attr' => array('autocomplete' => 'off')),
                    'second_options' => array('label' => 'Repeat Password', 'attr' => array('autocomplete' => 'off'))));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ibtikar\ShareEconomyUMSBundle\Entity\BaseUser'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'admin';
    }

}
