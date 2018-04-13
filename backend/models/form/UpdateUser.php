<?php
namespace backend\models\form;

use common\models\User;
use yii\base\Model;
use yii\web\NotFoundHttpException;

/**
 * Signup form
 */
class UpdateUser extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var User
     */
    private $user;

    /**
     * CreateUser constructor.
     * @param int $id
     * @param array $config
     * @throws NotFoundHttpException
     */
    public function __construct($id, array $config = [])
    {
        $this->user = User::findOne(['id' => $id]);
        if (!$this->user) {
            throw new NotFoundHttpException('User not found');
        }
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Updates user info
     *
     * @return User|null the saved model or null if saving fails
     */
    public function update()
    {
        if (!$this->validate()) {
            return null;
        }

        if ($this->username) {
            $this->user->username = $this->username;
        }
        if ($this->email) {
            $this->user->email = $this->email;
        }
        if ($this->password) {
            $this->user->setPassword($this->password);
        }
        $this->user->generateAuthKey();

        return $this->user->save() ? $this->user : null;
    }
}
