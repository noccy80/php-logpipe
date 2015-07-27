<?php


namespace NoccyLabs\LogPipe\Message;


/**
 * Interface MessageInterface
 * @package NoccyLabs\LogPipe\Message
 */
interface MessageInterface {

    /**
     * @return mixed
     */
    public function getTimestamp();

    /**
     * @return mixed
     */
    public function getSource();

    /**
     * @return string
     */
    public function getChannel();
    
    /**
     * @return mixed
     */
    public function getLevel();

    /**
     * @return mixed
     */
    public function getText();

    /**
     * @return mixed
     */
    public function getData();

    public function getExtra();

    /**
     * @param array $data
     * @return mixed
     */
    public function setData(array $data);

    /**
     * @return mixed
     */
    public function getClientId();

    /**
     * @return mixed
     */
    public function __toString();

}
