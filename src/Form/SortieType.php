<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class,[
                'label' => 'Nom de la sortie',
            ])
            ->add('dateHeureDebut',DateTimeType::class,[
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
            ])
            ->add('dateLimiteInscription',DateTimeType::class,[
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionsMax',TextType::class,[
                'label' => 'Nombre de places',
            ])
            ->add('duree',IntegerType::class,[
                'label' => 'Durée',
                'attr' => [
                    'min' => 1,
                    'step' => 1,
                ],
            ])
            ->add('infosSortie',TextareaType::class,[
                'label' => 'Description et infos',
            ])

            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'label' => 'Lieu',
                'placeholder' => '-- Choisir un lieu --',
                'choice_label' => function (Lieu $lieu) {
                    return $lieu->getNom();
                },
            ])
//            ->add('etat', EntityType::class, [
//                'class' => Etat::class,
//                'label' => 'Etat',
//                'attr' => [
//                    'value' => "Créée"
//                ]
//            ])



        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
