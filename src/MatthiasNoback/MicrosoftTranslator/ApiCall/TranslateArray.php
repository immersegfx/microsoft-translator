<?php

namespace MatthiasNoback\MicrosoftTranslator\ApiCall;

class TranslateArray extends AbstractMicrosoftTranslatorApiCall
{
    const MAXIMUM_NUMBER_OF_ARRAY_ELEMENTS = 2000;

    private $texts;
    private $to;
    private $from;

    public function __construct(array $texts, $to, $from = '')
    {
        if (count($texts) > self::MAXIMUM_NUMBER_OF_ARRAY_ELEMENTS) {
            throw new \InvalidArgumentException(sprintf(
                'A maximum amount of %d texts is allowed',
                self::MAXIMUM_NUMBER_OF_ARRAY_ELEMENTS
            ));
        }

        $totalLengthOfTexts = self::calculateTotalLengthOfTexts($texts);
        if ($totalLengthOfTexts > self::MAXIMUM_LENGTH_OF_TEXT) {
            throw new \InvalidArgumentException(sprintf(
                'A maximum amount of %d characters is allowed',
                self::MAXIMUM_LENGTH_OF_TEXT
            ));
        }

        $this->texts = $texts;
        $this->to = $to;
        $this->from = $from;
    }

    private static function calculateTotalLengthOfTexts(array $texts)
    {
        $totalLength = 0;

        array_walk($texts, function($text) use (&$totalLength) {
            $totalLength += strlen($text);
        });

        return $totalLength;
    }

    public function getApiMethodName()
    {
        return 'TranslateArray';
    }

    public function getHttpMethod()
    {
        return 'POST';
    }

    public function getRequestContent()
    {
        $document = new \DOMDocument();
        $document->appendChild($rootElement = $document->createElement('TranslateArrayRequest'));

        $appIdElement = $document->createElement('AppId');
        $rootElement->appendChild($appIdElement);

        $fromElement = $document->createElement('From', $this->from);
        $rootElement->appendChild($fromElement);

        $textsElement = $document->createElement('Texts');
        $rootElement->appendChild($textsElement);

        foreach ($this->texts as $text) {
            $stringElement = $document->createElementNS('http://schemas.microsoft.com/2003/10/Serialization/Arrays', 'string', $text);
            $textsElement->appendChild($stringElement);
        }

        $toElement = $document->createElement('To', $this->to);
        $rootElement->appendChild($toElement);

        return $document->saveXML();
    }

    public function getQueryParameters()
    {
    }

    public function parseResponse($response)
    {
        $simpleXml = $this->toSimpleXML($response);

        $translations = array();
        foreach ($simpleXml->TranslateArrayResponse as $translateArrayResponse) {
            if (isset($translateArrayResponse->Error)) {
                $translation = '';
                // TODO maybe find a better way to handle translation errors
            }
            else {
                $translation = (string) $translateArrayResponse->TranslatedText;
            }

            $translations[] = $translation;
        }

        return $translations;
    }
}
