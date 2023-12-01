<?php

namespace Nati\OutsideSales\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
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
                InputOption::VALUE_NONE,
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {

            // Executa a função update
            if ($input->getOption('update') || $input->getOption('all') || !$input->getOptions()) {

                // Pega as datas informadas no console
                $date_init = $input->getOption('date-init') ?? 'now - 24hours';
                $date_end = $input->getOption('date-end') ?? 'now';

                // Define o periodo de atualização, porém, podemos atualizar para que se informe o período no console
                $period_init = date('Y-m-d H:i:s-03:00', strtotime($date_init));
                $period_end = date('Y-m-d H:i:s-03:00', strtotime($date_end));

                $output->writeln("<info>Iniciando update - ". $period_init ." até ". $period_end ."</info>");
                $this->_queue->updateList($period_init, $period_end);
                $output->writeln("<info>Lista atualizada com sucesso.</info>");
            }

            // Executa a função validate
            if ($input->getOption('validate') || $input->getOption('all') || !$input->getOptions()) {
                $output->writeln("<info>Iniciando validação:</info>");
                $this->_queue->validateList();
                $output->writeln("<info>Lista validada com sucesso.</info>");
            }

            // Executa a função execute
            if ($input->getOption('execute') || $input->getOption('all') || !$input->getOptions()) {
                $output->writeln("<info>Iniciando execução da fila:</info>");
                $this->_queue->executeList();
                $output->writeln("<info>Lista executada com sucesso.</info>");
            }

            // Executa a função revalidate
            if ($input->getOption('revalidate')) {
                $ids = ($input->getOption('revalidate') == 'all') ? null : $input->getOption('revalidate');
                $output->writeln("<info>Iniciando reexecução:</info>");
                $this->_queue->revalidateList($ids);
                $output->writeln("<info>Lista revalidada com sucesso.</info>");
            }

            // Executa a função change-status
            if ($input->getOption('change-status') || $input->getOption('all') || !$input->getOptions()) {

                // Pega as datas informadas no console
                $date_init = $input->getOption('date-init') ?? 'now - 24hours';
                $date_end = $input->getOption('date-end') ?? 'now';

                // Define o periodo de verificacao, porém, podemos atualizar para que se informe o período no console
                $period_init = date('Y-m-d H:i:s-03:00', strtotime($date_init));
                $period_end = date('Y-m-d H:i:s-03:00', strtotime($date_end));

                $output->writeln("<info>Iniciando atualizações - ". $period_init ." até ". $period_end ."</info>");
                $this->_queue->changeStatusList($period_init, $period_end);
                $output->writeln("<info>Lista atualizada com sucesso.</info>");
            }

            if($input->getOption('webhook') || $input->getOption('all') || !$input->getOptions()) {
                $output->writeln("<info>Iniciando leitura da fila de webhooks:</info>");
                $this->_queue->readWebhookQueue();
                $output->writeln("<info>Lista de webhooks lida com sucesso.</info>");
            }

        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}