<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AdministrationController
 * @package App\Controller
 * @IsGranted("ROLE_ADMIN")
 * @Route("/administration")
 */
class AdministrationController extends AbstractController
{
    /**
     * @Route("/", name="administration")
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(User::class);
        $users = $repo->findAll();
        return $this->render('administration/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/ajouter", name="administration_ajouter_utilisateur")
     */
    public function ajouter(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setActif(true);
            if ($form->get('administrator')->getData() === true) {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Le compte de ' . $user->getFirstname() . ' ' . $user->getName() . 'a été crée avec succès.');

            return $this->redirectToRoute('administration');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @Route("/supprimer/{id}", methods={"GET"}, requirements={"page"="\d+"}, name="administration_remove_user")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function remove(User $user) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'L\'utilisateur ' . $user->getFirstname() . ' ' . $user->getName() . ' a  été supprimé avec succès.');
        return $this->redirectToRoute('administration');
    }

    /**
     * @param User $user
     * @Route("/block/{id}", methods={"GET"}, requirements={"page"="\d+"}, name="administration_block_user")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function block(User $user) {
        $user->setActif(!$user->getActif());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        $msg = '';
        if ($user->getActif()) {
            $msg = 'débloqué';
        } else {
            $msg = 'bloqué';
        }
        $this->addFlash('success', 'L\'utilisateur ' . $user->getFirstname() . ' ' . $user->getName() . ' a  été ' . $msg . '  avec succès.');
        return $this->redirectToRoute('administration');
    }
}
