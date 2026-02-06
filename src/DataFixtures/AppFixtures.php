<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\CollaborationRequest;
use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Enum\CollaborationStatus;
use App\Enum\CommentStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $categories = [];
        $categoryData = [
            ['name' => 'Technologie', 'description' => 'Articles sur les dernières tendances technologiques, développement web et innovations.'],
            ['name' => 'Voyage', 'description' => 'Découvrez les plus belles destinations et conseils de voyage.'],
            ['name' => 'Lifestyle', 'description' => 'Conseils et astuces pour améliorer votre quotidien.'],
            ['name' => 'Cuisine', 'description' => 'Recettes délicieuses et conseils culinaires.'],
            ['name' => 'Sport', 'description' => 'Actualités sportives et conseils fitness.'],
        ];

        foreach ($categoryData as $data) {
            $category = new Category();
            $category->setName($data['name']);
            $category->setDescription($data['description']);
            $manager->persist($category);
            $categories[] = $category;
        }

        $users = [];

        $admin = new User();
        $admin->setEmail('admin@blog.com');
        $admin->setFirstName('Jean');
        $admin->setLastName('Admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsActive(true);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setProfilePicture('https://i.pravatar.cc/150?img=1');
        $manager->persist($admin);
        $users[] = $admin;

        $admin2 = new User();
        $admin2->setEmail('marie@blog.com');
        $admin2->setFirstName('Marie');
        $admin2->setLastName('Dupont');
        $admin2->setRoles(['ROLE_ADMIN']);
        $admin2->setIsActive(true);
        $admin2->setPassword($this->passwordHasher->hashPassword($admin2, 'admin123'));
        $admin2->setProfilePicture('https://i.pravatar.cc/150?img=5');
        $manager->persist($admin2);
        $users[] = $admin2;

        $regularUsersData = [
            ['email' => 'pierre@example.com', 'firstName' => 'Pierre', 'lastName' => 'Martin', 'picture' => 'https://i.pravatar.cc/150?img=3'],
            ['email' => 'sophie@example.com', 'firstName' => 'Sophie', 'lastName' => 'Bernard', 'picture' => 'https://i.pravatar.cc/150?img=9'],
            ['email' => 'lucas@example.com', 'firstName' => 'Lucas', 'lastName' => 'Petit', 'picture' => 'https://i.pravatar.cc/150?img=12'],
            ['email' => 'emma@example.com', 'firstName' => 'Emma', 'lastName' => 'Leroy', 'picture' => 'https://i.pravatar.cc/150?img=16'],
        ];

        foreach ($regularUsersData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setRoles(['ROLE_USER']);
            $user->setIsActive(true);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
            $user->setProfilePicture($data['picture']);
            $manager->persist($user);
            $users[] = $user;
        }

        $pendingUser = new User();
        $pendingUser->setEmail('nouveau@example.com');
        $pendingUser->setFirstName('Nouveau');
        $pendingUser->setLastName('Utilisateur');
        $pendingUser->setRoles(['ROLE_USER']);
        $pendingUser->setIsActive(false);
        $pendingUser->setPassword($this->passwordHasher->hashPassword($pendingUser, 'user123'));
        $manager->persist($pendingUser);
        $users[] = $pendingUser;

        $posts = [];
        $postData = [
            [
                'title' => 'Introduction à Symfony 7 : Les nouveautés à découvrir',
                'content' => "Symfony 7 apporte son lot de nouveautés passionnantes pour les développeurs PHP. Dans cet article, nous allons explorer les principales fonctionnalités qui font de cette version une évolution majeure du framework.\n\nParmi les améliorations notables, on trouve une meilleure gestion des attributs PHP 8, des performances accrues et une intégration native encore plus poussée avec les standards modernes du web.\n\nLa nouvelle architecture de composants permet une modularité accrue, facilitant ainsi la maintenance et l'évolution des applications. Les développeurs apprécieront particulièrement les améliorations apportées au système de routing et à la gestion des formulaires.\n\nL'écosystème Symfony continue de s'enrichir avec de nouveaux bundles et une communauté toujours plus active. Si vous n'avez pas encore migré vers Symfony 7, c'est le moment idéal pour découvrir tout ce que cette version a à offrir !",
                'category' => 0,
                'author' => 0,
                'picture' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800'
            ],
            [
                'title' => 'Les plus belles plages de Thaïlande à visiter',
                'content' => "La Thaïlande regorge de plages paradisiaques qui font rêver les voyageurs du monde entier. Des eaux cristallines aux sables fins, découvrez notre sélection des destinations incontournables.\n\nKo Phi Phi reste une destination mythique avec ses falaises calcaires impressionnantes et ses eaux turquoise. Maya Bay, rendue célèbre par le film 'La Plage', offre un décor de carte postale.\n\nPour ceux qui recherchent plus de tranquillité, les îles de Ko Lipe ou Ko Tao proposent des ambiances plus intimistes tout en conservant la beauté caractéristique des plages thaïlandaises.\n\nN'oubliez pas d'explorer les fonds marins lors de sessions de snorkeling ou de plongée. La vie marine y est exceptionnelle et les récifs coralliens abritent une biodiversité remarquable.\n\nQuelle que soit la période de l'année, la Thaïlande vous accueille avec sa légendaire hospitalité et ses paysages à couper le souffle.",
                'category' => 1,
                'author' => 1,
                'picture' => 'https://images.unsplash.com/photo-1552465011-b4e21bf6e79a?w=800'
            ],
            [
                'title' => '10 habitudes pour une vie plus équilibrée',
                'content' => "Adopter de bonnes habitudes au quotidien peut transformer radicalement votre qualité de vie. Voici dix pratiques simples mais efficaces pour retrouver l'équilibre.\n\n1. Commencez chaque journée par 10 minutes de méditation\n2. Buvez un grand verre d'eau au réveil\n3. Faites au moins 30 minutes d'exercice par jour\n4. Limitez votre temps d'écran le soir\n5. Pratiquez la gratitude en notant 3 choses positives chaque jour\n\n6. Planifiez vos repas à l'avance\n7. Accordez-vous des pauses régulières au travail\n8. Cultivez vos relations sociales\n9. Dormez au moins 7 heures par nuit\n10. Prenez le temps de vous ressourcer dans la nature\n\nCes habitudes, appliquées régulièrement, vous aideront à maintenir un équilibre entre vie professionnelle et personnelle tout en préservant votre santé mentale et physique.",
                'category' => 2,
                'author' => 0,
                'picture' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?w=800'
            ],
            [
                'title' => 'Recette : Le parfait risotto aux champignons',
                'content' => "Le risotto aux champignons est un classique de la cuisine italienne qui séduit par sa texture crémeuse et ses saveurs automnales. Voici notre recette pour réussir ce plat à coup sûr.\n\nIngrédients (pour 4 personnes) :\n- 300g de riz arborio\n- 250g de champignons de Paris\n- 100g de champignons shiitake\n- 1 oignon\n- 2 gousses d'ail\n- 150ml de vin blanc\n- 1L de bouillon de légumes\n- 50g de parmesan râpé\n- 50g de beurre\n- Huile d'olive, sel, poivre\n\nFaites revenir les champignons émincés dans une poêle avec de l'huile d'olive. Réservez. Dans une casserole, faites suer l'oignon et l'ail dans le beurre. Ajoutez le riz et toastez-le 2 minutes.\n\nDéglacez au vin blanc, puis ajoutez le bouillon louche par louche en remuant constamment. Incorporez les champignons et le parmesan en fin de cuisson.\n\nServez immédiatement, parsemé de persil frais et d'un filet d'huile d'olive de qualité.",
                'category' => 3,
                'author' => 1,
                'picture' => 'https://images.unsplash.com/photo-1476124369491-e7addf5db371?w=800'
            ],
            [
                'title' => 'Guide complet pour débuter la course à pied',
                'content' => "La course à pied est l'un des sports les plus accessibles et bénéfiques pour la santé. Que vous souhaitiez perdre du poids, améliorer votre endurance ou simplement vous sentir mieux, ce guide vous accompagnera dans vos premiers pas.\n\nAvant de commencer, investissez dans une bonne paire de chaussures adaptée à votre foulée. C'est le seul équipement vraiment indispensable. Faites-vous conseiller dans un magasin spécialisé.\n\nProgramme pour débutants :\n- Semaine 1-2 : Alternez 1 minute de course et 2 minutes de marche pendant 20 minutes\n- Semaine 3-4 : Passez à 2 minutes de course, 1 minute de marche\n- Semaine 5-6 : 3 minutes de course, 1 minute de marche\n- Semaine 7-8 : 5 minutes de course, 1 minute de marche\n\nÉcoutez votre corps et n'hésitez pas à adapter ce programme selon vos sensations. L'important est la régularité : 3 séances par semaine suffisent pour progresser significativement.\n\nN'oubliez pas les étirements après chaque séance et hydratez-vous correctement !",
                'category' => 4,
                'author' => 0,
                'picture' => 'https://images.unsplash.com/photo-1461896836934- voices-from-runners?w=800'
            ],
            [
                'title' => 'Intelligence Artificielle : Ce que 2026 nous réserve',
                'content' => "L'intelligence artificielle continue de transformer notre quotidien à un rythme effréné. En 2026, plusieurs tendances majeures redéfinissent notre rapport à cette technologie.\n\nLes modèles de langage atteignent désormais des niveaux de sophistication impressionnants, permettant des interactions toujours plus naturelles et pertinentes. Les assistants IA s'intègrent dans tous les aspects de notre vie professionnelle et personnelle.\n\nLa génération d'images, de vidéos et de musique par IA ouvre de nouvelles perspectives créatives, tout en soulevant des questions éthiques importantes sur l'authenticité et les droits d'auteur.\n\nLes entreprises adoptent massivement ces technologies pour optimiser leurs processus, tandis que les développeurs disposent d'outils toujours plus puissants pour créer des applications innovantes.\n\nCependant, les enjeux de régulation et d'éthique restent au cœur des débats. Comment encadrer ces technologies tout en favorisant l'innovation ? C'est le défi majeur de notre époque.",
                'category' => 0,
                'author' => 1,
                'picture' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800'
            ],
            [
                'title' => 'Week-end à Lisbonne : Itinéraire parfait',
                'content' => "Lisbonne, la capitale portugaise, est une destination idéale pour un week-end dépaysant. Entre ses collines pittoresques, sa gastronomie savoureuse et son ambiance chaleureuse, découvrez notre itinéraire pour profiter au maximum de votre séjour.\n\nJour 1 : Commencez par le quartier de l'Alfama, le plus ancien de la ville. Perdez-vous dans ses ruelles étroites et montez jusqu'au château São Jorge pour une vue panoramique exceptionnelle. Le soir, dégustez un dîner traditionnel en écoutant du Fado.\n\nJour 2 : Direction Belém pour découvrir le monastère des Hiéronymites et la célèbre tour de Belém. Ne manquez pas les Pastéis de Belém, les authentiques tartes à la crème portugaises.\n\nJour 3 : Explorez le Bairro Alto et ses boutiques tendance, puis terminez par le quartier de LX Factory, un espace créatif installé dans une ancienne usine.\n\nPratique : Le tramway 28 est incontournable pour traverser les quartiers historiques de la ville.",
                'category' => 1,
                'author' => 0,
                'picture' => 'https://images.unsplash.com/photo-1585208798174-6cedd86e019a?w=800'
            ],
        ];

        foreach ($postData as $data) {
            $post = new Post();
            $post->setTitle($data['title']);
            $post->setContent($data['content']);
            $post->setCategory($categories[$data['category']]);
            $post->setAuthor($users[$data['author']]);
            $post->setPicture($data['picture']);
            $post->setPublishedAt(new \DateTimeImmutable('-' . rand(1, 60) . ' days'));
            $manager->persist($post);
            $posts[] = $post;
        }

        $commentTexts = [
            "Super article ! Très instructif, merci pour le partage.",
            "J'ai beaucoup appris en lisant cet article. Bravo !",
            "Excellente analyse, j'attends la suite avec impatience.",
            "Merci pour ces conseils, je vais les mettre en pratique.",
            "Article très complet et bien documenté.",
            "Je partage cet article avec mes collègues !",
            "Vraiment intéressant, continuez comme ça !",
            "J'aurais aimé plus de détails sur certains points.",
            "Parfait pour les débutants comme moi.",
            "Une lecture enrichissante, merci !",
        ];

        foreach ($posts as $post) {
            $numComments = rand(2, 5);
            for ($i = 0; $i < $numComments; $i++) {
                $comment = new Comment();
                $comment->setContent($commentTexts[array_rand($commentTexts)]);
                $comment->setPost($post);
                $comment->setAuthor($users[array_rand($users)]);
                $comment->setCreatedAt(new \DateTimeImmutable('-' . rand(1, 30) . ' days'));
                
                $statuses = [CommentStatus::APPROVED, CommentStatus::APPROVED, CommentStatus::APPROVED, CommentStatus::PENDING];
                $comment->setStatus($statuses[array_rand($statuses)]);
                
                $manager->persist($comment);
            }
        }

        $collabRequest1 = new CollaborationRequest();
        $collabRequest1->setPost($posts[0]);
        $collabRequest1->setCollaborator($users[2]);
        $collabRequest1->setStatus(CollaborationStatus::PENDING);
        $manager->persist($collabRequest1);

        $collabRequest2 = new CollaborationRequest();
        $collabRequest2->setPost($posts[0]);
        $collabRequest2->setCollaborator($users[3]);
        $collabRequest2->setStatus(CollaborationStatus::ACCEPTED);
        $collabRequest2->setRespondedAt(new \DateTimeImmutable('-5 days'));
        $manager->persist($collabRequest2);

        $collabRequest3 = new CollaborationRequest();
        $collabRequest3->setPost($posts[2]);
        $collabRequest3->setCollaborator($users[4]);
        $collabRequest3->setStatus(CollaborationStatus::PENDING);
        $manager->persist($collabRequest3);

        $collabRequest4 = new CollaborationRequest();
        $collabRequest4->setPost($posts[3]);
        $collabRequest4->setCollaborator($users[5]);
        $collabRequest4->setStatus(CollaborationStatus::REJECTED);
        $collabRequest4->setRespondedAt(new \DateTimeImmutable('-3 days'));
        $manager->persist($collabRequest4);

        $manager->flush();
    }
}
