<?php

namespace App\Form;

use App\Entity\GroupePrive;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupePriveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du groupe privé',
                'required' => true,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nomComplet',
                'label' => 'Membres à ajouter : ',
                'multiple' => true,
                'expanded' => false,
                'query_builder' => function (UserRepository $repo) use ($options) {
                    $groupe = $options['data'];

                    $excluded = [];
                    if ($groupe && $groupe->getChefGroupe()) {
                        $excluded[] = $groupe->getChefGroupe()->getId();
                    }

                    $qb = $repo->createQueryBuilder('u');
                    if (!empty($excluded)) {
                        $qb->where($qb->expr()->notIn('u.id', ':excluded'))
                            ->setParameter('excluded', $excluded);
                    }

                    return $qb->orderBy('u.nom', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GroupePrive::class,
        ]);
    }
}
