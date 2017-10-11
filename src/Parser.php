<?php
/**
 * php-parse-kv
 *
 * @author    lyozsi (kristof.dekany@apex-it-services.eu)
 * @copyright internal usage
 *
 * Date: 10/11/17 12:11 PM
 */

namespace Zedling\DotaKV;


class Parser
{
    /** @var Tokenizer */
    protected $tokenizer;

    /** @var object */
    protected $result;

    /** @var \Closure */
    protected $popStack;

    protected $stack          = [];
    protected $key            = null;
    protected $value          = null;
    protected $temporaryStack = "";
    protected $isInQuotes     = false;
    protected $isInComment    = false;
    protected $isEscaping     = false;
    protected $currentResult  = [];

    public function __construct()
    {
        $this->tokenizer = new Tokenizer();

        $this->stack          = [];
        $this->key            = null;
        $this->value          = null;
        $this->temporaryStack = "";
        $this->isInQuotes     = false;
        $this->isInComment    = false;
        $this->isEscaping     = false;

        $this->result = [
            "values" => []
        ];

        $this->currentResult = $this->result;

        $this->popStack = function () {
            $this->rootPopStack();
        };
    }

    public function loadRawData(string $data)
    {
        $lines = $this->tokenizer->tokenizeKVData($data);

        foreach($lines as $index => $line) {

            $lineTokens = $line["tokens"];

            foreach($lineTokens as $lineToken) {

                if ( $this->isInComment ) {
                    continue;
                }

                if ( !$this->isInQuotes && !strlen(trim($lineToken)) ) {
                    continue;
                }

                switch( $lineToken ) {

                    case "\\":
                        $this->isEscaping = true;
                        continue;
                    break;

                    case '"':
                        if ( $this->isEscaping ) {
                            $this->isEscaping = false;
                            break;
                        }

                        $this->isInQuotes = !$this->isInQuotes;

                        if ( !$this->isInQuotes ) {

                            switch( true ) {

                                case !$this->key:
                                    $this->key = $this->temporaryStack;
                                break;

                                case $this->value === null:
                                    $this->value = $this->temporaryStack;
                                break;

                                case $this->isInComment:
                                    // do nothing, this a comment
                                break;

                                case $this->key && $this->value:
                                    $this->currentResult[ $this->key ] = $this->value;

                                    $this->key   = $this->temporaryStack;
                                    $this->value = null;
                                break;

                                default:
                                    throw new \Exception("Too many values on line: {$line['line']}");
                                break;
                            }

                            $this->temporaryStack = "";
                        }
                        continue;
                    break;

                    case "{":
                        if ( !$this->temporaryStack ) {
                            if ( $this->key && !$this->value ) {
                                $this->temporaryStack = $this->key;
                                $this->key            = null;
                            }
                            else {
                                throw new \Exception("Unexpected \"{\" character on line: {$line['line']}");
                            }
                        }

                        $this->pushStack($this->temporaryStack);
                        $this->temporaryStack = "";
                        continue;
                    break;

                    case "}":
                        return ($this->popStack)();
                    break;
                }

                if ( $this->isInQuotes ) {
                    throw new \Exception("Unmatched close quotation on line: {$line['line']}");
                }

                if ( !empty($this->temporaryStack) ) {
                    $this->temporaryStack = "";
                }

                if ( $this->key && $this->value === null ) {
                    $this->temporaryStack = $this->key;
                }
                elseif( $this->key !== null && $this->value !== null ) {
                     $this->currentResult[ $this->key ] = $this->value;
                }

                $this->key         = null;
                $this->value       = null;
                $this->isInComment = false;
            }

        }

        return $this->result;
    }

    protected function pushStack(string $keyName) : void
    {
        $popStack     = $this->popStack;
        $parentResult = $this->currentResult;

        $this->stack[] = $keyName;

        $this->currentResult[ $keyName ] = [
            "values" => []
        ];

        $this->currentResult = $this->currentResult[ $keyName ];

        $this->popStack = function () use ($popStack, $parentResult) {
            array_pop($this->stack);

            $this->popStack      = $popStack;
            $this->currentResult = $parentResult;
        };

    }

    protected function rootPopStack () {
        throw new \Exception('Unexpected "}"');
    }
}
