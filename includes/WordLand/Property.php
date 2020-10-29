<?php
namespace WordLand;

use WordLand\Abstracts\Data;
use Ramphor\FriendlyNumbers\Parser;
use Ramphor\FriendlyNumbers\Scale;
use Ramphor\FriendlyNumbers\Locale;

class Property extends Data
{
    public $ID;
    public $name;
    public $description;
    public $content;
    public $price = 0;
    public $unitPrice = 0;
    public $size = 0;
    public $images = array();

    protected $style;

    public $metas = array(
        'clean_price' => null,
        'clean_size' => null,
        'goto_detail' => null,
    );

    public function setMeta($key, $value)
    {
        $this->metas[$key] = $value;
    }

    public function getMeta($key, $defaultValue = null)
    {
        switch ($key) {
            case 'clean_price':
                return $this->makeCleanPriceHtml();
            case 'clean_size':
                return $this->makeCleanSizeHtml();
            case 'goto_detail':
                return get_permalink();
            default:
                if (isset($this->metas[$key])) {
                    return $this->metas[$key];
                }
                return $defaultValue;
        }
    }

    public function is_visible()
    {
        return true;
    }

    public function getCurrency()
    {
        return 'đ';
    }

    public function getSizeUnit()
    {
        return 'm2';
    }

    public function makeCleanPriceHtml()
    {
        if (is_null($this->metas['clean_price'])) {
            $this->metas['clean_price'] = new Parser($this->price, new Scale(array(
                'scale' => 'currency',
                'unit' => $this->getCurrency()
            )), new Locale(get_locale()));
        }


        $clean_price = array_get($this->metas, 'clean_price', null);
        if (!$clean_price) {
            return '';
        }
        $parsed = $clean_price->toArray();
        return sprintf(
            '<span class="val">%s</span><span class="unit">%s</span>',
            array_get($parsed, 'value', 0),
            array_get($parsed, 'unit', $this->getCurrency())
        );
    }

    public function makeCleanSizeHtml()
    {
        if (is_null($this->metas['clean_size'])) {
            $this->metas['clean_size'] = new Parser($this->price, new Scale(array(
                'scale' => 'metric',
                'unit' => $this->getCurrency()
            )), new Locale(get_locale()));
        }

        $clean_size = array_get($this->metas, 'clean_size', null);
        if (!$clean_size) {
            return '';
        }
        $parsed = $clean_size->toArray();

        return sprintf(
            '<span class="val">%s</span><span class="unit">%s</span>',
            array_get($parsed, 'value', 0),
            array_get($parsed, 'unit', $this->getSizeUnit())
        );
    }

    public function setStyle($style)
    {
        $this->style = $style;
    }

    public function getStyle()
    {
        return $this->style;
    }
}
