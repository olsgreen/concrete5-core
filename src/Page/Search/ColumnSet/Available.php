<?php
namespace Concrete\Core\Page\Search\ColumnSet;

use Concrete\Core\Page\Search\ColumnSet\Column\SitemapDisplayOrder;
use Concrete\Core\Search\Column\Column;

class Available extends DefaultSet
{
    protected $attributeClass = 'CollectionAttributeKey';

    public function __construct()
    {
        $this->addColumn(new SitemapDisplayOrder());
        parent::__construct();
    }
}
