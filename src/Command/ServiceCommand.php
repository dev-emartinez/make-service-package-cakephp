<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use SebastianBergmann\Type\VoidType;

/**
 * Service command.
 */
class ServiceCommand extends Command
{
    protected $svcPath;
    const EXTENSION = 'Service.php';
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        
        /**
         * we can add `required` key
         */
        $parser->addArgument('serviceName', [
            'help' => 'New service name'
        ]);

        return $parser;
    }

    public function initialize() :void
    {
        $this->verifyFolder();
        $this->svcPath = APP_DIR.DS.'Services';
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $service = $args->getArgument('serviceName');

        if (empty($service)) {
            $io->out('Service name needed it. Please set the name. Example: `bin/cake bake service MyService`');
            exit;
        }

        if ($this->exists($service)) {
            $io->out('The service class already exists!');
            exit;
        }

        try {
            $this->createServiceClass($service);
            $io->out(sprintf('Service generated correctly! Stored in App/Services/%s', $service));
            exit;
        } catch (\Throwable $th) {
            /** @todo implement custom Exception */
            $io->out($th->getMessage());
        }
    }

    /**
     * generate the `Services` folder if not exists
     */
    private function verifyFolder():void 
    {
        if (!is_dir(APP_DIR.DIRECTORY_SEPARATOR.'Services')){
            @mkdir(APP_DIR.DIRECTORY_SEPARATOR.'Services');
        }
    }

    /**
     * verify if the service class exists
     * @param string $service
     * @return boolean
     */
    private function exists(string $service)
    {
        return is_dir($this->svcPath. DS .ucfirst($service));
    }

    /**
     * create the new service class file
     * @param string $serviceName
     */
    private function createServiceClass(string $serviceName) :void
    {
        $path = $this->svcPath. DS .ucfirst($serviceName).self::EXTENSION;

        if (!$file = fopen($path, 'w', true)) throw new \Exception("Error creating the service class file");

        $this->setSyntax($file, $serviceName);
    }

    /**
     * write the new service class file
     * @param resource $file
     * @param string $newService
     * @return bool|int
     */
    private function setSyntax($file, $newService)
    {
        $frame = $this->skeleton($newService);

        return fwrite($file, $frame);
    }

    /**
     * set the information that is going to be in the class
     * @param string $newService
     * @return string skeleton
     */
    private function skeleton(string $newService)
    {
        return "<?php \n \n namespace App\Services; \n \n class ".ucfirst($newService)."Service \n { \n \n \t// do stuff \n \tpublic function doSmth() \n \t{ \n \t} \n }";
    }
}
