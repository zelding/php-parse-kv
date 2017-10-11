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

        $lines = array_map(function ($lineString, $index){
            return [
                "text"   => trim($lineString),
                "tokens" => [],
                "line"   => $index + 1
            ];
        }, $lines);

        $lines = array_filter($lines, function ($elementData) {
            return !empty($elementData["text"]);
        });

        $lines = array_filter($lines, function ($elementData) {
            return substr($elementData["text"], 0, 2) !== "//";
        });

        return array_map([$this, 'tokenizeLine'], $lines);
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
