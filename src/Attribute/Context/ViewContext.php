<?php
namespace Concrete\Core\Attribute\Context;

use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Value\AbstractValue;

class ViewContext extends Context
{


    public function render(Key $key, AbstractValue $value = null)
    {
       // echo $value->getDisplayValue();
    }


}
