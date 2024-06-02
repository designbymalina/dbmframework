<?php

declare(strict_types=1);

namespace App\Command;

/**
 * The Command class is used in application/console.php
 */
class ConsoleCommand
{
    public function executeCommand(): void
    {
        echo $this->exampleCode();
    }

    private function exampleCode(): string
    {
        return "\033[42mOK! \033[0m \n";
        //return "\033[41mERROR! \033[0m \n";
    }
}
