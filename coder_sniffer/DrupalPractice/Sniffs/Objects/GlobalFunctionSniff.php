<?php
/**
 * DrupalPractice_Sniffs_Objects_GlobalFunctionSniff.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Checks that gloabl functions like t() are not used in forms or controllers.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class DrupalPractice_Sniffs_Objects_GlobalFunctionSniff implements PHP_CodeSniffer_Sniff
{

    /**
     * List of global functions that should not be called.
     *
     * @var string[]
     */
    protected $functions = array('t' => '$this->t()');


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(T_STRING);

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                         in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // We are only interested in function calls, not in the global scope.
        if (isset($this->functions[$tokens[$stackPtr]['content']]) === false
            || isset($tokens[($stackPtr + 1)]) === false
            || $tokens[($stackPtr + 1)]['code'] !== T_OPEN_PARENTHESIS
            || empty($tokens[$stackPtr]['conditions']) === true
        ) {
            return;
        }

        // Check if the class extends another class and get the name of the class
        // that is extended.
        $classPtr   = key($tokens[$stackPtr]['conditions']);
        $extendsPtr = $phpcsFile->findNext(T_EXTENDS, ($classPtr + 1), $tokens[$classPtr]['scope_opener']);
        if ($extendsPtr === false) {
            return;
        }

        $extendsNamePtr = $phpcsFile->findNext(T_STRING, ($extendsPtr + 1), $tokens[$classPtr]['scope_opener']);

        if ($extendsNamePtr === false || in_array($tokens[$extendsNamePtr]['content'], DrupalPractice_Sniffs_Objects_GlobalDrupalSniff::$baseClasses) === false
        ) {
            return;
        }

        $warning = '%s() calls should be avoided in classes, use dependency injection and %s instead';
        $data    = array(
                    $tokens[$stackPtr]['content'],
                    $this->functions[$tokens[$stackPtr]['content']],
                   );
        $phpcsFile->addWarning($warning, $stackPtr, 'GlobalFunction', $data);

    }//end process()


}//end class
