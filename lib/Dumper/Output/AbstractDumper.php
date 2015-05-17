<?php

namespace NoccyLabs\LogPipe\Dumper\Output;

use NoccyLabs\LogPipe\Decoder\DecoderInterface;
use NoccyLabs\LogPipe\Message\MessageInterface;

abstract class AbstractDumper
{
    protected $decoders = [];

    /**
     *
     *
     * @param DecoderInterface $decoder The decoder to add
     * @return DecoderInterface The added decoder
     */
    public function addDecoder(DecoderInterface $decoder)
    {
        $this->decoders[] = $decoder;
        return $decoder;
    }

    /**
     *
     *
     * @return AbstractDumper
     */
    public function clearDecoders()
    {
        $this->decoders = [];
        return $this;
    }


    /**
     * Decode a message, inflating it from a collapsed string or refining it. The function returnn the
     * original message if there is no suitable decoder, and otherwise it returns the updated message
     * or null. Returning null can be done by decoders that log or filter messages or data to prevent
     * outputting it to the log/console.
     *
     * @param MessageInterface $message The message to decode
     * @return MessageInterface|null The original message, decoded message, or null (for discard)
     */
    protected function decode(MessageInterface $message)
    {
        foreach ($this->decoders as $decoder) {
            if ($decoder->canDecode($message)) {
                return $decoder->decode($message);
            }
        }
        return $message;
    }

    /**
     * Dump a message. Should call on $this->decode().
     *
     * @param MessageInterface $message The message to decode
     */
    abstract public function dump(MessageInterface $message);

}
