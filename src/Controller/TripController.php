<?php


namespace App\Controller;


use App\Entity\Location;
use App\Entity\State;
use App\Entity\User;
use App\Entity\Trip;
use App\Form\TripType;
use App\Security\Voter\TripVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
/**
 * Class TripController
 * @package App\Controller
 * @IsGranted("ROLE_USER")
 */
class TripController extends AbstractController
{

    /**
     * @Route("/sorties/detail/{id}", name="trip_detail")
     */
    public function detail($id)
    {
        $tripRepo = $this->getDoctrine()->getRepository(Trip::class);
        $trip = $tripRepo->find($id);

        /** @var User $connectedUser */
        $connectedUser = $this->getUser();

        if (!$trip) {
            throw $this->createNotFoundException("Cette sortie n'existe pas !");
        }
        $state = $trip->getState();
        $condition = [
            'modification' => false,
            'creer' => false,
            'inscription' => false,
            'desincription' => false
        ];
        $autorisationModif = false;
        if ($connectedUser == $trip->getOrganizer() || in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $autorisationModif = true;
        }

        if (($state->getWording() == 'Ouverte' || $state->getWording() == 'Clôturée') && $autorisationModif) {
            $condition['modification'] = true;
        }
        if ($state->getWording() == 'Créée' && $autorisationModif) {
            $condition['modification'] = true;
            $condition['creer'] = true;
        }

        if (($trip->getState()->getWording() === 'Ouverte') && ($trip->getDateBeginning() > new \DateTime()) &&
            (count($trip->getUsers()) < $trip->getRegistrationMax()) && ($connectedUser !== $trip->getOrganizer())) {
            $condition['inscription'] = true;
        }
        if (($connectedUser !== $trip->getOrganizer()) && ($trip->getState()->getWording() === 'Ouverte' || $trip->getState()->getWording() === 'Clôturée')) {
            $condition['desincription'] = true;
        }

        return $this->render('trip/detail.html.twig', [
            'trip' => $trip,
            'condition' => $condition
        ]);
    }

    /**
     * @Route("/sorties", name="trip_list")
     */
    public function list()
    {
        $tripRepo = $this->getDoctrine()->getRepository(Trip::class);

        $tabSearch = [
            'crit' => 'all',
            'userId' => $this->getUser()->getId()
        ];
        $trips = $tripRepo->findListTrips($tabSearch);

        return $this->render('trip/list.html.twig', [
            "trips" => $trips
        ]);
    }
    /**
     * en mode ajax
     * @Route("/tripSearch", name="trip_search")
     */
    public function search(Request $request)
    {
        $search = $request->query->get('search');
        $tripRepo = $this->getDoctrine()->getRepository(Trip::class);

        $tabSearch = [
            'crit' => $search,
            'userId' => $this->getUser()->getId()
        ];
        $trips = $tripRepo->findListTrips($tabSearch);

        return $this->render('trip/trip_list.html.twig', [
            "trips" => $trips
        ]);
    }

    /**
     * @Route("/sorties/supprimer/{id}", name="trip_delete")
     */
    public function delete(Trip $trip)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($trip);
        //on exécute
        $em->flush();

        $this->addFlash("success", "Suppression réussi");

