<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

require_once __DIR__.'/exceptions.php';
require_once __DIR__.'/IModule.php';
require_once __DIR__.'/ICustomers.php';
require_once __DIR__.'/ILocale.php';
require_once __DIR__.'/Escape.php';
require_once __DIR__.'/Strict.php';
require_once __DIR__.'/DIContainer.php';
require_once __DIR__.'/Compress.php';
require_once __DIR__.'/Json.php';
require_once __DIR__.'/JsonResponse.php';
require_once __DIR__.'/Key.php';
require_once __DIR__.'/Translator.php';
require_once __DIR__.'/Buffer.php';
require_once __DIR__.'/Iterator.php';
require_once __DIR__.'/ISettings.php';
require_once __DIR__.'/Settings.php';
require_once __DIR__.'/Synchronize.php';
require_once __DIR__.'/Headers.php';
require_once __DIR__.'/ProxyActions.php';
require_once __DIR__.'/LocaleSimple.php';
require_once __DIR__.'/LocaleIntl.php';
require_once __DIR__.'/Customers.php';
require_once __DIR__.'/Helpers.php';

require_once __DIR__.'/Hook/Channel/IChannel.php';
require_once __DIR__.'/Hook/Channel/DefaultChannel.php';
require_once __DIR__.'/Hook/Channel/Sms.php';
require_once __DIR__.'/Hook/Variables.php';
require_once __DIR__.'/Hook/ILoad.php';
require_once __DIR__.'/Hook/Hook.php';
require_once __DIR__.'/Hook/Settings.php';

require_once __DIR__.'/Database/IDatabase.php';
require_once __DIR__.'/Database/Result.php';

require_once __DIR__.'/IO/exceptions.php';
require_once __DIR__.'/IO/ConnectionFactory.php';
require_once __DIR__.'/IO/IConnection.php';
require_once __DIR__.'/IO/Request.php';
require_once __DIR__.'/IO/Response.php';
require_once __DIR__.'/IO/FSock.php';
require_once __DIR__.'/IO/cUrl.php';
require_once __DIR__.'/IO/HttpHeaders.php';
require_once __DIR__.'/IO/Key.php';

require_once __DIR__.'/Api/exceptions.php';
require_once __DIR__.'/Api/Authenticator.php';
require_once __DIR__.'/Api/IRequest.php';
require_once __DIR__.'/Api/Request.php';
require_once __DIR__.'/Api/IResponse.php';
require_once __DIR__.'/Api/Response.php';
require_once __DIR__.'/Api/Api.php';

if (file_exists(__DIR__.'/Hook/IExtension.php'))
{
    require_once __DIR__.'/Hook/IExtension.php';
}
