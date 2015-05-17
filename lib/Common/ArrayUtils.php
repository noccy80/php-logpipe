<?php

namespace NoccyLabs\LogPipe\Common;

class ArrayUtils
{
    public static function sanitize(&$data)
    {
        array_walk_recursive($data, function (&$item, $key) {
            if ($item instanceof \DateTime) {
                $item = $item->format('U');
            }
            try {
                $ser = serialize($item);
            } catch (\Exception $e) {
                $item = get_class($item)."<".spl_object_hash($item).">";
            }
        });

        return $data;

    }
}
