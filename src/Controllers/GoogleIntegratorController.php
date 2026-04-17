<?php

namespace halestar\FabLmsGoogleIntegrator\Controllers;

use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use App\Models\SubjectMatter\SchoolClass;
use Closure;
use halestar\FabLmsGoogleIntegrator\GoogleIntegrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class GoogleIntegratorController
{
	public function googleAuth()
	{
		//load the service
		$service = GoogleIntegrator::getService(IntegratorServiceTypes::AUTHENTICATION);
		$breadcrumb =
			[
				__('system.menu.integrators') => route('integrators.index'),
				$service->integrator->name => route('integrators.index'),
				$service->name => '#'
			];
		return view('google-integrator::auth', ['breadcrumb' => $breadcrumb, 'service' => $service]);
	}

	public function oauthUpdate(Request $request, SecureVault $vault)
	{
		//do we have a client id?
		$client_id = $request->input('client_id', null);
		$secret = $request->input('client_secret', null);
		//if we have either client_id or secret defined, update the vault. There is no clear function.
		if($client_id && $client_id != '')
			$vault->store('google', 'client_id', $client_id);
		if($secret && $secret != '')
			$vault->store('google', 'client_secret', $secret);
		//finally, if we updated any of the fields, update the redirect as well.
		if(($client_id && $client_id != '') || ($secret && $secret != '') || !$vault->hasKey('google', 'redirect'))
			$vault->store('google', 'redirect', route('integrators.auth.callback', ['integrator' => 'google']));
		return redirect()
			->back()
			->with('success-status', __('google-integrator::google.oauth.update.success'));
	}
	
	public function authUpdate(Request $request)
	{
		//use avatar
		$service = GoogleIntegrator::getService(IntegratorServiceTypes::AUTHENTICATION);
		$service->data->allow_user_connection = $request->has('allow_user_connection');
		$service->data->use_avatar = $request->has('use_avatar');
		//services
		$service->data->services = $request->input('services', []);
		$service->save();
		return redirect()
			->back()
			->with('success-status', __('google-integrator::google.auth.update.success'));
		
	}

	public function authServiceUpdate(Request $request, SecureVault $vault)
	{
		//is there a file uploaded?
		if($request->hasFile('service_account'))
			$vault->storeFile($request->file('service_account'), 'google', 'service_account');
		return redirect()
			->back()
			->with('success-status', __('google-integrator::auth.service.update.success'));
	}
	
	public function work()
	{
		//load the service
		$service = GoogleIntegrator::getService(IntegratorServiceTypes::WORK);
		$breadcrumb =
			[
				__('system.menu.integrators') => route('integrators.index'),
				$service->integrator->name => route('integrators.index'),
				$service->name => '#'
			];
		//attempt a system connection
		$hasServiceAccount = $service->integrator->hasServiceAccountCredentials();
		$connection = $service->connect();
		return view('google-integrator::work',
			['breadcrumb' => $breadcrumb, 'service' => $service, 'connection' => $connection, 'hasServiceAccount' => $hasServiceAccount]);
	}
	
	public function workUpdate(Request $request)
	{
		$data = $request->validate(['service_account' => 'required|email',]);
		$service = GoogleIntegrator::getService(IntegratorServiceTypes::WORK);
		if($data['service_account'] != $service->data->service_account)
		{
			$service->data->service_account = $data['service_account'];
			$service->save();
			//since we're updating the service account, we need to disconnect.
			$service->forgetSystemConnection();
		}
		return redirect()
			->back()
			->with('success-status', __('google-integrator::google.work.update.success'));
		
	}

	public function ai()
	{
		//load the service
		$service = GoogleIntegrator::getService(IntegratorServiceTypes::AI);
		$breadcrumb =
			[
				__('system.menu.integrators') => route('integrators.index'),
				$service->integrator->name => route('integrators.index'),
				$service->name => '#'
			];
		//attempt a system connection
		$connection = $service->connect();
		return view('google-integrator::ai',
			['breadcrumb' => $breadcrumb, 'service' => $service, 'connection' => $connection]);
	}

	public function updateAi(Request $request)
	{
		$service = GoogleIntegrator::getService(IntegratorServiceTypes::AI);
		$data = Validator::make($request->all(),
			[
				'gemini_api' => [
					'required',
					function (string $attribute, mixed $value, Closure $fail) use ($service)
					{
						if (!$service->testConnection($value))
							$fail(__('integrators.local.ai.connect.error'));
					},
				],
			])->validate();
		$service->registerConnection(null, [ 'api_key' => Crypt::encryptString($data['gemini_api'])]);
		return redirect()
			->back()
			->with('success-status', __('google-integrator::google.ai.connect.success'));
	}
	
	public function registerAi(Request $request)
	{
		$person = auth()->user();
		$service = GoogleIntegrator::getService(IntegratorServiceTypes::AI);
		$breadcrumb =
			[
				__('people.profile.mine') => route('people.show', $person->school_id),
				__('google-integrator::google.services.ai') => '#'
			];
		$connection = $service->connect($person);
		return view('google-integrator::ai-registration', ['breadcrumb' => $breadcrumb, 'connection' => $connection]);
	}
	
	public function updateAiRegistration(Request $request)
	{
		$service = GoogleIntegrator::getService(IntegratorServiceTypes::AI);
		$data = Validator::make($request->all(),
			[
				'gemini_api' => [
					'required',
					function (string $attribute, mixed $value, Closure $fail) use ($service)
					{
						if (!$service->testConnection($value))
							$fail(__('integrators.local.ai.connect.error'));
					},
				],
			])->validate();
		$person = auth()->user();
		$service->registerConnection($person, [ 'api_key' => Crypt::encryptString($data['gemini_api'])]);
		if($service->connect($person))
			return redirect()
				->route('people.show', $person->school_id)
				->with('success-status', __('google-integrator::google.services.ai.registration.success'));
		return redirect()
			->back()
			->with('success-status', __('google-integrator::google.services.ai.registration.error'));
	}

	public function email()
	{
		$service = GoogleIntegrator::getService(IntegratorServiceTypes::EMAIL);
		$breadcrumb =
			[
				__('system.menu.integrators') => route('integrators.index'),
				$service->integrator->name => route('integrators.index'),
				$service->name => '#'
			];
		//attempt a system connection
		$hasServiceAccount = $service->integrator->hasServiceAccountCredentials();
		$connection = $service->connect();
		return view('google-integrator::email',
			['breadcrumb' => $breadcrumb, 'service' => $service, 'connection' => $connection, 'hasServiceAccount' => $hasServiceAccount]);
	}

	public function emailUpdate(Request $request)
	{
		$service = GoogleIntegrator::getService(IntegratorServiceTypes::EMAIL);
		$data = Validator::make($request->all(),
			[
				'account' => 'required|email',
			])->validate();
		//register the connection
		$service->registerConnection(null, $data);
		return redirect()
			->back()
			->with('success-status', __('google-integrator::google.services.email.success'));
	}
}