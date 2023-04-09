<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services as ConfigServices;
use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Model;
use CodeIgniter\Session\Session;
use CodeIgniter\Startci\Db;
use CodeIgniter\Test\TestLogger;
use Config\App;
use Config\Database;
use Config\Logger;
use Config\Services;
use Config\View;
use Laminas\Escaper\Escaper;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// Services Convenience Functions
//<newbgp>
function is_post()
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function is_get()
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'POST') === 'GET';
}

function form($key = null, $default = null)
{
    $_JSON = [];
    if (apache_request_headers()['Content-Type'] ?? '' == 'application/json')
        $_JSON = json_decode(file_get_contents('php://input'), true) ?? [];
    if ($key != null) {
        if (isset($_JSON[$key]))
            return $_JSON[$key];
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    } else {
        return array_merge($_GET, $_POST, $_JSON);
    }
}

/**
 * 
 * @param type $name
 * @return \CodeIgniter\Database\BaseBuilder
 */
function table(string $name, $db = null): \CodeIgniter\Database\BaseBuilder
{
    return db_connect($db)->table($name);
}


/**
 * 
 * @return \CodeIgniter\Validation\Validation
 */

function valid(): \CodeIgniter\Validation\Validation
{
    $valid = Services::validation();
    return $valid;
}

function form_error($field, $template = 'single')
{
    echo valid()->showError($field, $template);
}

function jwt_encode($data)
{
    return JWT::encode($data, env('encryption.key') ?? 'startci', 'HS256');
}

function jwt_decode($data)
{
    return JWT::decode($data, new Key(env('encryption.key') ?? 'startci', 'HS256'));
}
/**
 * @return object|\App\Models\Users
 */
function user($table = 'users')
{
    $id = null;
    if (session()->has('id'))
        $id = session()->get('id');
    try {
        if (isset(getallheaders()['Authorization'])) {
            $id = jwt_decode(substr(getallheaders()['Authorization'], strlen("Bearer ")));
            if (is_object($id))
                $id = $id->id;
        }
    } catch (\Throwable $th) {
        throw $th;
    }
    if ($id) {
        if (class_exists('\App\Models\Users')) {
            return \App\Models\Users::init()->byId($id);
        } else {
            return table($table)->where('id', $id)->get()->getFirstRow();
        }
    } else {
        return false;
    }
}

/**
 * 
 * @param string $data
 * @return string|boolean
 */
function excel_date($data, $format = 'd/m/Y', $erro = false)
{
    if (!$format) {
        $format = 'd/m/Y';
    }
    $data_excel = \Carbon\Carbon::create(1900, 1, 1);
    if (is_numeric($data)) {
        return $data_excel->clone()->addDays(($data - 2))->format($format);
    } elseif (is_date('d/m/Y', $data)) {
        return \Carbon\Carbon::createFromFormat('d/m/Y', $data)->format($format);
    } elseif (is_date('Y-m-d', $data)) {
        return \Carbon\Carbon::createFromFormat('Y-m-d', $data)->format($format);
    } else {
        return $erro;
    }
}

class FakeCarbon
{

    function __construct($date = null)
    {
    }

    function format($format = null)
    {
        return null;
    }
}

/**
 * 
 * @param string $data
 * @return \Carbon\Carbon
 */
function carbon(string $data = null)
{

    if ($data == '30/11/-0001' || $data == '0000-00-00') {
        $nullformat = new FakeCarbon();
        return $nullformat;
    }
    if (is_date('d/m/Y', $data)) {
        return \Carbon\Carbon::createFromFormat('d/m/Y', $data);
    } elseif (is_date('d/m/Y H:m:s', $data)) {
        return \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $data);
    } elseif (is_date('Y-m-d H:m:s', $data)) {
        return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $data);
    } elseif (is_date('Y-m-d', $data)) {
        return \Carbon\Carbon::createFromFormat('Y-m-d', $data);
    } elseif ($data == null) {
        return \Carbon\Carbon::now();
    } else {
        $nullformat = new FakeCarbon();
        return $nullformat;
    }
}

