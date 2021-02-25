<?php

namespace BulkGate\Extensions\IO;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions;

class FSock extends Extensions\Strict implements IConnection
{
    /** @var  string */
    private $application_id;

    /** @var  string */
    private $application_token;

    /** @var string */
    private $application_url;

    /** @var string */
    private $application_product;

    /** @var string */
    private $application_language;


    /**
     * Connection constructor.
     * @param $application_id
     * @param $application_token
     * @param $application_url
     * @param $application_product
     * @param $application_language
     */
    public function __construct($application_id, $application_token, $application_url, $application_product, $application_language)
    {
        $this->application_id = $application_id;
        $this->application_token = $application_token;
        $this->application_url = $application_url;
        $this->application_product = $application_product;
        $this->application_language = $application_language;
    }


    /**
     * @param Request $request
     * @throws ConnectionException
     * @return Response
     */
    public function run(Request $request)
    {
        $connection = @fopen($request->getUrl(), 'r', false, stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => array(
                    'Content-type: ' . $request->getContentType(),
                    'X-BulkGate-Application-ID: ' . (string) $this->application_id,
                    'X-BulkGate-Application-Token: ' . (string) $this->application_token,
                    'X-BulkGate-Application-Url: ' . (string) $this->application_url,
                    'X-BulkGate-Application-Product: '. (string) $this->application_product,
                    'X-BulkGate-Application-Language: '. (string) $this->application_language
                ),
                'content' => $request->getData(),
                'ignore_errors' => true,
                'timeout' => $request->getTimeout()
            )
        )));

        if ($connection)
        {
            $meta = stream_get_meta_data($connection);

            $header = new HttpHeaders(implode("\r\n", $meta['wrapper_data']));

            $result = stream_get_contents($connection);

            fclose($connection);

            return new Response($result, $header->getContentType());
        }
        return new Response(array('data' => array(), 'exception' => 'ConnectionException', 'error' => array('Server ('.$request->getUrl().') is unavailable. Try contact your hosting provider.')));
    }
}
