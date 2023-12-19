<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

namespace App\Model;

use Dbm\Classes\DataFlatfileClass;

class PageModel extends DataFlatfileClass
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
