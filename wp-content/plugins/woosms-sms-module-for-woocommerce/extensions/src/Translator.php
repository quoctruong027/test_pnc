<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

class Translator extends Strict
{
    /** @var ISettings */
    private $settings;

    /** @var string|null|bool */
    private $iso = null;

    /** @var array */
    private $translates = array();


    public function __construct(ISettings $settings)
    {
        $this->settings = $settings;
    }


    public function init($iso = null)
    {
        $this->iso = $iso ? $iso : $this->settings->load('main:language', 'en');

        if ($this->iso)
        {
            $translates = (array) $this->settings->load('translates:'.$this->iso);

            if ($translates && is_array($translates))
            {
                $this->translates = $translates;
            }
        }
    }


    public function setLanguage($iso)
    {
        $this->settings->set('main:language', $iso, array('type' => 'string'));
    }


    public function translate($key, $default = null)
    {
        if ($this->iso === null)
        {
            $this->init();
        }

        if (isset($this->translates[$key]))
        {
            return $this->translates[$key];
        }

        if ($default === null)
        {
            return ucfirst(str_replace('_', ' ', $key));
        }

        return $default;
    }
}
