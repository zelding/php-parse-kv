<?php
/**
 * php-parse-kv
 *
 * @author    lyozsi (kristof.dekany@apex-it-services.eu)
 * @copyright internal usage
 *
 * Date: 10/11/17 12:16 PM
 */

namespace Zedling\DotaKV;


class Tokenizer
{
    public function __construct()
    {
    }

    /**
     *
     * @param string $data
     *
     * @return array
     */
    public function tokenizeKVData(string $data) : array
    {
        $lines = explode("\n", $data);

        $lineData = [];

        foreach($lines as $index => $line) {
            $lineData[] = [
                "text"   => trim($line),
                "tokens" => [],
                "line"   => $index + 1
            ];
        }

        $lineData = array_filter($lineData, function ($elementData) {
            return !empty($elementData["text"]);
        });

        $lineData = array_filter($lineData, function ($elementData) {
            return substr($elementData["text"], 0, 2) !== "//";
        });

        return array_map([$this, 'tokenizeLine'], $lineData);
    }

    /**
     * @param array $lineData
     *
     * @return array
     */
    protected function tokenizeLine(array $lineData) : array
    {
        $tokens = preg_split("~/([\"\\\\])/g~", $lineData["text"]);
        $tokens = array_filter($tokens, function ($string) {
            return !empty($string);
        });

        $lineData["tokens"] = $tokens;

        return $lineData;
    }
}
