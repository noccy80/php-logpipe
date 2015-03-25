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
            $header = pack("vV", strlen($msg), crc32($msg));
        } catch (\Exception $e) { }
        return $header.$msg;
    }

    public function unpack(&$buffer)
    {
        if (strlen($buffer) > 6) {
            $header = unpack("vsize/Vcrc32", substr($buffer, 0, 6));
            if ((strlen($buffer) < $header['size'] - 6)) {
                return NULL;
            }
            $data = substr($buffer, 6, $header['size']);
            $buffer = substr($buffer, $header['size']+6);
            if (crc32($data) != $header['crc32']) {
                $buffer = null;
                error_log("Warning: Message with invalid crc32 encountered.");
                return NULL;
            }

            $rcv = $this->serializer->unserialize($data);

            return $rcv;
        }
    }
}
