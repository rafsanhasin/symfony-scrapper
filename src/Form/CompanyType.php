<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, array(
                    'attr' => array('class' => 'form-control'),
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length(['max' => 255]),
                    ],
                )
            )
            ->add('registration_code', IntegerType::class, array(
                    'attr' => array('class' => 'form-control'),
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length(['min' => 9]),
                    ],
                )
            )
            ->add('vat', TextType::class, array(
                    'attr' => array('class' => 'form-control'),
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length(['max' => 50]),
                    ],
                )
            )
            ->add('address', TextareaType::class, array(
                    'attr' => array('class' => 'form-control'),
                )
            )
            ->add('mobile_phone', TextType::class, array(
                    'attr' => array('class' => 'form-control'),
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Length(['max' => 30]),
                    ],
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
