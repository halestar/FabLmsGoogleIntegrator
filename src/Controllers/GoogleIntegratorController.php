<?php

namespace halestar\FabLmsGoogleIntegrator\Controllers;

use App\Classes\Integrators\SecureVault;
use App\Enums\IntegratorServiceTypes;
use halestar\FabLmsGoogleIntegrator\GoogleIntegrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class GoogleIntegratorController
{
	public function integrator()
	{
		$integrator = GoogleIntegrator::autoload();
		$breadcrumb =
			[
				__('system.menu.integrators') => route('integrators.index'),
				$integrator::integratorName() => '#',
			];
		return view('google-integrator::integrator', ['breadcrumb' => $breadcrumb, 'integrator' => $integrator]);
	}
	
	public function update(Request $request, SecureVault $vault)
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
		//is there a file to upload
		if($request->hasFile('service_account'))
			$vault->storeFile($request->file('service_account'), 'google', 'service_account');
		return redirect()
			->back()
			->with('success-status', __('google-integrator::google.update.success'));
	}
	
	public function auth()
	{
		//load the service
		$service = GoogleIntegrator::autoload()
		                           ->services()
		                           ->ofType(IntegratorServiceTypes::AUTHENTICATION)
		                           ->first();
		$breadcrumb =
			[
				__('system.menu.integrators') => route('integrators.index'),
				$service->integrator->name => route('integrators.google.integrator'),
				$service->name => '#'
			];
		return view('google-integrator::auth', ['breadcrumb' => $breadcrumb, 'service' => $service]);
	}
	
	public function authUpdate(Request $request)
	{
		//use avatar
		$service = GoogleIntegrator::autoload()
		                           ->services()
		                           ->ofType(IntegratorServiceTypes::AUTHENTICATION)
		                           ->first();
		$service->data->use_avatar = $request->has('use_avatar');
		//services
		$service->data->autoconnect = $request->input('autoconnect', []);
		$service->save();
		return redirect()
			->back()
			->with('success-status', __('google-integrator::google.auth.update.success'));
		
	}
	
	public function work()
	{
		//load the service
		$service = GoogleIntegrator::autoload()
		                           ->services()
		                           ->ofType(IntegratorServiceTypes::WORK)
		                           ->first();
		$breadcrumb =
			[
				__('system.menu.integrators') => route('integrators.index'),
				$service->integrator->name => route('integrators.google.integrator'),
				$service->name => '#'
			];
		//attempt a system connection
		$connection = $service->connectToSystem();
		return view('google-integrator::work',
			['breadcrumb' => $breadcrumb, 'service' => $service, 'connection' => $connection]);
	}
	
	public function workUpdate(Request $request)
	{
		$data = $request->validate([
			'service_account' => 'required|email',
		]);
		$service = GoogleIntegrator::autoload()
		                           ->services()
		                           ->ofType(IntegratorServiceTypes::WORK)
		                           ->first();
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
	
	public function systemAi()
	{
		//load the service
		$service = GoogleIntegrator::autoload()
		                           ->services()
		                           ->ofType(IntegratorServiceTypes::AI)
		                           ->first();
		$breadcrumb =
			[
				__('system.menu.integrators') => route('integrators.index'),
				$service->integrator->name => route('integrators.google.integrator'),
				$service->name => '#'
			];
		//attempt a system connection
		$connection = $service->connectToSystem();
		return view('google-integrator::ai',
			['breadcrumb' => $breadcrumb, 'service' => $service, 'connection' => $connection]);
	}
	
	public function systemAiUpdate(Request $request, SecureVault $vault)
	{
		$gemini_api = $request->input('gemini_api', null);
		if($gemini_api && $gemini_api != '')
			$vault->store('google', 'gemini_api', $gemini_api);
		$service = GoogleIntegrator::autoload()
		                           ->services()
		                           ->ofType(IntegratorServiceTypes::AI)
		                           ->first();
		if(!$request->has('allow_user_ai'))
		{
			$service->data->allow_user_ai = false;
			$service->data->allow_user_system_ai = false;
			$service->data->allow_user_own_ai = false;
		}
		else
		{
			$service->data->allow_user_ai = true;
			$allow = $request->input('user_allow_ai', 'allow_user_own_ai');
			$service->data->allow_user_system_ai = ($allow == 'allow_user_system_ai');
			$service->data->allow_user_own_ai = ($allow == 'allow_user_own_ai');
		}
		$service->save();
		return redirect()
			->back()
			->with('success-status', __('google-integrator::google.ai.update.success'));
		
	}
	
	public function registerAi(Request $request)
	{
		$person = auth()->user();
		$breadcrumb =
			[
				__('people.profile.mine') => route('people.show', $person->school_id),
				__('google-integrator::google.services.ai') => '#'
			];
		return view('google-integrator::ai-registration', ['breadcrumb' => $breadcrumb]);
	}
	
	public function updateAiRegistration(Request $request)
	{
		$data = $request->validate([
			'client_secret' => 'required',
		]);
		$service = GoogleIntegrator::autoload()
		                           ->services()
		                           ->ofType(IntegratorServiceTypes::AI)
		                           ->first();
		$person = auth()->user();
		$registrationData =
			[
				'className' => $service->getConnectionClass(),
				'data' => ['key' => Crypt::encryptString($data['client_secret'])],
				'enabled' => true,
			];
		$service->registerServiceConnection($person, $registrationData);
		if($service->connect($person))
			return redirect()
				->route('people.show', $person->school_id)
				->with('success-status', __('google-integrator::google.services.ai.registration.success'));
		return redirect()
			->back()
			->with('success-status', __('google-integrator::google.services.ai.registration.error'));
	}
}