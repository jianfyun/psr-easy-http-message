<?php
namespace PsrEasy\Http\Message;

use Psr\Http\Message\UploadedFileInterface;

/**
 * UploadedFile
 *
 * @uses Psr\Http\Message\UploadedFileInterface
 * @package PsrEasy\Http\Message
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * The uploaded file info.
     *
     * @var array
     * @access protected
     */
    protected $fileInfo = [];

    /**
     * If the uploaded file has been moved to the specified target path.
     *
     * @var bool
     * @access protected
     */
    protected $hasMoved = false;

    /**
     * __construct
     *
     * @see http://php.net/manual/en/features.file-upload.post-method.php
     * @param  array $fileInfo One uploaded file item in $_FILES.
     * @access public
     * @return void
     */
    public function __construct(array $fileInfo)
    {
        $this->fileInfo = $fileInfo;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * @access public
     * @return Psr\Http\Message\StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException                in cases when no stream is available.
     * @throws \RuntimeException                in cases when no stream can be created.
     */
    public function getStream()
    {
        if ($this->hasMoved) {
            throw new \RuntimeException("{$this->fileInfo['name']} has already been moved");
        }

        return new Stream($this->fileInfo['tmp_name'], 'r');
    }

    /**
     * Move the uploaded file to a new location.
     *
     * @param  string                    $targetPath Path to which to move the uploaded file.
     * @access public
     * @return void
     * @throws \InvalidArgumentException if the $path specified is invalid.
     * @throws \RuntimeException         on any error during the move operation.
     * @throws \RuntimeException         on the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        if ($this->hasMoved) {
            throw new \RuntimeException("{$this->fileInfo['name']} has already been moved");
        }

        if (!is_uploaded_file($this->fileInfo['tmp_name'])) {
            throw new \RuntimeException("{$this->fileInfo['name']} is not uploaded file");
        }

        if (!move_uploaded_file($this->fileInfo['tmp_name'], $targetPath)) {
            throw new \RuntimeException("moving file {$this->fileInfo['name']} to {$targetPath} fails");
        }

        $hasMoved = true;
    }

    /**
     * Retrieve the file size.
     *
     * @access public
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        return $this->fileInfo['size'];
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * @access public
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        return $this->fileInfo['error'];
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * @access public
     * @return string|null The filename sent by the client or null if none was provided.
     */
    public function getClientFilename()
    {
        return $this->fileInfo['name'];
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * @access public
     * @return string|null The media type sent by the client or null if none was provided.
     */
    public function getClientMediaType()
    {
        return $this->fileInfo['type'];
    }
}
