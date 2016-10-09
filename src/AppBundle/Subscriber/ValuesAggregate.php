<?php

namespace AppBundle\Subscriber;

use AppBundle\Entity\Values;
use AppBundle\Event\InitGameEvent;
use AppBundle\Event\ResetGameEvent;
use AppBundle\Event\SetGameEvent;
use AppBundle\Persistence\ValuesSession;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Description of ValueAggregate
 *
 * @author haclong
 */
class ValuesAggregate implements EventSubscriberInterface {
    protected $session ;

    public function __construct(ValuesSession $sessionService) {
        $this->session = $sessionService ;
    }

    public static function getSubscribedEvents() {
        return array(
            SetGameEvent::NAME => 'onSetGame',
            InitGameEvent::NAME => 'onInitGame', 
//            LoadGameEvent::NAME => array('onLoadGame', 2048),
            ResetGameEvent::NAME => 'onResetGame',
        ) ;
    }

    protected function getValuesFromSession() {
        return $this->session->getValues() ;
    }
    protected function storeValues(Values $values) {
        $this->session->setValues($values) ;
    }
    
    public function onSetGame(SetGameEvent $event) {
        $values = $event->getEntity('valuesentity') ;
        $values->reset() ;
        $this->storeValues($values) ;
    }
    
    public function onInitGame(InitGameEvent $event) {
        $values = $this->getValuesFromSession() ;
        $values->reset() ;
        $values->init($event->getGridSize()->get()) ;
        $this->storeValues($values) ;
    }
//    
//    public function onLoadGame(LoadGameEvent $event) {
//        $values = $this->getValuesFromSession() ;
//        $tiles = $event->getTiles() ;
//        
//        $values->init($tiles->getSize()) ;
//        foreach($tiles->getTiles() as $row) 
//        {
//            foreach($row as $value)
//            {
//                $values->add($value) ;
//            }
//        }
////        var_dump($values) ;
//        $this->storeValues($values) ;
//    }

    public function onResetGame(ResetGameEvent $event) {
        $values = $this->getValuesFromSession() ;
        $values->reset() ;
        $this->storeValues($values) ;
    }
}
