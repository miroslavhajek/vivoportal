<?php
namespace Vivo\Service\Initializer;

use Vivo\Storage\PathBuilder\PathBuilderInterface;

/**
 * Interface for injecting Path builder
 */
interface PathBuilderAwareInterface
{
    public function setPathBuilder(PathBuilderInterface $repository);
}

