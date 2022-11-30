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
        $root_routing = false;
        if ($climate->confirm('Enable root routing ?')->confirmed()) {
            if (!file_exists(ROOTPATH . '/public')) {
                $climate->error("Public Folder not found");
                return false;
            }
            if (!file_exists(ROOTPATH . '/public/index.php')) {
                $climate->error("File index.php in public folder not found");
                return false;
            }
            if (!file_exists(ROOTPATH . '/public/.htaccess')) {
                $climate->error("File .htaccess in public folder not found");
                return false;
            }
            copy(ROOTPATH . '/public/index.php', ROOTPATH . '/index.php');
            copy(ROOTPATH . '/public/.htaccess', ROOTPATH . '/.htaccess');
            $index = file_get_contents(ROOTPATH . '/index.php');
            $index = str_replace("require FCPATH . '../app/Config/Paths.php'", "require FCPATH . 'app/Config/Paths.php'", $index);
            file_put_contents(ROOTPATH . '/index.php', $index);
            $root_routing = true;
            (is_cli()) ? eval(\Psy\sh()) : false;
        }

        if ($climate->confirm('Enable road runner ?')->confirmed()) {
            $oldpath = getcwd();
            chdir(ROOTPATH);
            exec("composer require sdpmlab/codeigniter4-roadrunner");
            $process = Process::fromShellCommandline("./vendor/bin/rr get-binary")->start();
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
            if($root_routing){
                sleep(1);
                if(!file_exists('rr.yaml')){
                    $climate->error("Connot find rr.yaml configuration see more information in https://github.com/SDPM-lab/Codeigniter4-Roadrunner");
                    return false;
                }
                $rr_yaml = file_get_contents('rr.yaml');
                $rr_yaml = str_replace('dir: public','dir: .',$rr_yaml);
                file_put_contents('rr.yaml',$rr_yaml);
                unset($rr_yaml);
            }
            $climate->out("Please read documentation in https://github.com/SDPM-lab/Codeigniter4-Roadrunner");
            chdir($oldpath);
            unset($oldpath);
        }
        if ($climate->confirm('Enable auto routing ?')->confirmed()) {
            


            (is_cli()) ? eval(\Psy\sh()) : false;
        }
        if ($climate->confirm('Enable codeigniter 3 legacy package ?')->confirmed()) {
            $climate->white("Configurando root routing");
            (is_cli()) ? eval(\Psy\sh()) : false;
        }

        if ($climate->confirm('Create tables and seed ?')->confirmed()) {
            $climate->white("Running seed");
        }

        if ($climate->confirm('Enable telemetry ?')->confirmed()) {
            $climate->white("Telemetry enabled :)");
        }
    }
}
