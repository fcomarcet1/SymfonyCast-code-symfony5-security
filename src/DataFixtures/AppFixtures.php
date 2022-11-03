<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Tag;
use App\Entity\User;
use App\Factory\AnswerFactory;
use App\Factory\QuestionFactory;
use App\Factory\QuestionTagFactory;
use App\Factory\TagFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        // TODO inject services if required (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services)
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        TagFactory::createMany(100);

        $questions = QuestionFactory::createMany(20);

        QuestionTagFactory::createMany(100, function() {
            return [
                'tag' => TagFactory::random(),
                'question' => QuestionFactory::random(),
            ];
        });

        QuestionFactory::new()
            ->unpublished()
            ->many(5)
            ->create()
        ;

        AnswerFactory::createMany(100, function() use ($questions) {
            return [
                'question' => $questions[array_rand($questions)]
            ];
        });

        AnswerFactory::new(function() use ($questions) {
            return [
                'question' => $questions[array_rand($questions)]
            ];
        })->needsApproval()->many(20)->create();

        $manager->flush();

        UserFactory::createOne([
            'email' => 'admin@admin.com',
            'roles' => ['ROLE_ADMIN'],
            'password' => $this->passwordHasher->hashPassword(new User(), 'admin'),
        ]);

        UserFactory::createMany(10);
    }
}