        return $this->redirectToRoute('trip_list');
    }

    /**
     * @Route("/sorties/ajouter", name="trip_add")
     */
    public function add(Request $request)
    {

        $trip = new Trip();

        $form = $this->createForm(TripType::class, $trip);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $repo = $this->getDoctrine()->getRepository(Location::class);
            $location = $repo->find($request->request->all()['trip']['location']['idLocation']);
            $trip->setLocation($location);

            if ($trip->getDateBeginning() < $trip->getRegistrationDeadline()) {
                $this->addFlash('danger', 'La date limite d\'inscription ne peut pas être après la date de sortie');
                return $this->render('trip/add.html.twig', [
                    "tripForm" => $form->createView()
                ]);
            }
            if ($trip->getDateBeginning() < new \DateTime()) {
                $this->addFlash('danger', 'La date de la sortie ne peut pas être dans le passé...');
                return $this->render('trip/add.html.twig', [
                    "tripForm" => $form->createView()
                ]);
            }
            /** @var User $connectedUser */
            $connectedUser = $this->getUser();
            $trip->setOrganizer($connectedUser);

            $stateRepo = $this->getDoctrine()->getRepository(State::class);
            $state = $stateRepo->findOneBy(array('wording' => 'Créée'));
            $trip->setState($state);


            $em = $this->getDoctrine()->getManager();
            $em->persist($trip);
            $em->flush();

            $this->addFlash('success', 'Sortie ajoutée !');
            return $this->redirectToRoute('trip_detail', ['id' => $trip->getId()]);
        }

        return $this->render('trip/add.html.twig', [
            "tripForm" => $form->createView()
        ]);
    }


    /**
     * @Route("/sorties/modifier/{id}", name="trip_edit")
     */
    public function edit(Trip $trip, Request $request)
    {

        $form = $this->createForm(TripType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($trip->getDateBeginning() < $trip->getRegistrationDeadline()) {
                $this->addFlash('danger', 'La date limite d\'inscription ne peut pas être après la date de sortie');
                return $this->render('trip/add.html.twig', [
                    "tripForm" => $form->createView()
                ]);
            }
            if ($trip->getDateBeginning() < new \DateTime()) {
                $this->addFlash('danger', 'La date de la sortie ne peut pas être dans le passé...');
                return $this->render('trip/add.html.twig', [
                    "tripForm" => $form->createView()
                ]);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($trip);
            $em->flush();

            $this->addFlash('success', 'Sortie modifiée !');
            return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);
        }

        return $this->render('trip/edit.html.twig', [
            "tripForm" => $form->createView()
        ]);
    }

    /**
     * @Route("/sorties/ouvrir/{id}", name="trip_open")
     */
    public function open(Trip $trip)
    {
        if ($trip->getState()->getWording() != 'Créée') {
            $this->addFlash('warning', 'La sortie ne peut pas être ouverte');
            return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);
        }
        $stateRepo = $this->getDoctrine()->getRepository(State::class);
        $state = $stateRepo->findOneBy(array('wording' => 'Ouverte'));

        $trip->setState($state);

        $em = $this->getDoctrine()->getManager();
        $em->persist($trip);
        $em->flush();

        $this->addFlash('success', 'Sortie Ouverte !');
        return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);


    }

    /**
     * @Route("/sorties/annuler/{id}", name="trip_cancel")
     */
    public function cancel(Trip $trip)
    {
        $stateRepo = $this->getDoctrine()->getRepository(State::class);
        $state = $stateRepo->findOneBy(array('wording' => 'Annulée'));

        $trip->setState($state);

        $em = $this->getDoctrine()->getManager();
        $em->persist($trip);
        $em->flush();

        $this->addFlash('warning', 'Sortie annulé ... :(');
        return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);


    }

    /**
     * @Route("/sorties/inscription/{id}", name="trip_registration")
     */
    public function registration(Trip $trip)
    {
        /** @var User $connectedUser */
        $connectedUser = $this->getUser();
        if (($trip->getState()->getWording() !== 'Ouverte') && $trip->getDateBeginning() < new \DateTime()) {
            $this->addFlash('warning', 'Cette sortie n\'est plus ouverte aux inscriptions');
            return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);
        } elseif (count($trip->getUsers()) >= $trip->getRegistrationMax()) {
            $stateRepo = $this->getDoctrine()->getRepository(State::class);
            $state = $stateRepo->findOneBy(array('wording' => 'Clôturée'));
            $trip->setState($state);
            $this->addFlash('warning', 'Le nombre d\'inscriptions maximum a été atteint');
            return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);
        } elseif ($connectedUser == $trip->getOrganizer()) {
            $this->addFlash('warning', 'Vous est l\'organisateur de cette sortie, vous n\'avez pas besoin de participer!');
            return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);
        }
        $trip->addUser($connectedUser);
        $em = $this->getDoctrine()->getManager();
        $em->persist($trip);
        $em->flush();

        $this->addFlash('success', 'Vous vous êtes bien inscrist');
        return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);
    }

    /**
     * @Route("/sorties/desinscription/{id}", name="trip_unsubscribe")
     */
    public function unsubscribe(Trip $trip)
    {
        /** @var User $connectedUser */
        $connectedUser = $this->getUser();
        if ($connectedUser == $trip->getOrganizer()) {
            $this->addFlash('warning', 'Vous est l\'organisateur de cette sortie, vous ne pouvez pas vous désincrire.');
            return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);
        }
        if (($trip->getState()->getWording() != 'Ouverte') && ($trip->getState()->getWording() != 'Clôturée')) {
            $this->addFlash('warning', 'Vous ne pouvez plus vous désincrire.');
            return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);
        }
        $trip->removeUser($connectedUser);
        $em = $this->getDoctrine()->getManager();
        $em->persist($trip);
        $em->flush();

        $this->addFlash('success', 'Vous vous êtes désinscrit');
        return $this->redirectToRoute('trip_detail', ["id" => $trip->getId()]);
    }
}