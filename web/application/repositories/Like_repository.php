<?php

namespace Repository;

use App;
use Exception;
use Model\User_model;
use System\Emerald\Emerald_model;

/**
 * Like repository
 *
 * @author Farukh Baratov <seniorsngstaff@mail.ru>
 */
class Like_repository
{
    protected $model;

    private $_user;

    /**
     * Like repository constructor
     *
     * @param Emerald_model $model
     */
    public function __construct(Emerald_model $model)
    {
        $this->model = $model;
        # default user
        $this->_user = User_model::get_user();
    }

    /**
     * Set a new user
     *
     * @param User_model $user
     * @return $this
     */
    public function set_user(User_model $user)
    {
        $this->_user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User_model
     */
    public function get_user(): User_model
    {
        return $this->_user;
    }

    /**
     * Like model
     *
     * @return bool
     * @throws \ShadowIgniterException
     * @throws Exception
     */
    public function like(): bool
    {
        if ( ! $this->_user->can_pay())
        {
            throw new Exception('Insufficient funds, please up balance.');
        }

        # по сути мы работаем с балансом если 1 лайк = 1 usd
        # в случае возникновения ошибки с бд мы откатываем действия
        $transaction = App::get_s()->start_trans();

        App::get_s()->from($this->model->get_table())
            ->where([
                'id' => $this->model->get_id()
            ])
            ->update(sprintf('likes = likes + %s', App::get_s()->quote(1)))
            ->execute();

        try {
            $this->_user->decrement_likes();
            $this->model->reload();

            $transaction->commit();
        } catch (Exception $e) {
            # log e..
            $transaction->rollback();
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Get likes count
     *
     * @return mixed
     */
    public function get_likes_count()
    {
        /*
         * Можно создать трейт hasLike и подключать его в моделях, где используются лайки
         */
        return $this->model->get_likes();
    }
}