<?php
namespace PsrEasy\Http\Message;

use Psr\Http\Message\StreamInterface;

/**
 * HTTP data stream instance
 *
 * @uses Psr\Http\Message\StreamInterface
 * @package PsrEasy\Http\Message
 * @see http://www.php-fig.org/psr/psr-7/
 */
class Stream implements StreamInterface
{
    /**
     * Stream name, file name or URI.
     *
     * @var string
     * @access protected
     */
    protected $name = '';

    /**
     * Resource handler.
     *
     * @var resource
     * @access protected
     */
    protected $resource = null;

    /**
     * Access mode.
     *
     * @var string
     * @access protected
     */
    protected $mode = '';

    /**
     * __construct
     *
     * @see http://www.php.net/manual/en/function.fopen.php
     * @param  string $name Stream name, file name or URI
     * @param  string $mode Access mode.
     * @access public
     * @return void
     * @throw \RuntimeException if open stream error.
     */
    public function __construct($name, $mode)
    {
        $this->name     = $name;
        $this->mode     = $mode;
        $this->resource = fopen($name, $mode);

        if ($this->resource === false) {
            throw new \RuntimeException("open string $name error");
        }
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * @access public
     * @return string All data.
     */
    public function __toString()
    {
        rewind($this->resource);
        return stream_get_contents($this->resource);
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @access public
     * @return void
     */
    public function close()
    {
        fclose($this->resource);
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @access public
     * @return resource|null Underlying PHP stream, if any.
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     * Get the size of the stream if known.
     *
     * @access public
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        $bytes = null;

        if ($this->getMetadata('wrapper_type') == 'plainfile' || file_exists($this->name)) {
            $bytes = filesize($this->name);
            return ($bytes === false) ? null : $bytes;
        }

        rewind($this->resource);
        return strlen(stream_get_contents($this->resource));
    }

    /**
     * Returns the current position of the file read/write pointer.
     *
     * @access public
     * @return int               Position of the file pointer.
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        $position = ftell($this->resource);

        if ($position === false) {
            throw new \RuntimeException('find the current position of the string');
        }

        return $position;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @access public
     * @return bool If the stream is at the end of the stream.
     */
    public function eof()
    {
        return feof($this->resource);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @access public
     * @return bool Whether or not the stream is seekable.
     */
    public function isSeekable()
    {
        $seekable = $this->getMetadata('seekable');

        if ($seekable == true) {
            return true;
        }

        return false;
    }

    /**
     * Seek to a position in the stream.
     *
     * @see http://www.php.net/manual/en/function.fseek.php
     * @param  int               $offset Stream offset.
     * @param  int               $whence Specifies how the cursor position will be calculated based on the seek offset.
     * @access public
     * @return void
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (fseek($this->resource, $offset, $whence) == -1) {
            throw new \RuntimeException("seek {$this->name} to $offset error");
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * @access public
     * @return void
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        if (!rewind($this->resource)) {
            throw new \RuntimeException("rewind string {$this->name} error");
        }
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @access public
     * @return bool Whether or not the stream is writable.
     */
    public function isWritable()
    {
        if (similar_text($this->mode, 'cwa+') > 0) {
            return true;
        }

        return false;
    }

    /**
     * Write data to the stream.
     *
     * @param  string  $bytes The string that is to be written.
     * @access public
     * @return Returns the number of bytes written to the stream.
     */
    public function write($bytes)
    {
        $length = fwrite($this->resource, $bytes);

        if ($length === false) {
            throw new \RuntimeException("write string {$this->name} error");
        }

        return $length;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @access public
     * @return bool Whether or not the stream is readable.
     */
    public function isReadable()
    {
        if (similar_text($this->mode, 'ra+') > 0) {
            return true;
        }

        return false;
    }

    /**
     * Read data from the stream.
     *
     * @param  int               $length Read up to $length bytes from the object and return them.
     * @access public
     * @return string            Returns the data read from the stream, or an empty string if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        $bytes = fread($this->resource, $length);

        if ($bytes === false) {
            throw new \RuntimeException("read string {$this->name} error");
        }

        return $bytes;
    }

    /**
     * Returns the remaining contents in a string.
     *
     * @access public
     * @return string            The remaining contents.
     * @throws \RuntimeException if error occurs while reading.
     */
    public function getContents()
    {
        $remainder = stream_get_contents($this->resource);

        if ($remainder === false) {
            throw new \RuntimeException("read the remainder of stream {$this->name} error");
        }

        return $remainder;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * @see http://php.net/manual/en/function.stream-get-meta-data.php
     * @param  string           $key Specific metadata to retrieve.
     * @access public
     * @return array|mixed|null Returns an associative array if no key is provided.
     *                              Returns a specific key value if a key is provided and the value is found,
     *                              or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        $meta = stream_get_meta_data($this->resource);

        if ($key != null) {
            return $meta[$key];
        }

        return $meta;
    }
}