function is_date($format, $date)
{
    if (!$date)
        return false;
    return (DateTime::createFromFormat($format, $date)) !== false;
}

function thread($route)
{
    $p = new Process(explode(' ', 'php index.php ' . $route));
    $p->setTimeout(null);
    $p->setIdleTimeout(null);
    $p->setOptions(['create_new_console' => true]);
    $p->start(function ($type, $data) {
        //        echo "$type - $data" . PHP_EOL;
    });
    return $p;
}

function thread_group($routes)
{
    $ps = array_map(function ($p) {
        return thread($p);
    }, $routes);
    foreach ($ps as $value) {
        if ($value->isRunning())
            $value->wait();
    }
}

function thread_group_batch($routes, $size = 2)
{
    batch_exec_async(array_map(function ($v) {
        return "php index.php $v";
    }, $routes), $size);
}

function batch_exec_async($cmds, $limit = 1000)
{
    foreach ($cmds as $key => $value) {
        $p = new Process(explode(' ', $value));
        $p->setTimeout(null);
        $p->setIdleTimeout(null);
        $p->setOptions(['create_new_console' => true]);
        $cmds[$key] = $p;
    }
    foreach ($cmds as $cmd) {
        do {
            $total = 0;
            foreach ($cmds as $c) {
                if ($c->isRunning()) {
                    $total++;
                }
            }

            if ($total >= $limit) {
                sleep(1);
            }
        } while ($total >= $limit);
        $cmd->start(function ($type, $data) {
            echo "$type - $data" . PHP_EOL;
        });
        //        sleep(random_int(1, 2));
    }
    foreach ($cmds as $cmd) {
        if ($cmd->isRunning()) {
            $cmd->wait();
        }
    }
    return true;
}

function assets_build(array $files, $id = '')
{
    $css = [];
    $js = [];
    foreach ($files as $key => $f) {
        if (str_ends_with($f, '.js'))
            $js[] = $f;
        if (str_ends_with($f, '.css'))
            $css[] = $f;
    }
    if ($js)
        js_build($files, $id);
    if ($css)
        css_build($files, $id);
}

function css_build(array $files, $id = '')
{
    $c = stream_context_create([
        'ssl' => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ]
    ]);
    if (file_exists("public/build$id.css"))
        echo "<link rel='stylesheet' href='/public/build$id.css' />";
    $css = '';
    foreach ($files as $key => $f)
        if (file_exists($f))
            $css .= file_get_contents($f) . PHP_EOL;
        else
            $css .= ConfigServices::curlrequest()->get($f)->getBody() . PHP_EOL;
    if (!$css)
        return '';
    file_put_contents("public/build$id.css", $css);
    echo "<link rel='stylesheet' href='/public/build$id.css' />";
}

function js_build(array $files, $id = '')
{
    $c = stream_context_create([
        'ssl' => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ]
    ]);
    if (file_exists("public/build$id.js"))
        echo "<script src='/public/build$id.js'></script>";
    $js = '';
    foreach ($files as $key => $f)
        if (file_exists($f))
            $js .= file_get_contents($f) . PHP_EOL;
        else
            $js .= ConfigServices::curlrequest()->get($f)->getBody() . PHP_EOL;
    if (!$js)
        return;
    file_put_contents("public/build$id.js", $js);
    echo "<script src='/public/build$id.js'></script>";
}

function assets_cache($file)
{
    if (!$file)
        return '';
    if (is_array($file)) {
        foreach ($file as $key => $f)
            assets_cache($f);
        return '';
    }
    $id = md5($file);
    if (!$content = cache()->get("assets_$id")) {
        $content = file_get_contents($file);
        cache()->save("assets_$id", $content, 300);
    }
    if (str_contains($file, '.js'))
        echo "<script type='text/javascript'>$content</script>" . PHP_EOL;
    if (str_contains($file, '.css'))
        echo "<style>$content</style>" . PHP_EOL;
}

