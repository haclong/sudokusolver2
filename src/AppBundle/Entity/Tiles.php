<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Event\TileLastPossibility;
use AppBundle\Entity\Tiles\Tileset;
use AppBundle\Entity\Tiles\TileToSolve;

/**
 * Description of Tiles
 *
 * @author haclong
 */
class Tiles implements InitInterface, ResetInterface, ReloadInterface {
    protected $tileset ;
    protected $tile;
    protected $size ;
    protected $tilesToSolve ;

    public function __construct(Tileset $tileset, TileToSolve $tile)
    {
        $this->tileset = $tileset ;
        $this->tile = $tile ;
        $this->tilesToSolve = [] ;
    }
    
    public function init($size)
    {
        $this->setTileset($size) ;
        $this->setTilesToSolve($this->getTileset()) ;
        $this->size = $size ;
        return $this ;
    }
    public function reset() 
    {
        $this->tileset->exchangeArray(array()) ;
        $this->tilesToSolve = [] ;
        $this->size = null ;
        return $this ;
    }
    public function reload(Grid $grid)
    {
        $this->setTileset($grid->getSize()) ;
        $this->setTilesToSolve($this->getTileset()) ;
    }
    public function getSize()
    {
        return $this->size ;
    }
    public function getTileset()
    {
        return $this->tileset ;
    }
    public function getTilesToSolve()
    {
       return $this->tilesToSolve ;
    }
    public function getTile($row, $col)
    {
        return $this->tileset->offsetGet($row.'.'.$col) ;
    }
    public function set($row, $col, $value)
    {
        $this->tileset->offsetSet($row.'.'.$col, $value) ;
        $this->removeTileToSolve($row.'.'.$col) ;
    }
    public function priorizeTileToSolve(TileLastPossibility $deduceTile)
    {
        $tileId = $deduceTile->getRow() .'.'. $deduceTile->getCol() ;
        $tile = clone $this->tile ;
        $tile->setId($tileId) ;
        $tile->setValue($deduceTile->getValue()) ;
        $this->removeTileToSolve($tileId) ;
        
        array_unshift($this->tilesToSolve, $tile) ;
    }
    public function getFirstTileToSolve()
    {
        reset($this->tilesToSolve) ;
        return current($this->tilesToSolve) ;
    }
    
    protected function setTileset($size)
    {
        for($row=0; $row<$size; $row++)
        {
            for($col=0; $col<$size; $col++)
            {
                $this->tileset->offsetSet($row.'.'.$col, NULL) ;
            }
        }
        return $this ;
    }
    protected function setTilesToSolve($tileset)
    {
        $this->tilesToSolve = [] ;
        foreach($tileset as $key => $index)
        {
            $tile = clone $this->tile ;
            $tile->setId($key) ;
            $tile->setValue($index) ;
            array_push($this->tilesToSolve, $tile) ;
        }
    }
    protected function removeTileToSolve($id)
    {
        foreach($this->tilesToSolve as $key => $tile)
        {
            if($tile->getId() == $id)
            {
                unset($this->tilesToSolve[$key]) ;
                break ;
            }
        }
    }
}