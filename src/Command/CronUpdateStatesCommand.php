<?php

namespace App\Command;

use App\Entity\State;
use App\Entity\Trip;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CronUpdateStatesCommand extends Command
{
    protected static $defaultName = 'CronUpdateStates';
    private $container;

    public function __construct(string $name = null, ContainerInterface $container)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setDescription('Command to call for update states of trips every minute');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $currentDate = time();
        $repoTrips = $this->container->get('doctrine')->getRepository(Trip::class);
        $statesRepo = $this->container->get('doctrine')->getRepository(State::class);
        $states = $statesRepo->findAll();
        $trips = $repoTrips->findAll();
        $em = $this->container->get('doctrine')->getManager();
        /*** @var $trip Trip */
        foreach ($trips as $trip) {
            if ($trip->getRegistrationDeadline()->getTimestamp() <= $currentDate) {
                $trip->setState($this->getState($states,'Clôturée'));
                $em->persist($trip);
            }
            if ($trip->getDateBeginning()->getTimestamp() <= $currentDate && ($trip->getDateBeginning()->getTimestamp() + ($trip->getDuration() * 60)) >= $currentDate) {
                $trip->setState($this->getState($states,'En cours'));
                $em->persist($trip);
            }
            if (($trip->getDateBeginning()->getTimestamp() + ($trip->getDuration() * 60)) < $currentDate) {
                $trip->setState($this->getState($states,'Passée'));
                $em->persist($trip);
            }
            if (($trip->getDateBeginning()->getTimestamp() + ($trip->getDuration() * 60) + 2592000) < $currentDate && ($trip->getState()->getWording() === 'Passée' || $trip->getState()->getWording() === 'Annulée')) {
                $em->remove($trip);
            }
        }
        $em->flush();
        $io->success('La base de données a bien été mise à jours.');
        return 0;
    }

    protected function getState($states, $wording) {
        /*** @var $state State */
        foreach ($states as $state) {
            if($state->getWording() === $wording) {
                return $state;
            }
        }
    }
}
