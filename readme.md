## Client to interact with the Roundtable API

### Installation

- Run command: `composer require stakeholderlabs/roundtable-laravel`
- Update your .env file with the provided variables:
```dotenv
ROUNDTABLE_URL="https://roundtable.stakeholderlabs.com" #The domain of your Roundtable setup
ROUNDTABLE_API_URL="https://api.roundtable.stakeholderlabs.com" #The domain of your Roundtable API setup. Can be ommited.
ROUNDTABLE_SECRET_KEY=<private key from the dashboard>
ROUNDTABLE_PUBLIC_KEY=<public key from the dashboard>
```

### Usage

#### Obtain token from Roundtable™ API:

To get a user authorization link in Roundtable™, you must request the API via the obtainTokenUrl method. 
The result will be a unique link for each user, opening which the user will be prompted to enter the login and password from the bank account of his choice:

```php
    use Shl\RoundTable\Clients\Client as RoundtableClient;
    
    $client = app(RoundtableClient::class);
    
    if ($tokenUrl = $client->obtainTokenUrl('email@example.com', 'Joe Doe')) {
        return Redirect::to($tokenUrl);
    }
```


#### Decrypt Roundtable™ payload

After the user is authorized, it will be returned to the URL specified in the application settings. If you want to validate the user's connection, it is necessary to decrypt payload passed in the GET parameters. Rountable™ returns one parameter: payload. To decrypt it use decryptRoundtablePayload method:

```php
    use Shl\RoundTable\Clients\Client as RoundtableClient;

    $payload = request()->get('payload');
    
    $client = app(RoundtableClient::class);

    if($data = $client->decryptRoundtablePayload($payload)) {
        // Save roundtable customer id to the database as the confirmation of connection to Roundtable™
        // e.g. $user->update(['roundtable_id' => $data->getCustomerId()]); 
    }
```
