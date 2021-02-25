<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

class ProxyActions extends Strict
{
    /** @var IO\IConnection */
    private $connection;

    /** @var IModule */
    private $module;

    /** @var Synchronize */
    private $synchronize;

    /** @var ISettings */
    private $settings;

    /** @var Translator */
    private $translator;

    /** @var ICustomers */
    private $customers;


    public function __construct(IO\IConnection $connection, IModule $module, Synchronize $synchronize, ISettings $settings, Translator $translator, ICustomers $customers)
    {
        $this->connection = $connection;
        $this->module = $module;
        $this->synchronize = $synchronize;
        $this->settings = $settings;
        $this->translator = $translator;
        $this->customers = $customers;
    }

    public function login(array $data)
    {
        $response = $this->connection->run(new IO\Request($this->module->getUrl('/module/sign/in'), $data));

        $login = (array) $response->get('::login');

        if (isset($login['application_id']) && isset($login['application_token']))
        {
            $this->settings->set('static:application_id', $login['application_id'], array('type' => 'int'));
            $this->settings->set('static:application_token', $login['application_token']);
            $this->settings->set('static:synchronize', 0);
            return isset($login['application_token_temp']) ? $login['application_token_temp'] : 'guest';
        }
        return $response;
    }


    public function logout()
    {
        $this->settings->delete('static:application_token');
    }


    public function register(array $data)
    {
        $response = $this->connection->run(new IO\Request($this->module->getUrl('/module/sign/up'), $data));

        $register = (array) $response->get('::register');

        if (isset($register['application_id']) && isset($register['application_token']))
        {
            $this->settings->set('static:application_id', $register['application_id'], array('type' => 'int'));
            $this->settings->set('static:application_token', $register['application_token']);
            $this->settings->set('static:synchronize', 0);
            return isset($register['application_token_temp']) ? $register['application_token_temp'] : 'guest';
        }
        return $response;
    }


    public function authenticate()
    {
        try
        {
            return $this->connection->run(new IO\Request($this->module->getUrl('/widget/authenticate')));
        }
        catch (IO\AuthenticateException $e)
        {
            $this->settings->delete('static:application_token');
            throw $e;
        }
    }


    public function saveSettings(array $settings)
    {
        if (isset($settings['delete_db']))
        {
            $this->settings->set('main:delete_db', $settings['delete_db'], array('type' => 'int'));
        }

        if (isset($settings['language']))
        {
            $this->translator->setLanguage($settings['language']);
        }

        if (isset($settings['language_mutation']))
        {
            $this->settings->set('main:language_mutation', $settings['language_mutation'], array('type' => 'int'));
            $this->settings->set('static:synchronize', 0, array('type' => 'int'));
        }
    }


    public function saveCustomerNotifications(array $data)
    {
        $self = $this;

        return $this->synchronize->synchronize(function($module_settings) use ($self, $data)
        {
            return $self->connection->run(new IO\Request($self->module->getUrl('/module/hook/customer'),
                array_merge(array("__synchronize" => $module_settings), $data),
                true
            ));
        });
    }


    public function saveAdminNotifications(array $data)
    {
        $self = $this;

        return $this->synchronize->synchronize(function($module_settings) use ($self, $data)
        {
            return $self->connection->run(new IO\Request($self->module->getUrl('/module/hook/admin'),
                array_merge(array("__synchronize" => $module_settings), $data),
                true
            ));
        });
    }


    public function loadCustomersCount($application_id, $id, $type = 'load', array $data = array())
    {
        switch ($type)
        {
            case 'addFilter':
                $response = $this->connection->run(new IO\Request($this->module->getUrl('/module/sms-campaign/add-filter/'.(int)$id), $data, false, 60));
                break;
            case 'removeFilter':
                $response = $this->connection->run(new IO\Request($this->module->getUrl('/module/sms-campaign/remove-filter/'.(int)$id), $data, false, 60));
                break;
            case 'load':
            default:
                $response = $this->connection->run(new IO\Request($this->module->getUrl('/module/sms-campaign/load/'.(int)$id), array(), false, 60));
                break;
        }

        $campaign = $response->get('campaign::campaign');

        $response->set('campaign::module_recipients', $this->customers->loadCount(
            isset($campaign['filter_module']) && isset($campaign['filter_module'][$application_id]) ?
                $campaign['filter_module'][$application_id] :
                array()
        ));

        return $response;
    }


    public function saveModuleCustomers($application_id, $campaign_id)
    {
        $response = $this->loadCustomersCount($application_id, $campaign_id);

        $campaign = $response->get('campaign::campaign');

        return $this->connection->run(new IO\Request($this->module->getUrl('/module/sms-campaign/save/'.(int) $campaign_id), array(
            'customers' => $this->customers->load(
                isset($campaign['filter_module']) && isset($campaign['filter_module'][$application_id]) ?
                    $campaign['filter_module'][$application_id] :
                    array()
            )
        ), true, 120));
    }
}
