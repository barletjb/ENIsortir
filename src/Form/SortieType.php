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
use Symfony\Component\Validator\Constraints\NotNull;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class,[
                'label' => 'Nom de la sortie',
                'required' => false,
            ])
            ->add('dateHeureDebut',DateTimeType::class,[
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
                'required' => false,

            ])
            ->add('dateLimiteInscription',DateTimeType::class,[
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('nbInscriptionsMax',IntegerType::class,[
                'label' => 'Nombre de places',
                'required' => false,
            ])
            ->add('duree',IntegerType::class,[
                'label' => 'Durée (en minutes)',
                'required' => false,
                'attr' => [
                    'min' => 1,
                    'step' => 1,
                ],
            ])
            ->add('infosSortie',TextareaType::class,[
                'label' => 'Description et infos',
                'required' => false,
            ])

            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'label' => 'Lieu',
                'placeholder' => '-- Choisir un lieu --',
                'required' => false,
                'choice_label' => function (Lieu $lieu) {
                    return $lieu->getNom() ?: 'Nom non défini';
                },
                'attr'=> [
                    'id' => 'sortie_lieu',
                ]

            ])
//            ->add('etat', EntityType::class, [
//                'class' => Etat::class,
//                'label' => 'Etat',
//                'data' => "Créée",
//                'attr' => [
//                    'hidden' => true,
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
