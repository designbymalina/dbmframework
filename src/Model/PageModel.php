<?php
/*
 * DbM Framework
 * All code copyright Design by Malina
 * DbM: www.dbm.org.pl
 */

declare(strict_types=1);

namespace App\Model;

use Dbm\Classes\DataFlatfile;

class PageModel
{
    private $datafile;

    public function __construct()
    {
        $datafile = new DataFlatfile();
        $this->datafile = $datafile;
    }

    public function Title()
    {
        return $this->datafile->dataFlatFile('title');
    }

    public function Description()
    {
        return $this->datafile->dataFlatFile('description');
    }

    public function Keywords()
    {
        return $this->datafile->dataFlatFile('keywords');
    }

    public function Content()
    {
        return $this->datafile->dataFlatFile('content', '    ');
    }
}