function smarty($view, $data = [])
{
    // die("not compatible 8.1 https://github.com/smarty-php/smarty/issues/671");
    $smarty = new Smarty();
    @mkdir('writable/cache/smarty/templates_c/', 0777, true);
    @mkdir('writable/cache/smarty/cache/', 0777, true);
    $smarty->setCompileDir('writable/cache/smarty/templates_c/');
    $smarty->setCacheDir('writable/cache/smarty/cache/');
    $smarty->setTemplateDir('app/Views');
    foreach ($data as $key => $value) {
        $smarty->assign($key, $value);
    }
    // dd(ROOTPATH.'app/View/components/');
    if (file_exists('app/Views/components/'))
        $files = array_map(function (SplFileInfo $v) use ($smarty) {
            $value = $v->getPathname();
            if ($v->isDir() || !$v->isFile())
                return false;
            $filename = basename($value);
            $dir = dirname($value);
            $comp_path = 'app/Views/components/';
            if (str_starts_with($dir, $comp_path))
                $namespace = str_replace('/', '_', str_replace($comp_path, '', $dir)) . '_';
            else
                $namespace = '';
            $component_name = substr($filename, 0, -4);
            $smarty->registerPlugin('function', $namespace . $component_name, function ($params, Smarty_Internal_Template $smarty) use ($value) {
                $tpl = $value;
                foreach ($params as $key => $value)
                    $smarty->assign($key, $value);
                return $smarty->fetch($tpl);
            });
            return true;
        }, iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Views/components/'))));
    return $smarty->fetch("app/Views/{$view}.tpl");
}

/**
 * 
 * @return \CodeIgniter\JS
 */
function js(): \CodeIgniter\JS
{
    return new \CodeIgniter\JS();
}

/**
 * 
 * @param type $selector
 * @return \CodeIgniter\Jquery
 */
function jquery($selector = ''): \CodeIgniter\Jquery
{
    return new \CodeIgniter\Jquery($selector);
}

/**
 * 
 * @param type $varname
 * @return \CodeIgniter\Vue
 */
function vue($varname = 'vue'): \CodeIgniter\Vue
{
    return new \CodeIgniter\Vue($varname);
}

/**
 * 
 * @return \Faker\Generator
 */
function faker($locale = null)
{

    return \Faker\Factory::create($locale ?? 'pt_br');
}

function form_open($plus = '')
{
    echo '<form ' . $plus . '>';
}

function form_close()
{
    echo '</form>';
}

function row($class = "row", $id = "")
{
    global $ci_row;
    if ($ci_row == true || $ci_row == null) {
        echo "<div id=\"{$id}\" class=\"{$class}\">";
        $ci_row = false;
    } else {
        echo "</div>";
        $ci_row = true;
    }
}

function label_check($id, $texto, $plus = '', $size = 12)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = '';
    }
    ob_start();
    check($id, $texto, $plus, 12);
    _form_error_html($id);
    echo div(ob_get_clean(), $size, ' mt-4 ');
}

function label_button($texto, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = '';
    }
    if (!str_contains($plus, 'class="')) {
        $plus .= ' class="btn btn-primary form-control label_button mt-4" ';
    }
    ob_start();
?>
    <button data-button="true" type="button" <?= $plus ?>><?= $texto ?></button>
<?php
    $html = ob_get_contents();
    ob_end_clean();
    echo div($html, $size);
}

function button($texto, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = '';
    }
    if (!str_contains($plus, 'class="')) {
        $plus .= ' class="btn btn-primary form-control" ';
    }
    ob_start();
?>
    <button data-button="true" type="button" <?= $plus ?>><?= $texto ?></button>
<?php
    $html = ob_get_contents();
    ob_end_clean();
    echo div($html, $size);
}

/**
 * 
 * @param type $id identificação
 * @param type $texto Texto
 * @param type $grupo Agrupamento 
 * @param type $plus atributos adicionais
 * @param type $size colunas
 * @return type
 */
function radio($id, $texto, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = '';
    }
    $value = "value='$texto'";
    if (!str_contains($plus, "value")) {
        $value = "";
    }
    $html = "<label style='cursor:pointer;margin-top:5px' data-radio='true' ><input name='$id' $plus type='radio'  $value  />  {$texto}</label>";
    echo div($html, $size);
}

