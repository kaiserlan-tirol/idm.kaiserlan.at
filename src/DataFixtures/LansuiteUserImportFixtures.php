<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Helper\LansuiteImporter;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class LansuiteUserImportFixtures extends Fixture implements FixtureGroupInterface
{
    // -------- Usage -------- //
    // php bin/console doctrine:fixtures:load --group=lansuite --append
    // 
    // -------- Requirements -------- //
    // Lansuite User Exports
    // ls_user.php
    
    
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public static function getGroups(): array
    {
        return ['lansuite'];
    }

    public function load(ObjectManager $manager): void
    {
        $filteredUsers = LansuiteImporter::getFilteredUsersFromExports(dirname(__DIR__) . '/../../exports-for-migration/');
        
        foreach ($filteredUsers as $id => $d) {
            if ($d['username'] == "supl1an") {
                continue;
            }
            $userExists = $this->userRepository->findOneByCi(['email' => $d['email']]);
            if ($userExists) {
                echo "User with email {$d['email']}, {$d['username']} already exists, skipping\n";
                continue;
            } else {
                $userExists = $this->userRepository->findOneByCi(['nickname' => $d['username']]);
                if ($userExists) {
                    echo "User with nickname {$d['username']} already exists, skipping\n";
                    continue;
                }
            }
            $isAdmin = $d['type'] == 3;
            $uuid = Uuid::fromInteger(12300 . strval($id));
            // $user = $isAdmin ? new UserAdmin($uuid) : new UserGamer($uuid);
            $user = new User();
            if ($isAdmin) {
                $user->setIsSuperadmin(true);
            }
            $user->setNickname($d['username']);
            $user->setEmail($d['email']);
            $user->setFirstname($d['firstname']);
            $user->setSurname($d['name']);
            $user->setStatus(1);
            $user->setEmailConfirmed(true);
            $user->setInfoMails(true);
            $user->setPassword($d['password']);
            $user->setPhone($d['handy'] || $d['telefon'] || null);
            $user->setPersonalDataLocked(false);
            $user->setPersonalDataConfirmed(false);

            $user->setGender($d['sex'] == 2 ? 'f' : 'm');
            $this->addReference('user-ls-'.$d['userid'], $user);
            $manager->persist($user);
            $manager->flush();
            echo "Created ". ($isAdmin ? 'admin' : 'user') ." {$d['username']} ({$d['email']})\n";
        }

        echo "Finished importing users";
    }
}
