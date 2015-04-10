LogPipe Binary Protocol
=======================

Most transports use the format outlined here to encapsulate messages before sending them.
This is done by `PipeV1Protocol`.

    1 byte      Mark        Used as a synchronization byte, always 0xFF
    1 byte      Version     8 7 6 5 4 3 2 1
                            | | '--.------'
                            | |    '-------- Version (0x1=current)
                            | '------------- Compressed (1=GZ/BZ, 0=no)
                            '--------------- If compressed (1=BZ2, 0=GZ)
    1 byte      Format      Serialization used:
                              'P'  Standard PHP serializer
                              'j'  Json serializer
                              'm'  MsgPack serializer
    1 byte                  reserved
    4 bytes     Crc32       The crc32 of the payload
    4 bytes     Size        The size of the payload
    4 bytes                 reserved

    <size>      Payload     The payload data (message)

Thus, a frame serialized with json and compressed would have a header of:

        Hex:                                                Text:
        FF 41 6A 00 XX XX XX XX   YY YY YY YY 00 00 00 00   ..j.XXXX YYYY....

Where X is the payload CRC32 and Y is the payload size.


## Payload

The payload is in the case of the PHP serializer encoded as is. For the other class-agnostic
serializers, the data is generally serialized as `[ <message-class>, <message-data> ]`. The
data is extracted from the message using `getData()`, and injected into a new message using
`setData(..)`.
