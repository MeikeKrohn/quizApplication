<?php
/**
 * Created by PhpStorm.
 * User: meike
 * Date: 12.10.2018
 * Time: 15:49
 */

namespace App\Form\Type;


use App\Entity\Category;
use App\Entity\Exam;
use App\Entity\Question;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');

        $builder->add('category', EntityType::class, array(
            'class' => Category::class,
            'choice_label' => 'name'
        ));

        $builder->add('save', SubmitType::class, array(
            'label' => 'Save Exam'
        ));

        $formModifier = function (FormInterface $form, Category $category = null) {
            $questions = null === $category ? array() : $category->getQuestions();

            $form->add('questions', EntityType::class, array(
                'class' => Question::class,
                'choices' => $questions,
                'choice_label' => 'questionText',
                'multiple' => true,
                'by_reference' => false
            ));
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();

            $formModifier($event->getForm(), $data->getCategory());
        });

        $builder->get('category')->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) use ($formModifier) {
           $category = $event->getForm()->getData();

           $formModifier($event->getForm()->getParent(), $category);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Exam::class
        ));
    }
}