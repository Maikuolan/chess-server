<?php

namespace ChessServer\Command\Data;

use ChessServer\Socket\AbstractSocket;
use OTPHP\InternalClock;
use OTPHP\TOTP;

class TotpSignInCommand extends AbstractDataCommand
{
    public function __construct(Db $db)
    {
        parent::__construct($db);

        $this->name = '/totp_signin';
        $this->description = 'TOTP sign in.';
        $this->params = [
            'settings' => '<string>',
        ];
    }

    public function validate(array $argv)
    {
        return count($argv) - 1 === count($this->params);
    }

    public function run(AbstractSocket $socket, array $argv, int $id)
    {
        $params = json_decode(stripslashes($argv[1]), true);

        $otp = TOTP::createFromSecret($_ENV['TOTP_SECRET'], new InternalClock());
        $otp->setDigits(9);

        if ($otp->verify($params['password'], null, 5)) {
            $sql = "SELECT * FROM users WHERE username = :username";
            $values[] = [
                'param' => ":username",
                'value' => $params['username'],
                'type' => \PDO::PARAM_STR,
            ];
            $arr = $this->db->query($sql, $values)->fetch(\PDO::FETCH_ASSOC);

            $sql = "UPDATE users SET lastLoginAt = now() WHERE username = :username";
            $values[] = [
                'param' => ":username",
                'value' => $params['username'],
                'type' => \PDO::PARAM_STR,
            ];
            $this->db->query($sql, $values);

            return $socket->getClientStorage()->sendToOne($id, [
                $this->name => [
                    'username' => $arr['username'],
                    'elo' => $arr['elo'],
                ],
            ]);
        }

        return $socket->getClientStorage()->sendToOne($id, [
            $this->name => null,
        ]);
    }
}
