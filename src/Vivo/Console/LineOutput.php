<?php
namespace Vivo\Console;

/**
 * Output
 * Console output object accumulating text output sent from CLI controllers
 */
class LineOutput
{
    /**
     * Character used to concatenate the lines in the string output
     * @var string
     */
    protected $newLineChar  = PHP_EOL;

    /**
     * Number of line breaks added before the output
     * @var int
     */
    protected $leadingLineBreaks    = 1;

    /**
     * Number of line breaks added after the output
     * @var int
     */
    protected $trailingLineBreaks   = 1;

    /**
     * Lines of output text
     * @var string[]
     */
    protected $lines    = array();

    /**
     * Output lines immediately using echo?
     * @var bool
     */
    protected $immediate    = false;

    /**
     * Constructor
     * @param bool $immediate Output lines immediately using echo?
     */
    public function __construct($immediate = false)
    {
        $this->immediate    = $immediate;
    }

    /**
     * Clears all data
     */
    public function clear()
    {
        $this->lines    = array();
    }

    /**
     * Adds a line of output
     * @param string $line
     */
    public function line($line)
    {
        if ($this->immediate) {
            echo $line . $this->newLineChar;
        } else {
            $this->lines[]  = $line;
        }
    }

    /**
     * Adds the specified number of line breaks
     * @param int $count
     */
    public function newLine($count = 1)
    {
        if ($this->immediate) {
            for ($i = 0; $i < $count; $i++) {
                echo $this->newLineChar;
            }
        } else {
            for ($i = 0; $i < $count; $i++) {
                $this->lines[]  = '';
            }
        }
    }

    /**
     * Returns the buffered data as string
     * @return string
     */
    public function toString()
    {
        $output = '';
        for ($i = 0; $i < $this->leadingLineBreaks; $i++) {
            $output .= $this->newLineChar;
        }
        $output .= implode($this->newLineChar, $this->lines);
        for ($i = 0; $i < $this->trailingLineBreaks; $i++) {
            $output .= $this->newLineChar;
        }
        return $output;
    }

    /**
     * Returns the buffered data as string
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
