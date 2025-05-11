<?php

namespace CodeIgniter\Startci\Commands;

use Composer\Composer;
use League\CLImate\CLImate;
use Symfony\Component\Process\Process;

class Init extends \CodeIgniter\CLI\BaseCommand
{

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
    protected $name = 'startci:init';

    /**
     * The Command's usage
     *
     * @var string
     */
    protected $usage = 'startci:init';

    /**
     * The Command's short description.
     *
     * @var string
     */
    protected $description = 'Init and configure startci';

    public function run(array $params)
    {
        $cmd = $params[0] ?? 'run';
        $climate = new CLImate();
        $climate->addArt(ROOTPATH . 'vendor/startci/project/src/art');
        $i = random_int(1, 3);
        if ($i == 1)
            $climate->animation('ci1')->speed(200)->enterFrom('right');
        else
            $climate->animation('ci' . $i)->speed(150)->enterFrom('bottom');
        $version = json_decode(file_get_contents(ROOTPATH . 'vendor/startci/project/composer.json'));
        /*
        $climate->out("Running version $version->version");
        if ($climate->confirm('Enable road runner ?')->confirmed()) {
            $oldpath = getcwd();
            chdir(ROOTPATH);
            exec("composer require sdpmlab/codeigniter4-roadrunner");
            $process = Process::fromShellCommandline("php spark ciroad:init")->start();
            for ($i = 0; $i < 10; $i++) {
                if (file_exists('rr') || file_exists('rr.exe'))
                    break;
                sleep(5);
                $climate->yellow("Wait for rr executable");
            }
            if (!(file_exists('rr') || file_exists('rr.exe'))) {
                $climate->error("Connot find rr executable");
                return false;
            }
            $climate->out("Please read documentation in https://github.com/SDPM-lab/Codeigniter4-Roadrunner");
            chdir($oldpath);
            unset($oldpath);
        }
            */
        if ($climate->confirm('Enable cron job ?')->confirmed()) {
            exec("composer require daycry/cronjob");
            Process::fromShellCommandline("php spark cronjob:publish")->run();
            $climate->out("Please read documentation in https://github.com/daycry/cronjob");
        }
        if ($climate->confirm('Enable auto routing ?')->confirmed()) {
            $routes = file_get_contents(ROOTPATH . 'app/Config/Routes.php');
            $routes = str_replace('// $routes->setAutoRoute(false);', '$routes->setAutoRoute(true);', $routes);
            file_put_contents(ROOTPATH . 'app/Config/Routes.php', $routes);
            $climate->out("Auto Route enabled");
        } else {
            $routes = file_get_contents(ROOTPATH . 'app/Config/Routes.php');
            $routes = str_replace('$routes->setAutoRoute(true);', '// $routes->setAutoRoute(false);', $routes);
            file_put_contents(ROOTPATH . 'app/Config/Routes.php', $routes);
            $climate->out("Auto Route disabled");
        }
        

        // if ($climate->confirm('Enable telemetry ?')->confirmed()) {
        //     $climate->white("Telemetry enabled :)");
        // }
    }
}
