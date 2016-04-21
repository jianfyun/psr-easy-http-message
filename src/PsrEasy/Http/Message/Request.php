<?php
namespace PsrEasy\Http\Message;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * HTTP request message instance, especially for an outgoing, client-side request.
 *
 * @uses Message
 * @uses Psr\Http\Message\RequestInterface
 * @package PsrEasy\Http\Message
 * @see http://www.php-fig.org/psr/psr-7/
 */
class Request extends Message implements RequestInterface
{
    /**
     * HTTP request method.
     *
     * @var string
     * @access protected
     */
    protected $method = '';

    /**
     * HTTP request URI.
     *
     * @var URI
     * @access protected
     */
    protected $uri = null;

    /**
     * HTTP request target.
     *
     * @var string
     * @access protected
     */
    protected $target = '';

    /**
     * Retrieves the message's request target.
     *
     * @access public
     * @return string HTTP request target. If no target has been provided, return '/'.
     */
    public function getRequestTarget()
    {
        if ($this->target == '') {
            if ($this->uri == null) {
                return '/';
            }

            return (string) $this->uri;
        }

        return $this->target;
    }

    /**
     * Return an instance with the specific request target.
     *
     * @param  string $requestTarget HTTP request target
     * @access public
     * @return self
     */
    public function withRequestTarget($requestTarget)
    {
        $this->target = $requestTarget;
        return $this;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @access public
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Return an instance with the provided HTTP method.
     *
     * @param  string $method Case-sensitive method.
     * @access public
     * @return self
     */
    public function withMethod($method)
    {
        $upper   = strtoupper($method);
        $allowed = ['GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'TRACE', 'CONNECT', 'OPTIONS'];

        if (!in_array($upper, $allowed)) {
            throw new \InvalidArgumentException("Invalid http method: $method");
        }

        $this->method = $method;
        return $this;
    }

    /**
     * Retrieves the URI instance.
     *
     * @access public
     * @return Psr\Http\Message\UriInterface Returns a UriInterface instance representing the URI of the request.
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * @param  Psr\Http\Message\UriInterface $uri          New request URI to use.
     * @param  bool                          $preserveHost Preserve the original state of the Host header.
     * @access public
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $this->uri = $uri;
        $host = $this->uri->getHost();

        if ($preserveHost) {
            if ($host != '' && !$this->hasHeader('Host')) {
                $this->withHeader('Host', $host);
            }

            return $this;
        }

        if ($host != '') {
            $this->withHeader('Host', $host);
        }

        return $this;
    }
}
