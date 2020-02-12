<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Form\ResetPasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class ProfileController
 * @package App\Controller
 * @IsGranted("ROLE_USER")
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();
        $profileForm = $this->createForm(ProfileType::class, $user);
        $resetPasswordForm = $this->createForm(ResetPasswordType::class);
        $entityManager = $this->getDoctrine()->getManager();
        $profileForm->handleRequest($request);
        $resetPasswordForm->handleRequest($request);
        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été modifier avec succès.');
            return $this->redirectToRoute('profile');
        }
        if ($resetPasswordForm->isSubmitted() && $resetPasswordForm->isValid()) {
            if ($passwordEncoder->isPasswordValid($user, $resetPasswordForm->get('oldPassword')->getData())) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $resetPasswordForm->get('newPassword')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Votre mot de passe a été modifier avec succès.');
                return $this->redirectToRoute('profile');
            } else {
                return $this->render('profile/index.html.twig', [
                    'controller_name' => 'ProfileController',
                    'oldPasswordError' => 'L\'ancien mot de passe saisie est invalide',
                    'profileForm' => $profileForm->createView(),
                    'newPasswordForm' => $resetPasswordForm->createView()
                ]);
            }
        }
        return $this->render('profile/index.html.twig', [
            'profileForm' => $profileForm->createView(),
            'newPasswordForm' => $resetPasswordForm->createView()
        ]);
    }

    /**
     * @Route("/profile/{id}", requirements={"page"="\d+"}, name="user_profile")
     */
    public function profile(User $user) {
        if ($user->getId() === $this->getUser()->getId()) {
            return $this->redirectToRoute('profile');
        }
        return $this->render('profile/profile.html.twig', [
            'user' => $user
        ]);
    }

}
