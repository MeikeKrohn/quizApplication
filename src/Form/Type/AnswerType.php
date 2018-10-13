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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('answerText', TextType::class,
            array(
                'error_bubbling' => true,
                'trim' => true
            ));
        $builder->add('isCorrect', ChoiceType::class,
            array(
                'choices' => array(
                    'yes' => true,
                    'no' => false),
                'multiple' => false,
                'expanded' => true,
                'error_bubbling' => true
            ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data != null) {
                $form->add('delete', ButtonType::class, array(
                    'attr' => array(
                        'data-id' => $data->getId(),
                        'class' => 'deleteExistingAnswerButton',
                        'data-confirm' => 'Are you sure you want to delete this Answer?'
                    )
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