/**
 * Cria um input tipo checkbox
 * @param type $id identificador
 * @param type $texto Texto
 * @param type $grupo Para agrupar em array, se vazio envia 'true' ou 'false'
 * @param type $plus atributos adicionais
 * @param type $size tamanho da coluna
 * @return type
 */
function check($id, $texto, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = '';
    }
    $value = "value = '$texto'";

    if (!str_contains($plus, "value")) {
        $value = "";
    }
    $html = "<label style='margin-top:5px'><input name='$id' type='checkbox' $value $plus  />  {$texto}</label>";
    echo div($html, $size);
}

function combo($id, $itens, $valoresItens = array(), $plus = "", $size = 3)
{
    if (is_numeric($valoresItens)) {
        $size = $valoresItens;
        $valoresItens = array();
        $plus = '';
    }
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = '';
    }
    if (!str_contains($plus, 'class="')) {
        $plus .= ' class="form-control"';
    }
    $select_value = null;
    if (str_contains($plus, "value=")) {
        $attrs = _form_parse_attr($plus);
        $select_value = strval($attrs['value']);
    }
    $retorno = "";
    $retorno .= "<select name='{$id}' $plus >";
    $contador = 0;
    foreach ($itens as $value) {
        if (count($valoresItens) > 0) :
            $retorno .= "<option " . (($value == $select_value) ? 'selected' : '') . " value='" . $value . "'>" . $valoresItens[$contador] . "</option>";
        else :
            $retorno .= "<option " . (($value == $select_value) ? 'selected' : '') . " value='" . $value . "'>" . $value . "</option>";
        endif;
        $contador++;
    }
    $retorno .= "</select>";
    echo div($retorno, $size);
}

function label($texto = "", $size = 1)
{
    $retorno = "<label data-label='true' class='control-label'>$texto</label>";
    echo div($retorno, $size);
}

function text($id, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    if (!str_contains($plus, 'class="')) {
        $plus .= ' class="form-control"';
    }
    if (!str_contains($plus, "type")) {
        $html = "<input name='{$id}' type='text' $plus />";
    } else {
        $html = "<input name='{$id}' $plus />";
    }
    echo div($html, $size);
}

function _form_parse_attr($attr = '')
{
    $attributes = new SimpleXMLElement("<element $attr />");
    if (!isset($attributes['value'])) {
        $attributes['value'] = '';
    }
    return $attributes;
}

function _form_error_html($id)
{
    echo "<div class='invalid-feedback' id='invalid$id' ></div>";
}

function textarea($id, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    if (!str_contains($plus, 'class="')) {
        $plus .= ' class="form-control"';
    }
    $parse = _form_parse_attr($plus);
    $html = "<textarea name='{$id}' $plus >{$parse['value']}</textarea>";
    echo div($html, $size);
}

function mask($id, $mask = "999999", $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    if (!str_contains($plus, 'class="')) {
        $plus .= ' class="form-control"';
    }
    if (!str_contains($plus, "type")) {
        $html = "<input type='text' name='{$id}' data-inputmask=\"'mask':'{$mask}'\" {$plus} />";
    } else {
        $html = "<input name='{$id}' data-inputmask==\"'mask':'{$mask}'\" {$plus} />";
    }

    echo div($html, $size);
}

function number($id, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    if (!str_contains($plus, 'class="')) {
        $plus .= ' class="form-control"';
    }
    $html = "<input type='number' name='{$id}'  {$plus} />";
    echo div($html, $size);
}

function money($id, $digits = 2, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    if (!str_contains($plus, 'class="')) {
        $plus .= ' class="form-control"';
    }
    $info = localeconv();
    // $info['frac_digits'] = $digits;
    // dd($info);
    $html = "<input type='text' name='{$id}' data-thousands='' data-decimal='.' data-precision='{$digits}' {$plus} />";
    $html .= "<script>$(function(){ $('input[name=\"{$id}\"]').maskMoney(); })</script>";
    // $html = "<input type='text' name='{$id}' data-frac_digits='" . $info['frac_digits'] . "' data-decimal_point='" . $info['decimal_point'] . "' data-thousands_sep='" . $info['thousands_sep'] . "' data-money='true' {$plus} />";
    echo div($html, $size);
}

