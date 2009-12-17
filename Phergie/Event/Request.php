<?php

/**
 * Autonomous event originating from a user or the server.
 *
 * @see http://www.irchelp.org/irchelp/rfc/chapter4.html
 */
class Phergie_Event_Request 
    extends Phergie_Event_Abstract 
    implements ArrayAccess
{
    /**
     * Nick message event type
     *
     * @const string
     */
    const TYPE_NICK = 'nick';

    /**
     * Whois message event type
     *
     * @const string
     */
    const TYPE_WHOIS = 'whois';

    /**
     * Quit command event type
     *
     * @const string
     */
    const TYPE_QUIT = 'quit';

    /**
     * Join message event type
     *
     * @const string
     */
    const TYPE_JOIN = 'join';

    /**
     * Kick message event type
     *
     * @const string
     */
    const TYPE_KICK = 'kick';

    /**
     * Part message event type
     *
     * @const string
     */
    const TYPE_PART = 'part';

    /**
     * Mode message event type
     *
     * @const string
     */
    const TYPE_MODE = 'mode';

    /**
     * Topic message event type
     *
     * @const string
     */
    const TYPE_TOPIC = 'topic';

    /**
     * Private message command event type
     *
     * @const string
     */
    const TYPE_PRIVMSG = 'privmsg';

    /**
     * Notice message event type
     *
     * @const string
     */
    const TYPE_NOTICE = 'notice';

    /**
     * Pong message event type
     *
     * @const string
     */
    const TYPE_PONG = 'pong';

    /**
     * CTCP ACTION command event type
     *
     * @const string
     */
    const TYPE_ACTION = 'action';

    /**
     * CTCP PING command event type
     *
     * @const string
     */
    const TYPE_PING = 'ping';

    /**
     * CTCP TIME command event type
     *
     * @const string
     */
    const TYPE_TIME = 'time';

    /**
     * CTCP VERSION command event type
     *
     * @const string
     */
    const TYPE_VERSION = 'version';

    /**
     * RAW message event type
     *
     * @const string
     */
    const TYPE_RAW = 'raw';

    /**
     * Mapping of event types to their named parameters
     *
     * @var array
     */
    protected static $_map = array(

        self::TYPE_QUIT => array(
            'message' => 0
        ),

        self::TYPE_JOIN => array(
            'channel' => 0
        ),

        self::TYPE_KICK => array(
            'channel' => 0,
            'user'    => 1,
            'comment' => 2
        ),

        self::TYPE_PART => array(
            'channel' => 0,
            'message' => 1
        ),

        self::TYPE_MODE => array(
            'target'  => 0,
            'mode'    => 1,
            'limit'   => 2,
            'user'    => 3,
            'banmask' => 4
        ),

        self::TYPE_TOPIC => array(
            'channel' => 0,
            'topic'   => 1
        ),

        self::TYPE_PRIVMSG => array(
            'receiver' => 0,
            'text'     => 1
        ),

        self::TYPE_NOTICE => array(
            'nickname' => 0,
            'text'     => 1
        ),

        self::TYPE_ACTION => array(
            'target' => 0,
            'action' => 1
        ),

        self::TYPE_RAW => array(
            'message' => 0
        )

    );

    /**
     * Hostmask representing the originating user, if applicable
     *
     * @var Phergie_Hostmask
     */
    protected $_hostmask;

    /**
     * Arguments included with the message
     *
     * @var array
     */
    protected $_arguments;

    /**
     * Raw data sent by the server
     *
     * @var string
     */
    protected $_rawData;

    /**
     * Sets the hostmask representing the originating user.
     *
     * @param Phergie_Hostmask $hostmask
     * @return Phergie_Event_Request Provides a fluent interface
     */
    public function setHostmask(Phergie_Hostmask $hostmask)
    {
        $this->_hostmask = $hostmask;
        return $this;
    }

    /**
     * Returns the hostmask representing the originating user.
     *
     * @return Phergie_Event_Request|null Hostmask or NULL if none was set
     */
    public function getHostmask()
    {
        return $this->_hostmask;
    }

    /**
     * Sets the arguments for the request.
     *
     * @param array $arguments
     * @return Phergie_Event_Request Provides a fluent interface
     */
    public function setArguments($arguments)
    {
        $this->_arguments = $arguments;
        return $this;
    }

    /**
     * Returns the arguments for the request.
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Resolves an argument specification to an integer position.
     *
     * @param mixed $argument Integer position (starting from 0) or the
     *        equivalent string name of the argument from self::$_map
     * @return int|null Integer position of the argument or NULL if no 
     *         corresponding argument was found
     */
    protected function _resolveArgument($argument)
    {
        if (isset($this->_arguments[$argument])) {
            return $argument; 
        } else {
            $argument = strtolower($argument);
            if (isset(self::$_map[$this->_type][$argument]) &&
                isset($this->_arguments[self::$_map[$this->_type][$argument]])) {
                return self::$_map[$this->_type][$argument];
            }
        }
        return null;
    }

    /**
     * Returns a single specified argument for the request.
     *
     * @param mixed $argument Integer position (starting from 0) or the
     *        equivalent string name of the argument from self::$_map
     * @return string
     */
    public function getArgument($argument)
    {
        $argument = $this->_resolveArgument($argument);
        if ($argument !== null) { 
            return $this->_arguments[$argument];
        }
        return null;
    }

    /**
     * Sets the raw buffer for the event.
     *
     * @param string $buffer
     * @return Phergie_Event_Request Provides a fluent interface
     */
    public function setRawData($buffer)
    {
        $this->_rawData = $buffer;
        return $this;
    }

    /**
     * Returns the raw buffer sent from the server for the event.
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->_rawData;
    }

    /**
     * Returns the nick of the user who originated the event.
     *
     * @return string
     */
    public function getNick()
    {
        return $this->_hostmask->getNick();
    }

    /**
     * Returns the channel name if the event occurred in a channel or the 
     * user nick if the event was a private message directed at the bot by a 
     * user. 
     *
     * @return string
     */
    public function getSource()
    {
        if (substr($this->_arguments[0], 0, 1) == '#') {
            return $this->_arguments[0];
        }
        return $this->_hostmask->getNick();
    }

    /**
     * Returns whether or not the event occurred within a channel.
     *
     * @return TRUE if the event is in a channel, FALSE otherwise
     */
    public function isInChannel()
    {
        return (substr($this->getSource(), 0, 1) == '#');
    }

    /**
     * Returns whether or not the event originated from a user.
     *
     * @return TRUE if the event is from a user, FALSE otherwise
     */
    public function isFromUser()
    {
        $username = $this->_hostmask->getUsername();
        return !empty($username);
    }

    /**
     * Returns whether or not the event originated from the server.
     *
     * @return TRUE if the event is from the server, FALSE otherwise
     */
    public function isFromServer()
    {
        $username = $this->_hostmask->getUsername();
        return empty($username);
    }

    /**
     * Provides access to named parameters via virtual "getter" methods.
     *
     * @param string $name Name of the method called
     * @param array $arguments Arguments passed to the method (should always
     *                         be empty)
     * @return mixed Method return value
     */
    public function __call($name, array $arguments)
    {
        if (!count($arguments) && substr($name, 0, 3) == 'get') {
            return $this->getArgument(substr($name, 3));
        }
    }

    /**
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return ($this->_resolveArgument($offset) !== null);
    }

    /**
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->getArgument($offset);
    }

    /**
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        $offset = $this->_resolveArgument($offset);
        if ($offset !== null) { 
            $this->_arguments[$offset] = $value;
        }
    }

    /**
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        if ($offset = $this->_resolveArgument($offset)) {
            unset($this->_arguments[$offset]);
        }
    }
}
