<?php
/**
 * Created by PhpStorm.
 * User: meike
 * Date: 26.09.2018
 * Time: 16:50
 */

namespace App\Form\Type;


use App\Entity\Answer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('answerText');

        $builder->add('isCorrect', ChoiceType::class,
            array(
                'choices' => array(
                    'yes' => true,
                    'no' => false),
                'multiple' => false,
                'expanded' => true
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Answer::class
        ));
    }
}