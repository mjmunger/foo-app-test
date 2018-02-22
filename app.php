<?php

namespace Test\Demo;

use PHPAnt\Core\AntApp;
use PHPAnt\Core\AppInterface;

use PHPAnt\Core\CommandInvoker;
use PHPAnt\Core\CommandList;

/**
 * App Name: Demo App
 * App Description: Demos this code.
 * App Version: 1.0
 * App Action: cli-load-grammar  -> loadDemoApp       @ 90
 * App Action: cli-init          -> declareMySelf          @ 50
 * App Action: cli-command       -> processCommand         @ 50
 * App Action: load_loaders      -> DemoAppAutoLoader @ 50
 *
 * Actions may be remarked out by changing the ":" to a "#"
 * Example remarked action:
 * App Action# foo-action       -> doSomeWork             @ 50
 *
 * CSS and JS injections:
 * 
 * App Action: header-inject-css -> injectCSS              @ 50
 * App Action: footer-inject-js  -> injectFooterJS         @ 50
 * App Action: header-inject-js  -> injectHeaderJS         @ 50
 */

 /**
 * This app adds the Demo App and commands into the CLI by adding in
 * the grammar for commands into an array, and returning it up the chain.
 *
 * @package      Test
 * @subpackage   Demo
 * @category     Demo
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 


class DemoApp extends AntApp  implements AppInterface  {

    /**
     * Instantiates an instance of the DemoApp class.
     * Example:
     *
     * <code>
     * $appDemoApp = new DemoApp();
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function __construct() {
        $this->appName = 'Demo App';
        $this->canReload = true;
        $this->path = __DIR__;

        //requires to use the CommandList to get grammar... and to avoid crashes.
        $this->AppCommands = new CommandList();
        $this->loadCommands();
    }

    /**
     * Callback for the cli-load-grammar action, which adds commands specific to this plugin to the CLI grammar.
     * Example:
     *
     * @return array An array of CLI grammar that will be merged with the rest of the grammar. 
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function loadDemoApp() {
        $grammar = [];

        $this->loaded = true;

        /* Example command invoker. Use this for adding CLI commands:

        This code will cause the CLI command `foo bar` to execute the method $this->doSomeWork:

        //Hard way. Use the easy way below. This is here FYI because older apps used this way.
        $callback = 'doSomeWork';
        $criteria = ['is' => ['foo bar' => true]];
        $Command = new CommandInvoker($callback);
        $Command->addCriteria($criteria);
        $this->AppCommands->add($Command);
        
        //Easy way
        $Command = new CommandInvoker('doSomeWork');
        $Command->is('foo bar');
        $this->AppCommands->add($Command);

        //Easy way for "starts with"
        $Command = new CommandInvoker('doSomeWork');
        $Command->startsWith('foo bar');
        $this->AppCommands->add($Command);

        */

        $grammar = array_merge_recursive($grammar, $this->AppCommands->getGrammar());
        
        $results['grammar'] = $grammar;
        $results['success'] = true;
        return $results;
    }

    function DemoAppAutoLoader() {
        //REGISTER THE AUTOLOADER! This has to be done first thing! 
        spl_autoload_register(array($this,'loadDemoAppClasses'));
        return ['success' => true];
    }

    public function loadDemoAppClasses($class) {
        
        //Break apart namespaced requests.

        $buffer = explode("\\",$class);
        $class = end($buffer);

        $baseDir = $this->path;

        $candidate_files = array();

        //Try to grab it from the classes directory.
        $candidate_path = sprintf($baseDir.'/classes/%s.class.php',$class);
        array_push($candidate_files, $candidate_path);

        //Loop through all candidate files, and attempt to load them all in the correct order (FIFO)
        foreach($candidate_files as $dependency) {
            if($this->verbosity > 14) printf("Looking to load %s",$dependency) . PHP_EOL;

            if(file_exists($dependency)) {
                if(is_readable($dependency)) {

                    //Print debug info if verbosity is greater than 9
                    if($this->verbosity > 11) print "Including: " . $dependency . PHP_EOL;

                    //Include the file!
                    include($dependency);
                }
            }
        }
        return ['success' => true];
    }
    
    /**
     * Callback function that prints to the CLI during cli-init to show this plugin has loaded.
     * Example:
     *
     * @return array An associative array declaring the status / success of the operation.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function declareMySelf() {
        if($this->verbosity > 4 && $this->loaded ) print("Demo App app loaded.\n");

        return ['success' => true];
    }

    function processCommand($args) {
        $cmd = $args['command'];

        return ['success' => true];
    }


}