<?php
namespace Model;

use App;
use Exception;
use http\Client\Curl\User;
use System\Emerald\Emerald_model;
use stdClass;
use ShadowIgniterException;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 27.01.2020
 * Time: 10:10
 */
class Boosterpack_model extends Emerald_model
{
    const CLASS_TABLE = 'boosterpack';

    /** @var float Цена бустерпака */
    protected $price;
    /** @var float Банк, который наполняется  */
    protected $bank;
    /** @var float Наша комиссия */
    protected $us;

    protected $boosterpack_info;


    /** @var string */
    protected $time_created;
    /** @var string */
    protected $time_updated;

    /**
     * @return float
     */
    public function get_price(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return bool
     */
    public function set_price(int $price):bool
    {
        $this->price = $price;
        return $this->save('price', $price);
    }

    /**
     * @return float
     */
    public function get_bank(): float
    {
        return $this->bank;
    }

    /**
     * @param float $bank
     *
     * @return bool
     */
    public function set_bank(float $bank):bool
    {
        $this->bank = $bank;
        return $this->save('bank', $bank);
    }

    /**
     * @return float
     */
    public function get_us(): float
    {
        return $this->us;
    }

    /**
     * @param float $us
     *
     * @return bool
     */
    public function set_us(float $us):bool
    {
        $this->us = $us;
        return $this->save('us', $us);
    }

    /**
     * @return string
     */
    public function get_time_created(): string
    {
        return $this->time_created;
    }

    /**
     * @param string $time_created
     *
     * @return bool
     */
    public function set_time_created(string $time_created):bool
    {
        $this->time_created = $time_created;
        return $this->save('time_created', $time_created);
    }

    /**
     * @return string
     */
    public function get_time_updated(): string
    {
        return $this->time_updated;
    }

    /**
     * @param string $time_updated
     *
     * @return bool
     */
    public function set_time_updated(string $time_updated):bool
    {
        $this->time_updated = $time_updated;
        return $this->save('time_updated', $time_updated);
    }

    //////GENERATE

    /**
     * @return Boosterpack_info_model[]
     */
    public function get_boosterpack_info(): array
    {
        return [];
        /*
         * Связь бустерпаков нужно было заинсертить в бд?
         */
        // TODO
    }

    function __construct($id = NULL)
    {
        parent::__construct();

        $this->set_id($id);
    }

    public function reload()
    {
        parent::reload();
        return $this;
    }

    public static function create(array $data)
    {
        App::get_s()->from(self::CLASS_TABLE)->insert($data)->execute();
        return new static(App::get_s()->get_insert_id());
    }

    public function delete():bool
    {
        $this->is_loaded(TRUE);
        App::get_s()->from(self::CLASS_TABLE)->where(['id' => $this->get_id()])->delete()->execute();
        return App::get_s()->is_affected();
    }

    public static function get_all()
    {
        return static::transform_many(App::get_s()->from(self::CLASS_TABLE)->many());
    }

    /**
     * @return int
     * @throws Exception
     */
    public function open(): int
    {
        $user = User_model::get_user();
        if ($user->get_wallet_balance() < $this->get_price())
        {
            throw new Exception('Up balance');
        }

        # может быть я не так понял.
        $items = $this->get_contains($this->_get_max_item_cost());
        $item = Item_model::transform_one((array)$items[array_rand($items)]);

        // calculating..
        $transaction = App::get_s()->start_trans();
        try {
            $this->recalculate_bank($item->get_price());
            $user->remove_money($this->get_price());
            $user->set_likes_balance($user->get_likes_balance() + $item->get_price());

            $transaction->commit();
        } catch (Exception $e) {
            // need log
            $transaction->rollback();
        }

        return $item->get_price();
    }

    /**
     * Recalculate bank
     *
     * @param int $amount
     *
     * @return void
     */
    protected function recalculate_bank(int $amount)
    {
        $this->set_bank($this->get_bank() + $this->get_price() - $this->get_us() - $amount);
    }

    /**
     * @param int $max_available_likes
     *
     * @return Item_model[]
     */
    public function get_contains(int $max_available_likes): array
    {
        # тут можно было бы сразу получить 1 рандомную запись с бд
        return App::get_s()
            ->from('items')
            ->between('price', 1, $max_available_likes)
            ->many();
    }


    /**
     * @param Boosterpack_model $data
     * @param string            $preparation
     *
     * @return stdClass|stdClass[]
     */
    public static function preparation(Boosterpack_model $data, string $preparation = 'default')
    {
        switch ($preparation)
        {
            case 'default':
                return self::_preparation_default($data);
            case 'contains':
                return self::_preparation_contains($data);
            default:
                throw new Exception('undefined preparation type');
        }
    }

    /**
     * @param Boosterpack_model $data
     *
     * @return stdClass
     */
    private static function _preparation_default(Boosterpack_model $data): stdClass
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->price = $data->get_price();

        return $o;
    }


    /**
     * @param Boosterpack_model $data
     *
     * @return stdClass
     */
    private static function _preparation_contains(Boosterpack_model $data): stdClass
    {
        $o = new stdClass();

        $o->id = $data->get_id();
        $o->price = $data->get_price();

        $o->contains = Item_model::preparation_many($data->get_contains($data->_get_max_item_cost()));

        return $o;
    }

    /**
     * Get maximum item cost
     *
     * @return float
     *
     * @author Farukh Baratov <seniorsngstaff@mail.ru>
     */
    private function _get_max_item_cost(): float
    {
        return $this->get_bank() + ($this->get_price() - $this->get_us());
    }
}
