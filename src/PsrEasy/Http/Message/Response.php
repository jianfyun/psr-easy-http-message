<?php
namespace PsrEasy\Http\Message;

use Psr\Http\Message\ResponseInterface;

/**
 * HTTP response message instance
 *
 * @uses Message
 * @uses ResponseInterface
 * @package
 * @see http://www.php-fig.org/psr/psr-7/
 */
class Response extends Message implements ResponseInterface
{
    /**
     * HTTP response status code.
     *
     * @var int
     * @access protected
     */
    protected $status = 200;

    /**
     * HTTP response reason phrase.
     *
     * @var string
     * @access protected
     */
    protected $phrase = '';

    /**
     * Gets the response status code.
     *
     * @access public
     * @return int Status code.
     */
    public function getStatusCode()
    {
        return $this->status;
    }

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * @param  int                       $code         The 3-digit integer result code to set.
     * @param  string                    $reasonPhrase The reason phrase to use with the provided status code.
     * @access public
     * @return self
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        if (!is_numeric($code) || strlen($code) != 3) {
            throw new \InvalidArgumentException("Invalid HTTP status code: $code");
        }

        $this->status = intval($code);
        $this->phrase = $reasonPhrase;

        if ($reasonPhrase == '') {
            $defaultPhrases = [
                100 => 'Continue',
                101 => 'Switching Protocols',
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Moved Temporarily ',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                307 => 'Temporary Redirect',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Emented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported',
                509 => 'Bandwidth Limit Exceeded',
            ];
            $this->phrase = (string) $defaultPhrases[$code];
        }

        return $this;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * @access public
     * @return string Reason phrase. Return an empty string if none present.
     */
    public function getReasonPhrase()
    {
        return $this->phrase;
    }
}
