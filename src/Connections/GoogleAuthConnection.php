<?php

namespace halestar\FabLmsGoogleIntegrator\Connections;

use App\Enums\IntegratorServiceTypes;
use App\Models\Integrations\Connections\AuthConnection;
use App\Models\People\Person;
use Carbon\Carbon;
use halestar\FabLmsGoogleIntegrator\Enums\GoogleIntegrationServices;
use halestar\FabLmsGoogleIntegrator\GoogleIntegrator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthConnection extends AuthConnection
{
	protected static array $instanceDefaults =
		[
			'google_id' => null,
			'avatar' => null,
			'token' => null,
			'scopes' => [],
			'oauth_token' => null,
			'oauth_refresh_token' => null,
			'oauth_expires_in' => null,
		];
	
	/**
	 * @inheritDoc
	 */
	public static function requiresPassword(): bool
	{
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	static public function loginButton(): string
	{
		$html = '<img alt="Sign in with Google" src="' . asset('/vendor/google-integrator/login_btn.svg') . '" /></a>';
		return Blade::render($html);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function requiresRedirection(): bool
	{
		return true;
	}
	
	public static function callback(): \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse|null
	{
		//load the user that just logged in from socialite
		$gUser = Socialite::driver('google')
		                  ->user();
		//find the user in the database based on the email.
		$user = Person::where('email', $gUser->email)->first();
		//if there's no user, we go back to the login place
		if(!$user)
			return redirect()->route('login');

		//are we authenticating to log in? or to establish an integration to this integrator (registering)?
		if($user->authConnection instanceof GoogleAuthConnection)
			$connection = $user->authConnection; //logging in
		else
			$connection = GoogleIntegrator::getService(IntegratorServiceTypes::AUTHENTICATION)->connect($user); //registering

		//no matter what we're doing, if we have a connection, we need to update the data.
		if($connection)
		{
			//if we have a refresh token, we did a re-auth
			if ($gUser->refreshToken)
			{
				$connection->data->google_id = $gUser->getId();
				$connection->data->avatar = $gUser->getAvatar();
				$connection->data->oauth_refresh_token = $gUser->refreshToken;
				$connection->data->oauth_expires_in = now()->addSeconds($gUser->expiresIn)->timestamp;
				$connection->data->scopes = $gUser->approvedScopes;
			}
			$connection->data->oauth_token = $gUser->token;
			$connection->save();
			//check the avatar
			if ($connection->service->data->use_avatar && $user->portrait_url != $connection->data->avatar)
			{
				$user->portrait_url = $connection->data->avatar;
				$user->save();
			}
		}

		//if we have the auth connection established, then we're logging in.
		if($user->authConnection instanceof GoogleAuthConnection)
			return redirect($user->authConnection->completeLogin($user));

		//if not, we're integrating, so connect the user to all the services that they should be connected to
		$integrator = GoogleIntegrator::autoload();
		foreach($connection->service->data->autoconnect as $service_type)
			$integrator->services()
			           ->ofType(IntegratorServiceTypes::from($service_type))
			           ->first()
			           ->connect($user);
		return redirect()->route('people.show', ['person' => $user->school_id]);
	}
	
	public function hasScope(GoogleIntegrationServices $service): bool
	{
		foreach($service->scopes() as $scope)
			if(!in_array($scope, $this->data->scopes))
				return false;
		return true;
	}

	
	/**
	 * @inheritDoc
	 */
	public function canChangePassword(): bool
	{
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canResetPassword(): bool
	{
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setPassword(string $password): bool
	{
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	function attemptLogin(string $password, bool $rememberMe, bool $autoLogin = true): bool
	{
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function verifyPassword(string $password): bool
	{
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function canSetMustChangePassword(): bool
	{
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setMustChangePassword(bool $mustChangePassword = true): void {}
	
	/**
	 * @inheritDoc
	 */
	public function mustChangePassword(): bool
	{
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public function redirect(): \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse|null
	{
		//first, does the user have a valid token?
		$scopes = [];
		foreach($this->service->data->autoconnect as $service)
			$scopes = array_merge($scopes, GoogleIntegrationServices::from($service)
			                                                        ->scopes());
		$missing = false;
		foreach($scopes as $scope)
		{
			if(!in_array($scope, $this->data->scopes))
			{
				$missing = true;
				break;
			}
		}
		if($missing || !$this->hasActiveToken())
		{
			return Socialite::driver('google')
			                ->with(
				                [
					                'login_hint' => $this->person->system_email,
					                'prompt' => 'consent',
					                'access_type' => 'offline'
				                ])
			                ->scopes($scopes)
			                ->redirect();
		}
		return Socialite::driver('google')
		                ->with(['login_hint' => $this->person->system_email, 'access_type' => 'offline'])
		                ->scopes($scopes)
		                ->redirect();
	}
	
	public function hasActiveToken(): bool
	{
		$this->refreshToken();
		return $this->data->oauth_token && $this->data->oauth_refresh_token && !$this->shouldRefresh();
	}
	
	public function refreshToken(): void
	{
		if(!$this->shouldRefresh()) return;
		if(!$this->data->oauth_refresh_token) return;
		$oldRefreshToken = $this->data->oauth_refresh_token;
		$newToken = Socialite::driver('google')
		                     ->refreshToken($oldRefreshToken);
		if(!$newToken->token || !$newToken->refreshToken) return;
		$this->data->oauth_token = $newToken->token;
		$this->data->oauth_refresh_token = $newToken->refreshToken;
		$this->data->oauth_expires_in = now()->addSeconds($newToken->expiresIn)->timestamp;
		$this->save();
	}
	
	public function shouldRefresh(): bool
	{
		return Carbon::parse($this->data->oauth_expires_in)
		             ->isPast();
	}
}