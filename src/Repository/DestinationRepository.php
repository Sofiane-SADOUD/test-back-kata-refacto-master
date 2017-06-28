<?php

namespace Template\Repository;

use Template\Entity\Destination;
use Faker;

class DestinationRepository implements Repository
{
    use \Template\Helper\SingletonTrait;

    private $country;
    private $conjunction;
    private $computerName;

    /**
     * DestinationRepository constructor.
     */
    public function __construct()
    {
        $this->country = Faker\Factory::create()->country;
        $this->conjunction = 'en';
        $this->computerName = Faker\Factory::create()->slug();
    }

    /**
     * @param int $id
     *
     * @return Destination
     */
    public function getById($id)
    {
        // DO NOT MODIFY THIS METHOD
        return new Destination(
            $id,
            $this->country,
            $this->conjunction,
            $this->computerName
        );
    }
}