function password($id, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    if (!str_contains($plus, 'class="')) {
        $plus .= ' class="form-control"';
    }
    $html = "<input name='{$id}' type='password' $plus />";
    echo div($html, $size);
}

//wtf ?

function upload($id, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    if (!str_contains($plus, 'class="')) {
        $plus .= ' class="form-control"';
    }
    // conf::$pkj_uid_comp++;
    $r = "<input $plus type='file' name='{$id}' $plus />";
    echo div($r, $size);
}

function div($elemento = "", $tamanho = 2, $class = "", $plus = "")
{
    return "<div class='col-md-$tamanho $class' $plus >$elemento</div>\n";
}

function label_text($label, $id, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    ob_start();
    if ($label !== '') {
        label($label, 12);
    }
    text($id, $plus, 12);
    _form_error_html($id);
    $html = ob_get_clean();
    echo div($html, $size, "form-group");
}

function label_textarea($label, $id, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    ob_start();
    if ($label !== '') {
        label($label, 12);
    }
    textarea($id, $plus, 12);
    _form_error_html($id);
    $html = ob_get_clean();
    echo div($html, $size, "form-group");
}

function label_combo($label, $id, $itens = [], $valoresItens = [], $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    ob_start();
    if ($label !== '') {
        label($label, 12);
    }
    combo($id, $itens, $valoresItens, $plus, 12);
    _form_error_html($id);
    $html = ob_get_clean();
    echo div($html, $size, "form-group");
}

function label_upload($label, $id, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    ob_start();
    if ($label !== '') {
        label($label, 12);
    }
    upload($id, $plus, 12);
    _form_error_html($id);
    $html = ob_get_clean();
    echo div($html, $size, "form-group");
}

function label_password($label, $id, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    ob_start();
    if ($label !== '') {
        label($label, 12);
    }
    password($id, $plus, 12);
    _form_error_html($id);
    $html = ob_get_clean();
    echo div($html, $size, "form-group");
}

function label_money($label, $id, $digits = 2, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    ob_start();
    if ($label !== '') {
        label($label, 12);
    }
    money($id, $digits, $plus, 12);
    _form_error_html($id);
    $html = ob_get_clean();
    echo div($html, $size, "form-group");
}

function label_mask($label, $id, $mask = "9999", $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    ob_start();
    if ($label !== '') {
        label($label, 12);
    }
    mask($id, $mask, $plus, 12);
    _form_error_html($id);
    $html = ob_get_clean();
    echo div($html, $size, "form-group");
}

function label_number($label, $id, $plus = "", $size = 3)
{
    if (is_numeric($plus)) {
        $size = $plus;
        $plus = "";
    }
    ob_start();
    if ($label !== '') {
        label($label, 12);
    }
    number($id, $plus, 12);
    _form_error_html($id);
    $html = ob_get_clean();
    echo div($html, $size, "form-group");
}

function hidden($id, $value = "", $show = false)
{
    echo "<input type='hidden' name='{$id}' value='$value' />" . (($show) ? $value : "");
}
// if (!function_exists('sd')) {
//     function sd(...$v)
//     {
//         s(...$v);
//         exit;
//     }
//     Kint\Kint::$aliases[] = 'sd';
// }
/**
 * Grabs a database connection and returns it to the user.
 *
 * This is a convenience wrapper for \Config\Database::connect()
 * and supports the same parameters. Namely:
 *
 * When passing in $db, you may pass any of the following to connect:
 * - group name
 * - existing connection instance
 * - array of database configuration values
 *
 * If $getShared === false then a new connection instance will be provided,
 * otherwise it will all calls will return the same instance.
 *
 * @param array|ConnectionInterface|string|null $db
 *
 * @return BaseConnection|Db
 */
function db($connection = null)
{
    return new Db(db_connect($connection));
}
//</newbgp>
