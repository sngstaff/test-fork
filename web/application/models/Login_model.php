<?php

namespace Model;

use App;
use Exception;
use System\Core\CI_Model;

/**
 * Login model
 */
class Login_model extends CI_Model {

    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Logout user
     *
     * @return void
     */
    public static function logout()
    {
        App::get_ci()->session->unset_userdata('id');
    }

    /**
     * Login user
     *
     * @param array $data
     * @return User_model
     * @throws Exception
     *
     * @author Farukh Baratov <seniorsngstaff@mail.ru>
     */
    public static function login(array $data = []): User_model
    {
        $user = User_model::find_user_by_email($data['login']);

        if ( ! $user::validate_password($data['password']))
        {
            throw new Exception('Incorrect login or password');
        }

        self::start_session($user->get_id());

        return User_model::get_user();
    }

    /**
     * Start personal user session
     *
     * @param int $user_id
     * @throws Exception
     */
    public static function start_session(int $user_id)
    {
        // если перенедан пользователь
        if (empty($user_id))
        {
            throw new Exception('No id provided!');
        }

        App::get_ci()->session->set_userdata('id', $user_id);
    }
}
