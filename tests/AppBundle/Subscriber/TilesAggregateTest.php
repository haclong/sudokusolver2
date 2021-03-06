<?php

namespace Tests\AppBundle\Subscriber;

use AppBundle\Subscriber\TilesAggregate;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Description of TilesAggregateTest
 *
 * @author haclong
 */
class TilesAggregateTest extends \PHPUnit_Framework_TestCase {
    protected $session ;
    protected $tiles ;
    
    protected function setUp()
    {
        $this->tiles = $this->getMockBuilder('AppBundle\Entity\Tiles')
                            ->disableOriginalConstructor()
                            ->getMock() ;

        $this->session = $this->getMockBuilder('AppBundle\Persistence\TilesSession')
                              ->disableOriginalConstructor()
                              ->getMock() ;
    }
    
    protected function tearDown()
    {
        $this->session = null ;
        $this->tiles = null ;
    }

    public function testSetGameSubscriber()
    {
        $result = $this->commonEventSubscriber('SetGameEvent', 'onSetGame') ;
        $this->assertTrue($result) ;
    }

    public function testOnSetGame()
    {
        $event = $this->getMockBuilder('AppBundle\Event\SetGameEvent')
                                    ->disableOriginalConstructor()
                                    ->getMock() ;
        $event->method('getEntity')
              ->with('tilesentity')
              ->willReturn($this->tiles) ;
        
        $this->tiles->expects($this->once())
                ->method('reset') ;
        $this->session->expects($this->once())
                ->method('setTiles') ;
        
        $tilesAggregate = new TilesAggregate($this->session) ;
        $tilesAggregate->onSetGame($event) ;
    }

    public function testInitGameSubscriber()
    {
        $result = $this->commonEventSubscriber('InitGameEvent', 'onInitGame') ;
        $this->assertTrue($result) ;
    }

    public function testOnInitGame()
    {
        $size = $this->getMockBuilder('AppBundle\Entity\Event\GridSize')
                        ->disableOriginalConstructor()
                        ->getMock() ;
        $size->method('get')
                ->willReturn(9) ;
        $event = $this->getMockBuilder('AppBundle\Event\InitGameEvent')
                                    ->setConstructorArgs(array($size))
                                    ->getMock() ;
        $event->method('getGridSize')
                ->willReturn($size) ;
        
        $this->session->method('getTiles')
                ->willReturn($this->tiles) ;
        $this->session->expects($this->once())
                ->method('setTiles') ;
        $this->tiles->expects($this->once())
                ->method('reset') ;
        $this->tiles->expects($this->once())
                ->method('init')
                ->with($this->equalTo($size->get())) ;
        
        $tilesAggregate = new TilesAggregate($this->session) ;
        $tilesAggregate->onInitGame($event) ;
    }
    
    public function testReloadGameSubscriber()
    {
        $result = $this->commonEventSubscriber('ReloadGameEvent', 'onReloadGame') ;
        $this->assertTrue($result) ;
    }
    
    public function testOnReloadGame()
    {
        $grid = $this->getMockBuilder('AppBundle\Entity\Grid')
                        ->getMock() ;
        $event = $this->getMockBuilder('AppBundle\Event\ReloadGameEvent')
                                    ->disableOriginalConstructor()
                                    ->getMock() ;
        $event->method('getGrid')
              ->willReturn($grid) ;
        $this->tiles->expects($this->once())
                ->method('reload')
                ->with($this->equalTo($grid)) ;
        $this->session->method('getTiles')
                ->willReturn($this->tiles) ;
        $this->session->expects($this->once())
                ->method('setTiles') ;
        
        $tilesAggregate = new TilesAggregate($this->session) ;
        $tilesAggregate->onReloadGame($event) ;
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
        
        $this->tiles->expects($this->once())
                ->method('reset') ;
        $this->session->method('getTiles')
                ->willReturn($this->tiles) ;
        $this->session->expects($this->once())
                ->method('setTiles') ;
        $this->tiles->expects($this->once())
                ->method('init') ;
        
        $tilesAggregate = new TilesAggregate($this->session) ;
        $tilesAggregate->onResetGame($event) ;
    }

    public function testValidatedTileSubscriber()
    {
        $result = $this->commonEventSubscriber('ValidateTileSetEvent', 'onValidatedTile') ;
        $this->assertTrue($result) ;
    }
    
    public function testOnValidatedTile()
    {
        $event = $this->getMockBuilder('AppBundle\Event\ValidateTileSetEvent')
                      ->disableOriginalConstructor()
                      ->getMock() ;
        $tileset = $this->getMockBuilder('AppBundle\Entity\Event\TileSet')
                        ->getMock() ;
        $tileset->method('getRow')->willReturn(3) ;
        $tileset->method('getCol')->willReturn(3) ;
        $tileset->method('getValue')->willReturn(3) ;
        $event->expects($this->once())
                ->method('getTile')
                ->willReturn($tileset) ;
        $this->session->method('getTiles')
                ->willReturn($this->tiles) ;
        $this->session->expects($this->once())
                ->method('setTiles') ;
        $this->tiles->expects($this->once())
                ->method('set') ;
        
        $tilesAggregate = new TilesAggregate($this->session) ;
        $tilesAggregate->onValidatedTile($event) ;
    }

    public function testDeduceTileSubscriber()
    {
        $result = $this->commonEventSubscriber('DeduceTileEvent', 'onDeduceTile') ;
        $this->assertTrue($result) ;
    }
    
    public function testOnDeduceTile()
    {
        $event = $this->getMockBuilder('AppBundle\Event\DeduceTileEvent')
                      ->disableOriginalConstructor()
                      ->getMock() ;
        $tilelp = $this->getMockBuilder('AppBundle\Entity\Event\TileLastPossibility')
                        ->getMock() ;
        $event->expects($this->once())
                ->method('getTile')
                ->willReturn($tilelp) ;
        $this->session->method('getTiles')
                ->willReturn($this->tiles) ;
        $this->session->expects($this->once())
                ->method('setTiles') ;
        $this->tiles->expects($this->once())
                ->method('priorizeTileToSolve')
                ->with($tilelp) ;
        
        $tilesAggregate = new TilesAggregate($this->session) ;
        $tilesAggregate->onDeduceTile($event) ;
    }
   
    protected function commonEventSubscriber($eventName, $method)
    {
        $dispatcher = new EventDispatcher() ;
        $event = $this->getMockBuilder('AppBundle\Event\\'.$eventName)
                                    ->disableOriginalConstructor()
                                    ->getMock() ;
        
        $subscriber = $this->getMockBuilder('AppBundle\Subscriber\TilesAggregate')
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
