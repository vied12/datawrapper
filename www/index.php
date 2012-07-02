<?php

/**
 * Datawrapper main index
 *
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require the Slim PHP 5 Framework
require '../vendor/Slim/Slim.php';

// Include the main Propel script
// Initialize Propel with the runtime configuration
// Add the generated 'classes' directory to the include path
require_once '../vendor/propel/runtime/lib/Propel.php';
Propel::init("../lib/core/build/conf/datawrapper-conf.php");
set_include_path("../lib/core/build/classes" . PATH_SEPARATOR . get_include_path());

// Load TwigView
require_once '../vendor/Slim-Extras/Views/TwigView.php';
TwigView::$twigDirectory = '../vendor/Twig';

// include datawrapper session serialization
require '../lib/session/Datawrapper.php';

define('DW_DOMAIN', 'datawrapper');

$app = new Slim(array(
    'view' => new TwigView(),
    'templates.path' => '../templates'
));

// Load twig instance
$twig = $app->view()->getEnvironment();

// Twig Extension to convert strings to nice JavaScript class names, e.g. bar-chart --> BarChart
$twig->addFilter('classify', new Twig_Filter_Function('str_classify'));
function str_classify($s) {
    return preg_replace('/\s/', '', ucwords(preg_replace('/[_\-\.]/', ' ', $s)));
}

// Twig Extension to jsonify objects
$twig->addFilter('json', new Twig_Filter_Function('toJSON'));
function toJSON($arr) {
    return json_encode($arr);
}

// loae I18n extension for Twig
$twig->addExtension(new Twig_Extension_I18n());

if (function_exists('bindtextdomain')) {
    putenv('LC_ALL=de_DE');
    setlocale(LC_ALL, 'de_DE');
    bindtextdomain('Datawrapper', '../locale');
    bind_textdomain_codeset('Datawrapper', 'UTF-8');
    textdomain('Datawrapper');
} else {
    // If no gettext extension is found, we will
    // pretend the API to not break the application
    function gettext($s) { return $s; }
    function ngettext($s) { return $s; }
}


function add_header_vars(&$page, $active = null) {
    // define the header links
    global $app;
    if (!isset($active)) {
        $active = explode('/', $app->request()->getResourceUri());
        $active = $active[1];
    }

    $user = DatawrapperSession::getUser();
    $headlinks = array();
    $headlinks[] = array('url' => '/docs', 'id' => 'about', 'title' => 'About', 'icon' => 'home');
    $headlinks[] = array('url' => '/chart/create', 'id' => 'chart', 'title' => 'Create', 'icon' => 'pencil');
    if ($user->isLoggedIn()) {
        $headlinks[] = array('url' => '/mycharts', 'id' => 'mycharts', 'title' => 'My Charts', 'icon' => 'signal');
    }
    $headlinks[] = array(
        'url' => '',
        'id' => 'lang',
        'dropdown' => array(array(
            'url' => '#lang-de',
            'title' => 'Deutsch'
        ), array(
            'url' => '#lang-en',
            'title' => 'English'
        ), array(
            'url' => '#lang-fr',
            'title' => 'Francais'
        )),
        'title' => 'Language',
        'icon' => 'font'
    );
    if ($user->isLoggedIn()) {
        $headlinks[] = array(
            'url' => '#user',
            'id' => 'user',
            'title' => $user->getEmail(),
            'icon' => 'user',
            'dropdown' => array(array(
                'url' => '/account/settings',
                'icon' => 'cog',
                'title' => 'Settings'
            ), array(
                'url' => '#logout',
                'icon' => 'off',
                'title' => 'Logout'
            ))
        );
    } else {
        $headlinks[] = array(
            'url' => '#login',
            'id' => 'login',
            'title' => 'Login / Sign Up',
            'icon' => 'user'
        );
    }
    foreach ($headlinks as $i => $link) {
        $headlinks[$i]['active'] = $headlinks[$i]['id'] == $active;
    }
    $page['headlinks'] = $headlinks;
    $page['user'] = DatawrapperSession::getUser();
    $page['DW_DOMAIN'] = DW_DOMAIN;
}


function add_editor_nav(&$page, $step) {
    // define 4 step navigation
    $steps = array();
    $steps[] = array('index'=>1, 'id'=>'upload', 'title'=>'Upload Data');
    $steps[] = array('index'=>2, 'id'=>'describe', 'title'=>'Check & Describe');
    $steps[] = array('index'=>3, 'id'=>'visualize', 'title'=>'Visualize');
    $steps[] = array('index'=>4, 'id'=>'publish', 'title'=>'Publish');
    $page['steps'] = $steps;
    $page['createstep'] = $step;
}


require_once '../lib/utils/errors.php';
require_once '../lib/utils/check_chart.php';
require_once '../controller/home.php';
require_once '../controller/account-settings.php';
require_once '../controller/account-activate.php';
require_once '../controller/chart-create.php';
require_once '../controller/chart-edit.php';
require_once '../controller/chart-upload.php';
require_once '../controller/chart-describe.php';
require_once '../controller/chart-visualize.php';
require_once '../controller/chart-data.php';
require_once '../controller/chart-preview.php';
require_once '../controller/chart-embed.php';
require_once '../controller/chart-publish.php';
require_once '../controller/mycharts.php';
require_once '../controller/xhr.php';
require_once '../controller/docs.php';



/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This is responsible for executing
 * the Slim application using the settings and routes defined above.
 */
$app->run();