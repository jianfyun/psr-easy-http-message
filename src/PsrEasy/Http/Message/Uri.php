<?php
namespace PsrEasy\Http\Message;

use Psr\Http\Message\UriInterface;

/**
 * HTTP URI instance.
 *
 * @uses    Psr\Http\Message\UriInterface
 * @package PsrEasy\Http\Message
 * @see     http://www.php-fig.org/psr/psr-7/
 */
class Uri implements UriInterface
{
    /**
     * Min TCP(or UDP) port number.
     */
    const MIN_TCP_PORT = 1;

    /**
     * Max TCP(or UDP) port number.
     */
    const MAX_TCP_PORT = 65535;

    /**
     * Raw input URI string
     *
     * @var string
     * @access private
     */
    private $rawUri = '';

    /**
     * The URI components.
     *
     * @var array
     * @access private
     */
    private $components = [];

    /**
     * __construct
     *
     * @param  string $rawUri Raw URI string
     * @access public
     * @return void
     * @throw  \InvalidArgumentException if an error occurs.
     */
    public function __construct($rawUri = '')
    {
        if ($rawUri != '') {
            $this->components = parse_url($rawUri);

            if ($this->components === false) {
                throw new \InvalidArgumentException("The rawUri is malformed, the input is $rawUri");
            }
        }

        $this->rawUri = $rawUri;
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * @access public
     * @return string The URI scheme.
     */
    public function getScheme()
    {
        return strtolower($this->components['scheme'] == '' ? '' : $this->components['scheme']);
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * @access public
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        $authority = $this->components['host'];

        if ($this->components['user'] != '' && $this->components['pass'] != '') {
            $authority = "{$this->components['user']}:{$this->components['pass']}@{$this->components['host']}";
        }

        if ($this->components['port'] != '') {
            $authority .= ":{$this->components['port']}";
        }

        return $authority;
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * @access public
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        $userInfo = '';

        if ($this->components['user'] != '') {
            $userInfo = $this->components['user'];

            if ($this->components['pass'] != '') {
                $userInfo .= ":{$this->components['pass']}";
            }
        }

        return $userInfo;
    }

    /**
     * Retrieve the host component of the URI.
     *
     * @access public
     * @return string The URI host.
     */
    public function getHost()
    {
        return strtolower($this->components['host'] == '' ? '' : $this->components['host']);
    }

    /**
     * Retrieve the port component of the URI.
     *
     * @access public
     * @return null|int The URI port.
     */
    public function getPort()
    {
        $port = null;

        if ($this->components['port'] != '') {
            $port = intval($this->components['port']);

            if ($port == 80 && $this->getScheme() == 'http') {
                $port = null;
            } elseif ($port == 443 && $this->getScheme() == 'https') {
                $port = null;
            }
        }

        return $port;
    }

    /**
     * Retrieve the path component of the URI.
     *
     * @access public
     * @return string The URI path.
     */
    public function getPath()
    {
        $path = '';

        if ($this->components['path'] != '') {
            $path = $this->components['path'];

            while (strpos($path, '//') !== false) {
                $path = str_replace('//', '/', $path);
            }

            if (strpos($path, '%') === false) {
                $parts = explode('/', $path);

                foreach ($parts as &$part) {
                    if ($part != '') {
                        $part = rawurlencode($part);
                    }
                }

                $path = implode('/', $parts);
            }
        }

        return $path;
    }

    /**
     * Retrieve the query string of the URI.
     *
     * @access public
     * @return string The URI query string.
     */
    public function getQuery()
    {
        $query = '';

        if ($this->components['query'] != '') {
            $query = $this->components['query'];

            if (strpos($query, '%') === false) {
                $pairs = explode('&', $query);
                $parts = [];

                foreach ($pairs as $pair) {
                    list($name, $value) = explode('=', $pair);
                    $parts[] = "$name=" . rawurlencode($value);
                }

                $query = implode('&', $parts);
            }
        }

        return $query;
    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * @access public
     * @return string The URI fragment.
     */
    public function getFragment()
    {
        $fragment = '';

        if ($this->components['fragment'] != '') {
            $fragment = $this->components['fragment'];

            if (strpos($fragment, '%') === false) {
                $fragment = rawurlencode($fragment);
            }
        }

        return $fragment;
    }

    /**
     * Return an instance with the specified scheme.
     *
     * @param  string $scheme The scheme to use with the new instance.
     * @access public
     * @return self
     */
    public function withScheme($scheme)
    {
        $scheme = strtolower($scheme);

        if ($scheme != 'http' && $scheme != 'https') {
            throw new \InvalidArgumentException("Scheme only accept http or https, the input is $scheme");
        }

        $this->components['scheme'] = $scheme;
        return $this;
    }

    /**
     * Return an instance with the specified user information.
     *
     * @param  string      $user     The user name to use for authority.
     * @param  string|null $password The password associated with $user.
     * @access public
     * @return self
     */
    public function withUserInfo($user, $password = null)
    {
        $this->components['user'] = $user;

        if ($password != null) {
            $this->components['pass'] = $password;
        }

        return $this;
    }

    /**
     * Return an instance with the specified host.
     *
     * @param  string $host The hostname to use with the new instance.
     * @access public
     * @return self
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {
        if (!preg_match('/^[\w-]+(\.[\w-]+)+$/', $host)) {
            throw new \InvalidArgumentException("Only accept valid hostname, the input is $host");
        }

        $this->components['host'] = $host;
        return $this;
    }

    /**
     * Return an instance with the specified port.
     *
     * @param  int|null $port The port to use with the new instance; a null value removes the port information.
     * @access public
     * @return self
     */
    public function withPort($port)
    {
        if (!is_numeric($port) || $port != intval($port)) {
            throw new \InvalidArgumentException("Only accept number for port, the input is $port");
        }

        $port = intval($port);

        if ($port < self::MIN_TCP_PORT || $port > self::MAX_TCP_PORT) {
            throw new \InvalidArgumentException("Port exceed valid range, the input is $port");
        }

        $this->components['port'] = $port;
        return $this;
    }

    /**
     * Return an instance with the specified path.
     *
     * @param  string $path The path to use with the new instance.
     * @access public
     * @return self
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {
        $this->components['path'] = $path;
        return $this;
    }

    /**
     * Return an instance with the specified query string.
     *
     * @param  string $query The query string to use with the new instance.
     * @access public
     * @return self
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {
        if ($query[0] == '?') {
            throw new \InvalidArgumentException("Query can not start with ?, the input is $port");
        }

        $this->components['query'] = $query;
        return $this;
    }

    /**
     * Return an instance with the specified URI fragment.
     *
     * @param  string $fragment The fragment to use with the new instance.
     * @access public
     * @return self
     */
    public function withFragment($fragment)
    {
        if ($fragment[0] == '#') {
            throw new \InvalidArgumentException("Fragment can not start with #, the input is $port");
        }

        $this->components['fragment'] = $fragment;
        return $this;
    }

    /**
     * Return the string representation as a URI reference.
     *
     * @access public
     * @return string URI string;
     */
    public function __toString()
    {
        $uri = '';
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        if ($scheme != '') {
            $uri .= "{$scheme}:";
        }

        if ($authority != '') {
            $uri .= "//{$authority}";
        }

        if ($path != '') {
            $uri .= ($path[0] != '/') ? "/{$path}" : $path;
        }

        if ($query != '') {
            $uri .= "?{$query}";
        }

        if ($fragment != '') {
            $uri .= "#{$fragment}";
        }

        return $uri;
    }
}
