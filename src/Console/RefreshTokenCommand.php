<?php
namespace LaravelAds\Console;

use Exception;
use Illuminate\Console\Command;
use Google\Auth\OAuth2;
use Google\Auth\CredentialsLoader;

use Microsoft\BingAds\Auth\AuthorizationData;
use Microsoft\BingAds\Auth\OAuthTokenRequestException;
use Microsoft\BingAds\Auth\OAuthWebAuthCodeGrant;

class RefreshTokenCommand extends Command
{

    /**
     * Console command signature
     *
     * @var string
     */
    protected $signature = 'laravelads:token:generate {--service=}';

    /**
     * Description
     *
     * @var string
     */
    protected $description = 'Generate a refresh token for BingAds';

    /**
     * Generate refresh token
     *
     */
    public function handle()
    {
        $service = strtolower($this->option('service'));

        switch ($service) {
            case 'bingads':
                $this->bingAdsRefresh();
            break;
            default:
                $this->error("Error: --service option is required. (Use BingAds)");
        }
    }

    /**
     * bingAdsRefresh
     *
     */
    protected function bingAdsRefresh()
    {
        $config = config('bing-ads') ?? [];

        // check if the config is right
        if (!$config) {
            return $this->error('Your Bing Ads config is not setup properly. Aborting.');
        }

        $clientId       = $config['clientId'];
        $clientSecret   = $config['clientSecret'];
        $developerToken = $config['developerToken'];
        $redirectUri    = $config['redirect_uri'] ?? 'https://login.microsoftonline.com/common/oauth2/nativeclient';

        $authentication = (new OAuthWebAuthCodeGrant())
            ->withClientId($clientId)
            ->withClientSecret($clientSecret)
            ->withRedirectUri($redirectUri)
            ->withState(rand(0, 999999999));

        $AuthorizationData = (new AuthorizationData())
            ->withAuthentication($authentication)
            ->withDeveloperToken($developerToken);

        $this->comment("Please sign in to your Bing Ads account, and open following url:");
        $this->line(str_replace(' ','%20',$AuthorizationData->Authentication->GetAuthorizationEndpoint()));

        $accessToken = $this->ask('Insert the FULL URL that you were redirected to (after you approve the access):');

        try {
            $AuthorizationData->Authentication->RequestOAuthTokensByResponseUri($accessToken);
        } catch (Exception $exception) {
            return $this->error($exception->getMessage());
        }

        $this->comment('Copy the refresh token and paste the value on BING_REFRESH_TOKEN in your .env');

        // Print refresh token
        $this->line(sprintf(
            'Refresh token: "%s"',
            $AuthorizationData->Authentication->OAuthTokens->RefreshToken
        ));
    }
}
