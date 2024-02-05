<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Model;

use Dbm\Classes\DataFlatfile;

class PageModel extends DataFlatfile
{
    public function Title()
    {
        return $this->dataFlatFile('title');
    }

    public function Description()
    {
        return $this->dataFlatFile('description');
    }

    public function Keywords()
    {
        return $this->dataFlatFile('keywords');
    }

    public function Content()
    {
        return $this->dataFlatFile('content', '    ');
    }
}
