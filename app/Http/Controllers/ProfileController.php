<?php
namespace App\Http\Controllers;

use Image;
use Input;
use Redirect;
use App\Models\Location;
use View;
use Auth;
use App\Helpers\Helper;
use App\Models\Setting;
use Gate;

/**
 * This controller handles all actions related to User Profiles for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class ProfileController extends Controller
{
    /**
    * Returns a view with the user's profile form for editing
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    * @return \Illuminate\Contracts\View\View
     */
    public function getIndex()
    {
        $user = Auth::user();
        $location_list = Helper::locationsList();
        return view('account/profile', compact('user'))->with('location_list', $location_list);
    }

    /**
    * Validates and stores the user's update data.
    *
    * @author [A. Gianotto] [<snipe@snipe.net>]
    * @since [v1.0]
    * @return \Illuminate\Http\RedirectResponse
     */
    public function postIndex()
    {

        $user = Auth::user();
        $user->first_name = Input::get('first_name');
        $user->last_name  = Input::get('last_name');
        $user->website    = Input::get('website');
        $user->location_id    = Input::get('location_id');
        $user->gravatar   = Input::get('gravatar');
        $user->locale = Input::get('locale');

        if ((Gate::allows('self.two_factor')) && ((Setting::getSettings()->two_factor_enabled=='1') && (!config('app.lock_passwords')))) {
            $user->two_factor_optin = Input::get('two_factor_optin', '0');
        }
        
        if (Input::file('avatar')) {
            $image = Input::file('avatar');
            $file_name = str_slug($user->first_name."-".$user->last_name).".".$image->getClientOriginalExtension();
            $path = public_path('uploads/avatars/'.$file_name);
            Image::make($image->getRealPath())->resize(84, 84)->save($path);
            $user->avatar = $file_name;
        }

        if (Input::get('avatar_delete') == 1 && Input::file('avatar') == "") {
            $user->avatar = null;
        }

        if ($user->save()) {
            return redirect()->route('profile')->with('success', 'Account successfully updated');
        }
        return redirect()->back()->withInput()->withErrors($user->getErrors());
    }


    /**
     * Returns a page with the API token generation interface.
     *
     * We created a controller method for this because closures aren't allowed
     * in the routes file if you want to be able to cache the routes.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return View
     */
    public function api() {
        return view('account/api');
    }
}
