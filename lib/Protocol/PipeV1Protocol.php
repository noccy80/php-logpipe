<?php

namespace NoccyLabs\LogPipe\Protocol;

use NoccyLabs\LogPipe\Serializer\SerializerInterface;
use NoccyLabs\LogPipe\Message\MessageInterface;

class PipeV1Protocol implements ProtocolInterface
{
    protected $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getTag()
    {
        return 1;
    }
    
    public function pack(MessageInterface $message)
    {
        try {
            $msg = $this->serializer->serialize($message);
            $header = pack("CVV", $this->getTag(), strlen($msg), crc32($msg));
        } catch (\Exception $e) { }
        return $header.$msg;
    }

    public function unpack(&$buffer)
    {
        if (strlen($buffer) > 9) {
            $pkgheader = substr($buffer, 0, 9);
            $header = unpack("Ctag/Vsize/Vcrc32", $pkgheader);
            if ((strlen($buffer) < $header['size'] + 9)) {
                return NULL;
            }
            $data = substr($buffer, 9, $header['size']);
            $buffer = substr($buffer, $header['size']+9);
            if (crc32($data) != $header['crc32']) {
                echo("Warning: Message with invalid crc32 encountered.\n");
                $buffer = null;
                return NULL;
            }
            if ($header['tag'] == $this->getTag()) {
                $rcv = $this->serializer->unserialize($data);
                return $rcv;
            }
            echo("Warning: PipeV1Protocol can't handle protocol V{$tag}\n");
            return NULL;
        }
    }
}
