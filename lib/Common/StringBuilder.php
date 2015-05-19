<?php


namespace NoccyLabs\LogPipe\Common;


/**
 * Memory-efficient string builder that uses php://memory to manipulate string data.
 *
 * @package NoccyLabs\LogPipe\Common
 */
class StringBuilder {

    private $default_append_options = array(
        "wrap"              => false,
        "markup"            => false,
        "indent"            => false,
        "newline"           => false
    );

    /**
     * @var resource
     */
    protected $fh;

    /**
     * Constructor
     *
     */
    public function __construct(array $default_options=[])
    {
        $this->fh = fopen("php://memory", "wb");
        if (!$this->fh) {
            throw new \RuntimeException("Unable to open php://memory for stringbuilder");
        }
        $this->default_append_options = array_merge($this->default_append_options, $default_options);
    }

    /**
     * Destructor
     *
     */
    public function __destruct()
    {
        if (!$this->fh) {
            return;
        }
        fclose($this->fh);
    }

    /**
     * Append a string to the buffer. The string can contain variable placeholders in the format '{foo}' as long as
     * a matching key is found in $vars.
     *
     * @param $string
     * @param array $vars
     */
    public function append($string, array $vars=[], array $options=[])
    {
        $options = (object)array_merge($this->default_append_options, $options);

        $string = preg_replace_callback("/(\{(.*?)\})/", function ($m) use ($vars) {
            if (array_key_exists($m[2], $vars)) {
                return $vars[$m[2]];
            }
            return $m[1];
        }, $string);

        if ($options->wrap) {
            $wrap = ($options->indent)?$options->wrap - $options->indent:$options->wrap;
            if ($options->markup) {
                $string = $this->softWrapText($string, $wrap);
            } else {
                $string = wordwrap($string, $wrap);
            }
        }

        if ($options->indent) {
            if ($options->indent === true) {
                $indent = strlen($string) - ltrim($string," ");
                $string = ltrim($string," ");
            } else {
                $indent = (int)$options->indent;
            }
            $indent = str_repeat(" ",$indent);
            $string = $indent . join("\n{$indent}", explode("\n", $string));
            $string = rtrim($string," ");
        }

        if ($options->newline) {
            $string = $string . "\n";
        }

        fseek($this->fh, 0, SEEK_END);
        fwrite($this->fh, $string);
    }

    protected function softWrapText($string, $wrap)
    {
        $pos = 0;
        $out = [];
        $buf = null;
        while ($pos <= strlen($string)) {
            $next = strpos($string, " ",$pos);
            $nextbr= strpos($string, "\n",$pos);
            if (($next===false) || ($nextbr < $next)) {
                $next = $nextbr;
            }
            if ($next===false) {
                $next = strlen($string);
            }
            $sub = substr($string, $pos, $next-$pos+1);

            if ((strlen(strip_tags($buf)) + strlen(strip_tags($sub)) > $wrap)) {
                $out[] = $buf."\n";
                $buf = null;
            }
            $buf.= $sub;
            if  (strpos($sub,"\n")!==false) {
                $out[] = $buf;
                $buf = null;
            }
            if ($next == -1) {
                break;
            }
            $pos = $next + 1;
        }
        if (strlen($buf)>0) {
            $out[] = $buf;
        }

        return join("",$out);
    }

    /**
     * Truncate the buffer to the specified position
     *
     * @param int $position
     * @return bool
     */
    public function truncate($position=0)
    {
        return ftruncate($this->fh, $position);
    }

    /**
     * Return the entire string
     *
     * @return string
     */
    public function __toString()
    {
        fseek($this->fh, 0, SEEK_SET);
        return stream_get_contents($this->fh);
    }

    /**
     * Write the string to a file
     *
     * @param $filename
     */
    public function writeFile($filename)
    {
        fseek($this->fh, 0, SEEK_SET);
        $dest = fopen($filename, "rb");
        stream_copy_to_stream($this->fh, $dest);
        fclose($dest);
    }

    public function __invoke($string, $args=null)
    {
        if (func_num_args()>1) {
            $va = func_get_args();
            $this->append(call_user_func_array("sprintf", $va));
        } else {
            $this->append($string);
        }
    }

}