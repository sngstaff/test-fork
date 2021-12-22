<?php

use Model\Boosterpack_model;
use Model\Comment_model;
use Model\Login_model;
use Model\Post_model;
use Model\User_model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{
    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        if (is_prod()) {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    /**
     * Main page
     *
     * @return void
     * @throws Exception
     */
    public function index()
    {
        $user = User_model::get_user();
        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    /**
     * Get all posts
     *
     * @return object|string|void
     * @throws Exception
     */
    public function get_all_posts()
    {
        $posts = Post_model::preparation_many(Post_model::get_all(), 'default');
        return $this->response_success(['posts' => $posts]);
    }

    /**
     * Get booster packs
     *
     * @return object|string|void
     */
    public function get_boosterpacks()
    {
        $posts = Boosterpack_model::preparation_many(Boosterpack_model::get_all(), 'default');
        return $this->response_success(['boosterpacks' => $posts]);
    }

    /**
     * Endpoint login user
     *
     * @throws Exception
     *
     * @author Farukh Baratov <seniorsngstaff@mail.ru>
     */
    public function login()
    {
        try {
            $model = Login_model::login($this->input->post());
        } catch (Exception $e) {
            return $this->response_error($e->getMessage());
        }

        return $this->response_success([
            'user' => $model
        ]);
    }

    public function logout()
    {
        // TODO: task 1, аутентификация
    }

    /**
     * Store comment to db
     *
     * @return object|string|void
     * @throws Exception
     *
     * @author Farukh Baratov <seniorsngstaff@mail.ru>
     */
    public function comment()
    {
        $data = App::get_ci()->input->post();
        $data['user_id'] = User_model::get_user()->get_id();

        try {
            $comment = Comment_model::create($data);
        } catch (Exception $e) {
            return $this->response_error('Whoops');
        }

        return $this->response_success([
            'comment' => Comment_model::preparation($comment, 'default')
        ]);
    }

    public function like_comment(int $comment_id)
    {
        // TODO: task 3, лайк комментария
    }

    public function like_post(int $post_id)
    {
        // TODO: task 3, лайк поста
    }

    public function add_money()
    {
        // TODO: task 4, пополнение баланса

        $sum = (float)App::get_ci()->input->post('sum');

    }

    /**
     * Get one post
     *
     * @param int $post_id
     * @return object|string|void
     *
     * @author Farukh Baratov <seniorsngstaff@mail.ru>
     */
    public function get_post(int $post_id)
    {
        $post = Post_model::preparation(Post_model::get_by_id($post_id), 'full_info');
        return $this->response_success(['post' => $post]);
    }

    public function buy_boosterpack()
    {
        // Check user is authorize
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        // TODO: task 5, покупка и открытие бустерпака
    }


    /**
     * @return object|string|void
     */
    public function get_boosterpack_info(int $bootserpack_info)
    {
        // Check user is authorize
        if (!User_model::is_logged()) {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }


        //TODO получить содержимое бустерпака
    }
}
