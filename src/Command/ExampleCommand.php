<?php

declare(strict_types=1);

namespace App\Command;

/**
 * Klasa Command uzywane jest w application/console.php
 */
class ExampleCommand
{
    public function executeCommand(): void
    {
        echo $this->exampleCode();
    }

    private function exampleCode(): string
    {
        return "\033[42mOK! \033[0m \n";
        //return "\033[41mError! \033[0m \n";
    }
}
