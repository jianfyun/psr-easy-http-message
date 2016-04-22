<?php
namespace PsrEasy\Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * HTTP request message instance, especially for an incoming, server-side HTTP request.
 *
 * @uses Request
 * @uses Psr\Http\Message\ServerRequestInterface
 * @package PsrEasy\Http\Message
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * Cookie arguments.
     *
     * @var array
     * @access protected
     */
    protected $cookies = [];

    /**
     * Query string arguments.
     *
     * @var array
     * @access protected
     */
    protected $query = [];

    /**
     * Uploaded file instances.
     *
     * @var array
     * @access protected
     */
    protected $files = [];

    /**
     * Parsed body data.
     *
     * @var array
     * @access protected
     */
    protected $data = [];

    /**
     * Attributes derived from the request.
     *
     * @var array
     * @access protected
     */
    protected $attributes = [];

    /**
     * __construct 
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->withMethod($_SERVER['REQUEST_METHOD']);
            $this->withUri(new Uri($_SERVER['REQUEST_URI']));

            $this->parseHeaders();

            $method = $this->getMethod();

            if ($method == 'POST' || $method == 'PUT') {
                $this->withBody(new Stream('php://input', 'r'));
            }
        }
    }

    /**
     * Retrieve server parameters.
     *
     * @access public
     * @return array Server parameters which is compatible with the structure of $_SERVER.
     */
    public function getServerParams()
    {
        return $_SERVER;
    }

    /**
     * Retrieve cookies.
     *
     * @access public
     * @return array Cookies which is compatible with the structure of $_COOKIE.
     */
    public function getCookieParams()
    {
        return array_merge($_COOKIE, $this->cookies);
    }

    /**
     * Return an instance with the specified cookies.
     *
     * @param  array $cookies Array of key/value pairs representing cookies.
     * @access public
     * @return self
     */
    public function withCookieParams(array $cookies)
    {
        $this->cookies = $cookies;
        return $this;
    }

    /**
     * Retrieve query string arguments.
     *
     * @access public
     * @return array Query string arguments which is compatible with the structure of $_GET.
     */
    public function getQueryParams()
    {
        return array_merge($_GET, $this->query);
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * @param  array $query Array of query string arguments, typically from $_GET.
     * @access public
     * @return self
     */
    public function withQueryParams(array $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * @access public
     * @return array An array tree of Psr\Http\Message\UploadedFileInterface instances.
     */
    public function getUploadedFiles()
    {
        if (empty($this->files) && !empty($_FILES)) {
            $this->files = $this->parseUploadedFiles($_FILES);
        }

        return $this->files;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * @param  array $uploadedFiles An array tree of Psr\Http\Message\UploadedFileInterface instances.
     * @access public
     * @return self
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $leaves = $this->getFileTreeLeaves($uploadedFiles);

        foreach ($leaves as $leaf) {
            if (!$leaf instanceof UploadedFileInterface) {
                throw new \InvalidArgumentException('Invalid structure of $uploadedFiles');
            }
        }

        $this->files = $uploadedFiles;
        return $this;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * @access public
     * @return null|array|object The deserialized body parameters, if any.
     */
    public function getParsedBody()
    {
        if (!empty($this->data)) {
            return $this->data;
        }

        if ('POST' == strtoupper($this->getMethod())) {
            if (!empty($_POST)) {
                return $_POST;
            }

            $contentType = $this->getHeaderLine('Content-Type');

            if (strpos($contentType, 'application/json') !== false) {
                return json_decode((string) $this->body, true);
            } elseif (strpos($contentType, 'application/xml') !== false) {
                return simplexml_load_string((string) $this->body);
            }
        }

        return null;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * @param  null|array|object $data The deserialized body data.
     * @access public
     * @return self
     */
    public function withParsedBody($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * @access public
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * @param  string $name    The attribute name.
     * @param  mixed  $default Default value to return if the attribute does not exist.
     * @access public
     * @return mixed  Single derived request attribute.
     */
    public function getAttribute($name, $default = null)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * @param  string $name  The attribute name.
     * @param  mixed  $value The value of the attribute.
     * @access public
     * @return self
     */
    public function withAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * @param  string $name The attribute name.
     * @access public
     * @return self
     */
    public function withoutAttribute($name)
    {
        unset($this->attributes[$name]);
        return $this;
    }

    /**
     * Retrieve leaves of the uploaded file tree
     *
     * @param  array $branch Tree branch.
     * @access protected
     * @return array Leaves of the uploaded file tree.
     */
    protected function getFileTreeLeaves(array $branch)
    {
        $leaves = [];

        foreach ($branch as $name => $value) {
            if (is_array($value)) {
                $leaves = array_merge($leaves, $this->getFileTreeLeaves($value));
            } else {
                $leaves[] = $value;
            }
        }

        return $leaves;
    }

    /**
     * Parse HTTP request headers.
     *
     * @access protected
     * @return array The headers array.
     */
    protected function parseHeaders()
    {
        $headers = [];

        if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
            $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
        } elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            $headers['Authorization'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
        }

        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $name  = substr($key, 5);
                $words = explode('_', $name);

                foreach ($words as &$word) {
                    $word = ucfirst(strtolower($word));
                }

                $name = implode('-', $words);
                $headers[$name] = explode(',', $value);
            }
        }

        foreach ($headers as $name => $value) {
            $this->withHeader($name, $value);
        }
    }

    /**
     * Parse the uploaded files to normalized tree structure.
     *
     * @param  array $items The uploaded file items.
     * @access protected
     * @return array Normalized tree structure.
     */
    protected function parseUploadedFiles(array $items)
    {
        $branch = [];

        foreach ($items as $file => $item) {
            if (!is_array($item)) {
                continue;
            } elseif (is_array($item['name']) && is_array($item['type']) && is_array($item['tmp_name'])) {
                foreach ($item['name'] as $index => $name) {
                    $fileInfo = [
                        'name'     => $name,
                        'type'     => $item['type'][$index],
                        'tmp_name' => $item['tmp_name'][$index],
                        'error'    => $item['error'][$index],
                        'size'     => $item['size'][$index],
                    ];
                    $branch[$file][$index] = new UploadedFile($fileInfo);
                }
            } elseif (is_string($item['name']) && is_string($item['type']) && is_string($item['tmp_name'])) {
                $branch[$file] = new UploadedFile($item);
            } else {
                $branch[$file] = $this->parseUploadedFiles($item);
            }
        }

        return $branch;
    }
}
