<?php
namespace Vivo\Service\Initializer;

use Vivo\Repository\RepositoryInterface;

/**
 * Interface for injecting Repository
 */
interface RepositoryAwareInterface
{
    public function setRepository(RepositoryInterface $repository);
}
