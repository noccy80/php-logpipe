<?php

namespace NoccyLabs\LogPipe\Application\Buffer;

use NoccyLabs\LogPipe\Common\FifoBuffer;

class FifoBufferTest extends \PhpUnit_Framework_TestCase
{
    public function setup()
    {
    }

    public function teardown()
    {
    }

    public function getFifo()
    {
        $fifo = new FifoBuffer(100);
        for ($n = 1; $n <= 100; $n++) {
            $fifo->push($n);
        }
        return $fifo;
    }

    public function testOverflowingTheBufferTruncatesIt()
    {
        $fifo = new FifoBuffer(50);
        for ($n = 1; $n < 100; $n++) {
            $fifo->push($n);
            $this->assertEquals(min(50,$n), count($fifo));
            $this->assertEquals($n, $fifo->getTotal());
        }
    }

    public function testFirstInFirstOut()
    {
        $fifo = new FifoBuffer(100);
        $data = [ "a", "b", "c", "d" ];
        foreach ($data as $d) {
            $fifo->push($d);
        }
        foreach ($data as $n=>$d) {
            $this->assertEquals($d, $fifo[$n]);
        }
    }

    public function testSlicingTheBuffer()
    {
        $fifo = $this->getFifo();
        $expect = [ 6, 7, 8, 9, 10 ];
        $slice  = $fifo->slice(5,5);
        $this->assertEquals($expect, $slice);
    }

    public function testHeadingTheBuffer()
    {
        $fifo = $this->getFifo();
        $expect = [ 1, 2, 3, 4, 5 ];
        $slice  = $fifo->head(5);
        $this->assertEquals($expect, $slice);
    }

    public function testTailingTheBuffer()
    {
        $fifo = $this->getFifo();
        $expect = [ 96, 97, 98, 99, 100 ];
        $slice  = $fifo->tail(5);
        $this->assertEquals($expect, $slice);
    }

}
