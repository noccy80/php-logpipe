<?php


namespace NoccyLabs\LogPipe\Dumper\Filter;


use NoccyLabs\LogPipe\Message\MessageInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionFilter implements FilterInterface
{
    protected $expr;

    public function __construct($expr)
    {
        $this->language = new ExpressionLanguage();
        $this->expr = $expr;
    }

    /**
     * {@inheritdoc}
     */
    public function filterMessage(MessageInterface $message, $filtered)
    {
        $data = [
            "message" => (object)[
                "text" => $message->getText(),
                "level" => $message->getLevel()
            ]
        ];
        $filter = $this->language->evaluate($this->expr, $data);
        return $filter;
    }

}