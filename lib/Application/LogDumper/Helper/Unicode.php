<?php

namespace NoccyLabs\LogPipe\Application\LogDumper\Helper;

class Unicode
{
    
    public static function char($index)
    {
        return html_entity_decode(sprintf("&#%d;", $index), ENT_COMPAT, "utf-8");
    }
    
}
