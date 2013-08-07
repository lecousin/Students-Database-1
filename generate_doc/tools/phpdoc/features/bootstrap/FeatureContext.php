<?php
/**
 * phpDocumentor
 *
 * PHP Version 5.3
 *
 * @copyright 2010-2013 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

use Behat\Behat\Context\BehatContext;

/**
 * Context class for the phpDocumentor Features.
 */
class FeatureContext extends BehatContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets it's own context object.
     *
     * @param string[] $parameters context parameters (set them up through
     *    behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->useContext('execution', new \ExecutionContext());
        $this->useContext('checkstyle', new \CheckstyleContext());
    }

    /**
     * Returns the output as generated by phpDocumentor.
     *
     * @return string
     */
    public function getOutput()
    {
        /** @var \ExecutionContext $executionContext */
        $executionContext = $this->getSubcontext('execution');

        return $executionContext->getOutput();
    }

    /**
     * Changes the current working directory to that of phpDocumentor's root.
     *
     * @Given /^I am in the phpDocumentor root directory$/
     *
     * @return void
     */
    public function iAmInThePhpdocumentorRootDirectory()
    {
        chdir(__DIR__.'/../..');
    }

    /**
     * Verifies whether one of the log entries is the same as the given.
     *
     * Please note that this method exactly checks the given except for leading
     * and trailing spaces and control characters; those are stripped first.
     *
     * @param string $string
     *
     * @Then /^I should get a log entry "([^"]*)"$/
     *
     * @throws \Exception if the condition is not fulfilled
     *
     * @return void
     */
    public function iShouldGetALogEntry($string)
    {
        $found = false;
        foreach (explode("\n", $this->getOutput()) as $line) {
            if (trim($line) == $string) {
                $found = true;
            }
        }

        if (!$found) {
            throw new \Exception(
                "Actual output is:\n" . $this->getOutput()
            );
        }
    }

    /**
     * Verifies whether a specific log entry was not thrown.
     *
     * @param string $string
     *
     * @Then /^I should not get a log entry "([^"]*)"$/
     *
     * @throws \Exception if the condition is not fulfilled
     *
     * @return void
     */
    public function iShouldNotGetALogEntry($string)
    {
        $found = false;
        foreach (explode("\n", $this->getOutput()) as $line) {
            if (trim($line) == $string) {
                $found = true;
            }
        }

        if ($found) {
            throw new \Exception(
                "Actual output is:\n" . $this->getOutput()
            );
        }
    }

    /**
     * Verifies whether a log entry contains the given substring.
     *
     * @param string $string
     *
     * @Then /^I should get a log entry containing "([^"]*)"$/
     *
     * @throws \Exception if the condition is not fulfilled
     *
     * @return void
     */
    public function iShouldGetALogEntryContaining($string)
    {
        $found = false;
        foreach (explode("\n", $this->getOutput()) as $line) {
            if (strpos(trim($line), $string) !== false) {
                $found = true;
            }
        }

        if (!$found) {
            throw new \Exception(
                "Actual output is:\n" . $this->getOutput()
            );
        }
    }

    /**
     * Verifies whether none of the log entries contain the given substring.
     *
     * @param string $string
     *
     * @Then /^I should not get a log entry containing "([^"]*)"$/
     *
     * @throws \Exception if the condition is not fulfilled
     *
     * @return void
     */
    public function iShouldNotGetALogEntryContaining($string)
    {
        $found = false;
        foreach (explode("\n", $this->getOutput()) as $line) {
            if (strpos(trim($line), $string) !== false) {
                $found = true;
            }
        }

        if ($found) {
            throw new \Exception(
                "Actual output is:\n" . $this->getOutput()
            );
        }
    }

    /**
     * Verifies whether an exception is thrown during excecution.
     *
     * @param string $exception_text
     *
     * @Then /^I should get an exception "([^"]*)"$/
     *
     * @throws \Exception if the condition is not fulfilled
     *
     * @return void
     */
    public function iShouldGetAnException($exception_text)
    {
        $this->iShouldGetALogEntry('[Exception]');
        $this->iShouldGetALogEntry($exception_text);
    }

    /**
     * Verifies whether an exception is thrown during execution containing a
     * substring.
     *
     * @param string $exception_text
     *
     * @Then /^I should get an exception containing "([^"]*)"$/
     *
     * @throws \Exception if the condition is not fulfilled
     *
     * @return void
     */
    public function iShouldGetAnExceptionContaining($exception_text)
    {
        $this->iShouldGetALogEntry('[Exception]');
        $this->iShouldGetALogEntryContaining($exception_text);
    }
}
