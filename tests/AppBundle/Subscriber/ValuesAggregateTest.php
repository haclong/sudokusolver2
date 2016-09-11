<?php

namespace Tests\AppBundle\Subscriber;

use AppBundle\Subscriber\ValuesAggregate;
use AppBundle\Utils\SudokuSession;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Description of ValuesAggregateTest
 *
 * @author haclong
 */
class ValuesAggregateTest extends \PHPUnit_Framework_TestCase
{
    protected $dispatcher ;
    protected $session ;
    protected $values ;

    protected function setUp()
    {
        $mockSessionStorage = new MockArraySessionStorage() ;
        $this->sess = new Session($mockSessionStorage) ;
        $this->values = $this->getMockBuilder('AppBundle\Entity\Values')
                     ->disableOriginalConstructor()
                     ->setMethods(array('setGridSize', 'reset', 'add'))
                     ->getMock() ;
        $grid = $this->getMockBuilder('AppBundle\Entity\Grid')
                     ->disableOriginalConstructor()
                     ->getMock() ;
        $tiles = $this->getMockBuilder('AppBundle\Entity\Tiles')
                     ->disableOriginalConstructor()
                     ->getMock() ;
        $this->session = new SudokuSession($this->sess, $grid, $this->values, $tiles) ;
        $this->session->setValues($this->values) ;
//        $this->service = $this->getMockBuilder('AppBundle\Service\SudokuSessionService')
//                              //->setConstructorArgs(array($trueSession))
//                              //->disableOriginalConstructor()
//                              ->getMock() ;
//        $this->service->method('setSession')
//                    ->with($this->equalTo($trueSession))
//                    ->will($this->returnSelf());
//        $this->service->method('getGridFromSession')
//                    ->willReturn($this->grid) ;
    }
    
    protected function tearDown()
    {
//        $this->dispatcher = null ;
        $this->session = null ;
        $this->values = null ;
    }

    public function testLoadGameSubscriber()
    {
        $result = $this->commonEventSubscriber('LoadGameEvent', 'onLoadGame') ;
        $this->assertTrue($result) ;
    }
    
    public function testOnLoadGame()
    {
        $array = array() ;
        $array[0][2] = 2 ;
        $array[0][5] = 9 ;
        $array[0][6] = 1 ;
        $array[0][8] = 6 ;
        $array[1][0] = 3 ;
        $array[1][2] = 5 ;
        $array[1][4] = 4 ;
        $array[1][6] = 2 ;
        $array[2][1] = 7 ;
        $array[2][2] = 9 ;
        $array[2][3] = 2 ;
        $array[2][4] = 6 ;
        $array[3][1] = 5 ;
        $array[3][7] = 1 ;
        $array[3][8] = 9 ;
        $array[4][1] = 2 ;
        $array[4][2] = 1 ;
        $array[4][3] = 9 ;
        $array[4][4] = 7 ;
        $array[4][5] = 5 ;
        $array[4][6] = 8 ;
        $array[4][7] = 4 ;
        $array[5][0] = 9 ;
        $array[5][1] = 8 ;
        $array[5][7] = 2 ;
        $array[6][4] = 9 ;
        $array[6][5] = 1 ;
        $array[6][6] = 7 ;
        $array[6][7] = 6 ;
        $array[7][2] = 4 ;
        $array[7][4] = 5 ;
        $array[7][6] = 3 ;
        $array[7][8] = 1 ;
        $array[8][0] = 7 ;
        $array[8][2] = 6 ;
        $array[8][3] = 3 ;
        $array[8][6] = 9 ;

        $tiles = $this->getMockBuilder('AppBundle\Entity\Event\TilesLoaded')
                        ->disableOriginalConstructor()
                        ->getMock() ;
        $tiles->method('getSize')
                ->willReturn(9) ;
        $tiles->method('getTiles')
                ->willReturn($array) ;
        $event = $this->getMockBuilder('AppBundle\Event\LoadGameEvent')
                                    ->setConstructorArgs(array($tiles))
                                    ->getMock() ;
        
        $event->expects($this->exactly(1))
              ->method('getTiles')
              ->will($this->returnValue($tiles));
        
        $valuesAggregate = new ValuesAggregate($this->session) ;
        $valuesAggregate->onLoadGame($event) ;
    }

    public function testResetGameSubscriber()
    {
        $result = $this->commonEventSubscriber('ResetGameEvent', 'onResetGame') ;
        $this->assertTrue($result) ;
    }
    
    public function testOnResetGame()
    {
        $event = $this->getMockBuilder('AppBundle\Event\ResetGameEvent')
                                    ->getMock() ;
        
        $this->values->expects($this->once())
                ->method('reset') ;
        
        $valuesAggregate = new ValuesAggregate($this->session) ;
        $valuesAggregate->onResetGame($event) ;
    }
   
    protected function commonEventSubscriber($eventName, $method)
    {
        $dispatcher = new EventDispatcher() ;
        $event = $this->getMockBuilder('AppBundle\Event\\'.$eventName)
                                    ->disableOriginalConstructor()
                                    ->getMock() ;
        
        $subscriber = $this->getMockBuilder('AppBundle\Subscriber\ValuesAggregate')
                                   ->disableOriginalConstructor()
                                   ->setMethods(array($method))
                                   ->getMock() ;

        $dispatcher->addSubscriber($subscriber) ;

        $subscriber->expects($this->once())
                   ->method($method)
                   ->with($this->equalTo($event));
        $dispatcher->dispatch($event::NAME, $event) ;
        $listeners = $dispatcher->getListeners($event::NAME) ;
        $result = false ;
        foreach($listeners as $listener)
        {
            if($listener[0] instanceof $subscriber) {
                $result = true ;
                continue ;
            }
        }
        return $result ;
    }
    
}
