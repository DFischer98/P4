<?php

class RedirectController extends BaseController{

	public function callRedirect($given_redirect){
		/*
		* IMPLEMENT NON-EXISTENT REDIRECT CATCHING
		*/

		$called_redirect = URLRedirect::whereRedirectKey($given_redirect)->first();
		$called_redirect->hits = $called_redirect->hits + 1;
		$called_redirect->save();

		return Redirect::away($called_redirect->shortened_url);


	}

	public function testRedirect($given_redirect){
		$called_redirect = URLRedirect::whereRedirectKey($given_redirect)->first();
		$called_redirect->hits = $called_redirect->hits + 1;
		$called_redirect->save();

		echo 'Redirected';
	}

	public function homepage(){
		return View::make('index');
	}

	public function generateRedirect(){
		// Get query data 
		$url = Input::get('URL');


		
		//format for validation URL check
		$url = UrlFormatting::completeUrl($url);
		$data = array('URL' => $url);


		// Validate URL 
		$rules = array(
			'URL' => 'url'
		);


		 $urlValidator = Validator::make($data, $rules);


		if ($urlValidator->passes()) {

			//redirect key recycling
			if (!Auth::check()){
				$data = array('url' => $url);
				$rules = array('url' => 'unique:redirects,shortened_url');
				$existing_key = Validator::make($data, $rules);

				if($existing_key->fails()) {
					$redirect = URLRedirect::whereShortenedUrl($url)->first();

					/*
					* SHOULD REDIRECT TO VIEW
					*/

					return '<a href="' . URL::to('/', $redirect->redirect_key) . '">'
						. URL::to('/', $redirect->redirect_key);
				}
			}


			//generate unique redirect key, check uniqeness with validation
			$data = array('key' => str_random(6));
			$rules = array('key' => 'unique:redirects,redirect_key');
			$keyValidator = Validator::make($data, $rules);

			//If key already exists, loop until unique key is generated
			while($keyValidator->fails()){
				$data = array('key' => str_random(6));
				$keyValidator = Validator::make($data, $rules);
			}
			
			//make object & save redirect
			$redirect = new URLRedirect;
			$redirect->redirect_key = $data['key'];
			//turn stripped URL into full URL for redirecting
			$redirect->shortened_url = $url;
			$redirect->hits = 0;

			//user assignment, WIP
			$logged_in_user = False;
			
			if (Auth::check()){
				//link FK to user
			}
			else{
				$redirect->user_id = NULL;
			}

			$redirect->save();

			/*
			* SHOULD REDIRECT TO VIEW
			*/

			return '<a href="' . URL::to('/', $redirect->redirect_key) . '">'
				. URL::to('/', $redirect->redirect_key);


		}

		//returns to homepage with flash error
		else {
			return Redirect::to('/')->with('flash_message', 'Invalid URL!');;
		}

	}
}

