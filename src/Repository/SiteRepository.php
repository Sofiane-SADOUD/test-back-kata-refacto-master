<?php

namespace Template\Repository;

use Template\Entity\Site;
use Faker;

class SiteRepository implements RepositoryInterface
{
    use \Template\Helper\SingletonTrait;

    private $url;

    /**
     * SiteRepository constructor.
     *
     */
    public function __construct()
    {
        // DO NOT MODIFY THIS METHOD
        $this->url = Faker\Factory::create()->url;
    }

    /**
     * @param int $id
     *
     * @return Site
     */
    public function getById($id)
    {
        // DO NOT MODIFY THIS METHOD
        return new Site($id, $this->url);
    }
}
