<?php
namespace Concrete\Core\Entity\Attribute\Key\Settings;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="atTextareaSettings")
 */
class TextareaSettings extends Settings
{
    /**
     * @ORM\Column(type="string")
     */
    protected $akTextareaDisplayMode = '';

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->akTextareaDisplayMode;
    }

    /**
     * @param mixed $mode
     */
    public function setMode($mode)
    {
        $this->akTextareaDisplayMode = $mode;
    }

}
