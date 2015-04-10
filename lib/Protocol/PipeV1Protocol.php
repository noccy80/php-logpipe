<?php

namespace NoccyLabs\LogPipe\Protocol;

use NoccyLabs\LogPipe\Serializer\SerializerInterface;
use NoccyLabs\LogPipe\Message\MessageInterface;

/**
 * Class PipeV1Protocol
 *
 * @package NoccyLabs\LogPipe\Protocol
 */
class PipeV1Protocol implements ProtocolInterface
{
    /**
     * The format used to pack() the header structure
     */
    const PACK_FORMAT = "CCCCVVV"; // CVV

    /**
     * The format used to unpack() the header structure
     */
    const UNPACK_FORMAT = "Cmark/Cversion/Cformat/Cres1/Vsize/Vcrc32/Vres2"; // Ctag/Vsize/Vcrc32

    /**
     * The size of the packed header
     */
    const HEADER_SIZE = 16;

    /**
     * The version of the protocol handled by this implementation
     */
    const PROTOCOL_VERSION = 1;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param MessageInterface $message
     * @return string
     */
    public function pack(MessageInterface $message)
    {
        $version = self::PROTOCOL_VERSION;
        $format = $this->serializer->getTag();

        try {
            $msg = $this->serializer->serialize($message);
            $header = pack(
                self::PACK_FORMAT,
                0xFF,
                $version,
                $format,
                0,
                strlen($msg),
                crc32($msg),
                0
            );
        } catch (\Exception $e) { }
        return $header.$msg;
    }

    /**
     * @param $buffer
     * @return mixed|null
     */
    public function unpack(&$buffer)
    {
        if (strlen($buffer) < self::HEADER_SIZE) {
            return NULL;
        }

        $pkgheader = substr($buffer, 0, self::HEADER_SIZE);
        $header = unpack(self::UNPACK_FORMAT, $pkgheader);
        if ((strlen($buffer) < $header['size'] + self::HEADER_SIZE)) {
            return NULL;
        }

        $data = substr($buffer, self::HEADER_SIZE, $header['size']);
        $buffer = substr($buffer, $header['size'] + self::HEADER_SIZE);

        // Match the protocol version
        if (($header['version'] & 0x3F) != self::PROTOCOL_VERSION) {
            return NULL;
        }

        // Verify the protocol checksum
        if (crc32($data) != $header['crc32']) {
            $buffer = null;
            return NULL;
        }

        $rcv = $this->serializer->unserialize($data);
        return $rcv;
    }
}
