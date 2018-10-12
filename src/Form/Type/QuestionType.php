<?php
/**
 * Created by PhpStorm.
 * User: meike
 * Date: 26.09.2018
 * Time: 16:53
 */

namespace App\Form\Type;


use App\Entity\Category;
use App\Entity\Question;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('questionText', TextType::class, array(
            'error_bubbling' => true
        ));

        $builder->add('category', EntityType::class, array(
            'class' => Category::class,
            'choice_label' => 'name',
            'error_bubbling' => true
        ));

        $builder->add('answers', CollectionType::class, array(
            'entry_type' => AnswerType::class,
            'entry_options' => array('label' => false),
            'allow_add' => true,
            'by_reference' => false,
            'allow_delete' => true,
            'error_bubbling' => true
        ));

        $builder->add('save', SubmitType::class, array(
            'label' => 'Save',
            'attr' => array(
                'class' => 'orangeButton',
            )
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Question::class
        ));
    }

}