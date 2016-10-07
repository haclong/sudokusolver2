<?php

namespace AppBundle\Service;

use AppBundle\Event\SetTileEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Description of SetTileService
 *
 * @author haclong
 */
class SetTileService {
    protected $dispatcher ;
    protected $setTileEvent ;
    
    public function __construct(EventDispatcherInterface $eventDispatcher, SetTileEvent $setTileEvent)
    {
        $this->dispatcher = $eventDispatcher ;
        $this->setTileEvent = $setTileEvent ;
    }
}
