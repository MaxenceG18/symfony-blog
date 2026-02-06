<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function __construct(
        private UserRepository $userRepository,
        private Security $security
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentUser = $this->security->getUser();

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'article',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le titre de l\'article'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 10,
                    'placeholder' => 'Rédigez votre article ici...'
                ]
            ])
            ->add('picture', UrlType::class, [
                'label' => 'URL de l\'image',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://exemple.com/image.jpg'
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'placeholder' => 'Choisir une catégorie',
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
        ;
        
        // On passe les utilisateurs disponibles via les options pour le JS
        $builder->setAttribute('available_collaborators', 
            $currentUser ? $this->userRepository->findAllExcept($currentUser) : []
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }

    public function getAvailableCollaborators(): array
    {
        $currentUser = $this->security->getUser();
        return $currentUser ? $this->userRepository->findAllExcept($currentUser) : [];
    }
}
