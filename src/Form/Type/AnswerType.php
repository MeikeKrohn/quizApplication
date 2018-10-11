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
use Symfony\Component\Form\Button;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        /*
        $builder->add('delete', ButtonType::class,
            array(
                'attr' => array(
                    'class' => 'deleteExistingAnswerButton')
            ));
        */

        $builder->add('answerText');
        $builder->add('isCorrect', ChoiceType::class,
            array(
                'choices' => array(
                    'yes' => true,
                    'no' => false),
                'multiple' => false, 'expanded' => true
            ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if($data != null) {
                $form->add('delete', ButtonType::class, array(
                    'attr' => array('data-id' => $data->getId(), 'class' => 'deleteExistingAnswerButton')
                ));
            }
        });

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Answer::class
        ));
    }
}