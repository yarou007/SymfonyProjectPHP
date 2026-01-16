<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $error = null;

        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email'));
            $pass  = (string) $request->request->get('password');
            $roleName = strtoupper((string) $request->request->get('role', 'ROLE_USER'));

            if (!in_array($roleName, ['ROLE_USER', 'ROLE_ADMIN'], true)) {
                $roleName = 'ROLE_USER';
            }

            if ($email === '' || $pass === '') {
                $error = 'Email and password required';
            } else {
                $exists = $em->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($exists) {
                    $error = 'Email already exists';
                } else {
                    $role = $em->getRepository(Role::class)->findOneBy(['name' => $roleName]);
                    if (!$role) {
                        return new Response(
                            'ROLE missing. Run: php bin/console app:seed-roles',
                            500
                        );
                    }

                    $user = new User();
                    $user->setEmail($email);
                    $user->addRoleEntity($role);
                    $user->setPassword($hasher->hashPassword($user, $pass));

                    $em->persist($user);
                    $em->flush();

                    return $this->redirectToRoute('app_login');
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'error' => $error
        ]);
    }
}
