<?php
namespace PsrEasy\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * HTTP message instance, for both HTTP request and HTTP response
 *
 * @uses MessageInterface
 * @package PsrEasy\Http\Message
 * @see http://www.php-fig.org/psr/psr-7/
 */
class Message implements MessageInterface
{
    /**
     * HTTP protocol version.
     *
     * @var string
     * @access protected
     */
    protected $version = '';

    /**
     * HTTP headers.
     *
     * @var array
     * @access protected
     */
    protected $headers = [];

    /**
     * HTTP body.
     *
     * @var Psr\Http\Message\StreamInterface
     * @access protected
     */
    protected $body = null;

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * @access public
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * @param  string $version HTTP protocol version.
     * @access public
     * @return self
     */
    public function withProtocolVersion($version)
    {
        if ($version == '1.0' || $version == '1.1') {
            $this->version = strval($version);
        }

        return $this;
    }

    /**
     * Retrieves all message header values.
     *
     * @access public
     * @return array Returns an associative array of the message's headers.
     *               Each key is a header name, and each value is an array of
     *               strings for that header.
     */
    public function getHeaders()
    {
        $headers = [];

        foreach ($this->headers as $lower => $list) {
            $headers = array_merge($headers, $list);
        }

        return $headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param  string $name Case-insensitive header field name.
     * @access public
     * @return bool   Returns true if any header names match the given header
     *                     name using a case-insensitive string comparison. Returns false if
     *                     no matching header name is found in the message.
     */
    public function hasHeader($name)
    {
        return array_key_exists(strtolower($name), $this->headers);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * @param  string $name Case-insensitive header field name.
     * @access public
     * @return array  An array of string values as provided for the given header.
     *                     If the header does not appear in the message, this method return an empty array.
     */
    public function getHeader($name)
    {
        $header = [];
        $lower  = strtolower($name);

        if (empty($this->headers[$lower])) {
            return $header;
        }

        foreach ($this->headers[$lower] as $list) {
            $header = array_merge($header, $list);
        }

        return $header;
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * @param  string $name Case-insensitive header field name.
     * @access public
     * @return string A string of values as provided for the given header
     *                     concatenated together using a comma. If the header does not appear in
     *                     the message, this method return an empty string.
     */
    public function getHeaderLine($name)
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * Return an instance with the provided value replacing the specified header.
     *
     * @param  string                    $name  Case-insensitive header field name.
     * @param  string|array              $value Header value(s).
     * @access public
     * @return self
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($name, $value)
    {
        $replace = [];

        if (is_array($value)) {
            $replace = $value;
        } elseif (is_string($value)) {
            $replace = [$value];
        } else {
            throw new \InvalidArgumentException('Value must be string or array, the input is ' . gettype($value));
        }

        $this->headers[strtolower($name)][$name] = $replace;
        return $this;
    }

    /**
     * Return an instance with the specified header appended with the given value.
     *
     * @param  string                    $name  Case-insensitive header field name to add.
     * @param  string|array              $value Header value(s).
     * @access public
     * @return self
     * @throws \InvalidArgumentException for invalid header names.
     * @throws \InvalidArgumentException for invalid header values.
     */
    public function withAddedHeader($name, $value)
    {
        $append = [];

        if (is_array($value)) {
            $append = $value;
        } elseif (is_string($value)) {
            $append = [$value];
        } else {
            throw new \InvalidArgumentException('Header value must be string or array, the input is ' . gettype($value));
        }

        $lower = strtolower($name);

        if (array_key_exists($lower, $this->headers)) {
            if (array_key_exists($name, $this->headers[$lower])) {
                $this->headers[$lower][$name] = array_merge($this->headers[$lower][$name], $append);
            } else {
                $this->headers[$lower][$name] = $append;
            }
        } else {
            $this->headers[$lower][$name] = $append;
        }

        return $this;
    }

    /**
     * Return an instance without the specified header.
     *
     * @param  string $name Case-insensitive header field name to remove.
     * @access public
     * @return self
     */
    public function withoutHeader($name)
    {
        unset($this->headers[strtolower($name)][$name]);
        return $this;
    }

    /**
     * Gets the body of the message.
     *
     * @access public
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Return an instance with the specified message body.
     *
     * @param  StreamInterface           $body Body.
     * @access public
     * @return self
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        $this->body = $body;
        return $this;
    }
}
