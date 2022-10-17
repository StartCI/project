<?php

namespace CodeIgniter\Startci\Commands;

use Ahc\Cron\Expression;

class Cron extends \CodeIgniter\CLI\BaseCommand {

    /**
     * The group the command is lumped under
     * when listing commands.
     *
     * @var string
     */
    protected $group = 'Startci';

    /**
     * The Command's name
     *
     * @var string
     */
    protected $name = 'startci:cron';

    /**
     * The Command's usage
     *
     * @var string
     */
    protected $usage = 'startci:cron run?';

    /**
     * The Command's short description.
     *
     * @var string
     */
    protected $description = 'Run the crons';

    public function run(array $params) {
        $cmd = $params[0] ?? 'run';
        $cron = implode(' ', array_splice($params, 1, 5));
        $route = $params[6] ?? null;
        $cron = $this->_read();
        foreach (Expression::getDues($cron) as $key => $value) {
            exec("php ../index.php $value", $out);
            echo implode(PHP_EOL, $out);
            log_message('debug', 'CRON ' . $value . PHP_EOL . $out);
        }
    }

    function _read(): array {
        $r = [];
        $file = file_get_contents('../writable/cron.txt');
        foreach (explode(PHP_EOL, $file) as $value) {
            $value = trim($value);
            if (($value[0] ?? null == '#') || !$value)
                continue;
            $v = explode(':', $value);
            $r[$v[1]] = $v[0];
        }
        return $r;
    }

}
