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

