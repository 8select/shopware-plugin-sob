<?php

namespace EightSelect\Models;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="es_attribute_mapping")
 */
class EightSelectAttribute extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $eightSelectAttribute
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $eightSelectAttribute;

    /**
     * @var string $shopwareAttribute
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $shopwareAttribute;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEightSelectAttribute()
    {
        return $this->eightSelectAttribute;
    }

    /**
     * @param string $name
     */
    public function setShopwareAttribute($name)
    {
        $this->shopwareAttribute= $name;
    }

    /**
     * @return string
     */
    public function getShopwareAttribute()
    {
        return $this->shopwareAttribute;
    }
}
