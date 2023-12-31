<?php

namespace Nati\OutsideSales\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Nati\OutsideSales\Model\OutsideSalesQueue;

class OutsideQueue extends Command
{
    protected $_queue;

    public function __construct(OutsideSalesQueue $queue, $name = null)
    {
        $this->_queue = $queue;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('nati:outside-sales')
            ->setDescription('Run queue functions.')
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Executar todas as funções'
            )
            ->addOption(
                'update',
                null,
                InputOption::VALUE_NONE,
                'Executa apenas a função de atualizar a lista'
            )
            ->addOption(
                'validate',
                null,
                InputOption::VALUE_NONE,
                'Executa apenas a função de validar a lista'
            )
            ->addOption(
                'execute',
                null,
                InputOption::VALUE_NONE,
                'Executa apenas a função de executar a lista'
            )
            ->addOption(
                'revalidate',
                null,
                InputOption::VALUE_REQUIRED,
                'Executa função que reexecuta a lista que houve erro'
            )
            ->addOption(
                'change-status',
                null,
                InputOption::VALUE_NONE,
                'Executa função que atualiza os status das listas já cadastradas'
            )
            ->addOption(
                'webhook',
                null,
                InputOption::VALUE_REQUIRED,
                'Executa função que le a fila de webhooks'
            )
            ->addOption(
                'date-init',
                null,
                InputOption::VALUE_REQUIRED,
                'Data inicial para atualização da lista'
            )
            ->addOption(
                'date-end',
                null,
                InputOption::VALUE_REQUIRED,
                'Data final para atualização da lista'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output, $executeCron = false)
    {
        // Pega as datas informadas no console
        $date_init = 'now - 24hours';
        try {
            $date_init = $input->getOption('date-init') ?? $date_init;
        }
        catch (\Exception $e) {
            $date_init = 'now - 24hours';
        }

        $date_end = 'now';
        try {
            $date_end = $input->getOption('date-end') ?? $date_end;
        }
        catch (\Exception $e) {
            $date_end = 'now';
        }

        try {

            // Executa a função revalidate (primeira funcao, pois executa primeiro a fila parada, pra n gerar duplicidade na validacao da fila gerada após a execucao)
            if ($executeCron || $input->getOption('revalidate')) {
                $ids = ($executeCron || $input->getOption('revalidate') == 'all') ? null : $input->getOption('revalidate');
                $output->writeln("<info>Iniciando reexecução:</info>");

                try {
                    $this->_queue->revalidateList($ids);
                    $output->writeln("<info>Lista reexecutada com sucesso.</info>");
                } catch (\Exception $e) {
                    $output->writeln("<error>{$e->getMessage()}</error>");
                }
            }

            // Executa a função update
            if ($executeCron || $input->getOption('update') || $input->getOption('all') || !$input->getOptions()) {

                // Define o periodo de atualização, porém, podemos atualizar para que se informe o período no console
                $period_init = date('Y-m-d H:i:s', strtotime($date_init));
                $period_end = date('Y-m-d H:i:s', strtotime($date_end));

                $output->writeln("<info>Iniciando update - ". $period_init ." até ". $period_end ."</info>");

                try {
                    $this->_queue->updateList($period_init, $period_end);
                    $output->writeln("<info>Lista atualizada com sucesso.</info>");
                } catch (\Exception $e) {
                    $output->writeln("<error>{$e->getMessage()}</error>");
                }
            }

            // Executa a função validate
            if ($executeCron || $input->getOption('validate') || $input->getOption('all') || !$input->getOptions()) {
                $output->writeln("<info>Iniciando validação:</info>");

                try {
                    $this->_queue->validateList();
                    $output->writeln("<info>Lista validada com sucesso.</info>");
                } catch (\Exception $e) {
                    $output->writeln("<error>{$e->getMessage()}</error>");
                }
            }

            // Executa a função execute
            if ($executeCron || $input->getOption('execute') || $input->getOption('all') || !$input->getOptions()) {
                $output->writeln("<info>Iniciando execução da fila:</info>");

                try {
                    $this->_queue->executeList();
                    $output->writeln("<info>Lista executada com sucesso.</info>");
                } catch (\Exception $e) {
                    $output->writeln("<error>{$e->getMessage()}</error>");
                }
            }

            // Executa a função change-status
            if ($executeCron || $input->getOption('change-status') || $input->getOption('all') || !$input->getOptions()) {

                // Define o periodo de verificacao, porém, podemos atualizar para que se informe o período no console
                $period_init = date('Y-m-d H:i:s', strtotime($date_init));
                $period_end = date('Y-m-d H:i:s', strtotime($date_end));

                $output->writeln("<info>Iniciando atualizações - ". $period_init ." até ". $period_end ."</info>");
                try {
                    $this->_queue->changeStatusList($period_init, $period_end);
                    $output->writeln("<info>Lista atualizada com sucesso.</info>");
                } catch (\Exception $e) {
                    $output->writeln("<error>{$e->getMessage()}</error>");
                }
            }

            if ($executeCron || $input->getOption('webhook') || $input->getOption('all') || !$input->getOptions()) {
                $ids = ($executeCron || $input->getOption('webhook') == 'all') ? null : $input->getOption('webhook');
                $output->writeln("<info>Iniciando leitura da fila de webhooks:</info>");

                try {
                    $this->_queue->readWebhookQueue($ids);
                    $output->writeln("<info>Lista de webhooks lida com sucesso.</info>");
                } catch (\Exception $e) {
                    $output->writeln("<error>{$e->getMessage()}</error>");
                }
            }

        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }

    // Funcao para executar a funcao externamente, ex. pelo CRON ou funcoes externas (nao pelo console)
    public function executeCron() {
        $input = new ArrayInput([]); //Executa todas as funcoes
        $output = new ConsoleOutput(); // ou new NullOutput();

        $this->execute($input, $output, true);
    }
}